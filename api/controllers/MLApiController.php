<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MLApiController
 * Main class for the Mobiloud API endpoint.
 */
class MLApiController {
	private $posts;
	public $response_cache_on;
	public $posts_cache_on;

	public function __construct() {
		/*
		 * @TODO spl_autoload_register
		 */
		include_once MOBILOUD_PLUGIN_DIR . "/categories.php";
		include_once MOBILOUD_PLUGIN_DIR . '/subscriptions/functions.php';
		include_once dirname( __FILE__ ) . '/MLPostsController.php';
		include_once dirname( __FILE__ ) . '/MLCacheController.php';
		include_once dirname( __FILE__ ) . '/MLCategoryController.php';
		include_once dirname( __FILE__ ) . '/MLMediaController.php';
		include_once dirname( __FILE__ ) . '/../models/MLQuery.php';
		include_once dirname( __FILE__ ) . '/../models/MLPostsModel.php';

		if ( ! function_exists( "file_get_html" ) ) {
			require_once( MOBILOUD_PLUGIN_DIR . "libs/simple_html_dom.php" );
		}

		$this->response_cache_on = (Mobiloud::get_option( 'ml_cache_enabled' ) ? true : false);
		$this->posts_cache_on    = (Mobiloud::get_option( 'ml_cache_enabled' ) ? true : false);
		add_filter( 'ml_posts', array( 'MLApiController', 'filter_add_thumbs'), 15, 2);
		add_filter( 'ml_posts', array( 'MLApiController', 'filter_fix_images_url'), 20, 2);
	}

	/**
	 * Handler for the Mobiloud API endpoint
	 * 1. Create wp query array based on $_POST and $_GET variables
	 * 2. Get posts array
	 * 3. Prepare JSON string
	 * 4. Add JSON string to the cache
	 * 5. Send response
	 *
	 * @param $return_array bool
	 */
	public function handle_request( $return_array = false ) {
		$ml_query = new MLQuery();

		//Disable string cache for the array output
		if ( $return_array ) {
			$this->response_cache_on = false;
		}

		$cached_response = $this->get_cached_output( $ml_query );

		if ( ! empty( $cached_response ) ) {
			return $cached_response;
		}

		$ml_posts = new MLPostsModel();
		$this->build_query( $ml_query, $ml_posts );
		$this->posts = $ml_posts->get_posts( $ml_query );

		$json_string = $this->prepare_response( $ml_query, $return_array );
		$this->store_cache_response( $ml_query, $json_string );

		return $json_string;
	}


	/**
	 * Query builder
	 * Prepare MLQuery object, build array for wp_query
	 */
	function build_query( MLQuery $ml_query, $ml_posts ) {
		$ml_category = new MLCategoryController();

		$ml_query->post_types = $ml_posts->post_types();
		$this->define_taxonomies( $ml_query, $ml_category );
		$this->define_terms( $ml_query );

		$ml_query->post_count  = $ml_posts->count_posts_by_filter( $ml_query );
		$ml_query->real_offset = $this->calc_real_offset( $ml_query );
		$ml_query->post_types  = $this->extended_post_types( $ml_query->user_search );

		//Data validation
		if ( ( $this->no_post_types( $ml_query ) ) || ( $this->no_post_id( $ml_query ) ) ) {
			http_response_code(404); die();
		}

		//Use excluded categories list if category is not defined by user
		if ( $ml_query->user_category || $ml_query->user_category_id ) {
			$ml_query->user_term = $ml_query->category;
		} else {
			$ml_category->set_included_tax( $ml_query );
			$ml_category->set_excluded_cats( $ml_query );
		}

		if ( $ml_query->is_custom_request() ) {
			$ml_query->post_count = $ml_posts->count_posts_by_query( $ml_query );
		}

		$ml_query->build_query_array();
	}


	/**
	 * Prepare response
	 * Data processing: raw posts customization
	 */
	function prepare_response( MLQuery $ml_query, $return_array ) {
		$ml_posts_ctrl = new MLPostsController();

		$user_offset = $ml_query->user_offset;
		$taxonomy    = $ml_query->taxonomy;
		$post_count  = $ml_query->post_count;

		$cache_on = $this->posts_cache_on;

		$image_format = $ml_query->image_format;

		$final_posts = $ml_posts_ctrl->get_final_posts( $this->posts, $user_offset, $taxonomy, $post_count, $cache_on, $image_format );

		// Add a top-level attribute for the taxonomy if we had a taxonomy permalink request
		if ( $ml_query->permalink_is_taxonomy ) {
			$final_posts['taxonomy'] = $ml_query->taxonomy;
		}

		$current_user = wp_get_current_user();
		$final_posts  = apply_filters( 'ml_posts', $final_posts, $current_user );

		if ( $return_array ) {
			return $final_posts;
		}

		$json_string = json_encode( $final_posts );

		return $json_string;
	}


	function get_cached_output( MLQuery $ml_query ) {
		if ( $this->response_cache_on ) {
			$key      = http_build_query( $ml_query->ml_request );
			$ml_cache = new MLCacheController();
			$cache    = $ml_cache->get_cache( 'ml_json', http_build_query( $ml_query->ml_request ) );

			if ( ! empty( $cache ) ) {
				return $cache;
			};
		}

		return false;
	}

	/**
	 * Cache response
	 *
	 * @param $data
	 */
	function store_cache_response( MLQuery $ml_query, $json_string ) {
		if ( $this->response_cache_on ) {
			$key      = http_build_query( $ml_query->ml_request );
			$ml_cache = new MLCacheController();
			$ml_cache->set_cache( 'ml_json', $key, $json_string );
		}
	}

	public function add_json_header() {
		header( 'Content-Type: application/json' );
	}

	public function add_cache_header() {
		$time = absint(Mobiloud::get_option( 'ml_cache_expiration', 30)) * 60;
		header( "Cache-Control: private, max-age=$time, s-max-age=$time", true );
	}

	/**
	 * @param $ml_query
	 * @param $category
	 */
	public function define_taxonomies( MLQuery $ml_query, $ml_category ) {
		if ( empty( $ml_query->user_category_id ) && ! empty( $ml_query->user_permalink ) ) {
			$ml_category->set_taxonomy_by_permalink( $ml_query );
		}
	}

	/**
	 * @param $ml_query
	 */
	public function define_terms( MLQuery $ml_query ) {
		$ml_category = new MLCategoryController();

		if ( $ml_query->user_category_id ) {
			$ml_query->term_arr = $ml_category->ml_get_term_by( 'id', $ml_query->user_category_id, $ml_query->taxonomy );
			$ml_query->category = $ml_query->term_arr['term'];
		} else if ( $ml_query->user_category ) {
			$ml_query->term_arr = $ml_category->ml_get_term_by( 'slug', $ml_query->user_category, $ml_query->taxonomy );
			$ml_query->category = $ml_query->term_arr['term'];
		}
	}

	/**
	 * @param $user_search
	 *
	 * @return array
	 */
	public function extended_post_types( $user_search ) {
		$post_types       = explode( ",", get_option( "ml_article_list_include_post_types" ) );
		$ml_include_pages = get_option( "ml_include_pages_in_search", "false" );
		$include_pages    = ( $ml_include_pages == "true" || $ml_include_pages == true );

		if ( strlen( $user_search ) > 0 && ! in_array( "page", $post_types ) && $include_pages ) {
			array_push( $post_types, "page" );

			return $post_types;
		}

		return $post_types;
	}

	/**
	 * @param $user_post_count
	 *
	 * @return mixed
	 */
	public function calc_real_offset( MLQuery $ml_query ) {
		if ( $ml_query->user_post_count == null ) {
			$ml_query->user_post_count = $ml_query->post_count;
		}
		$new_posts_count = $ml_query->post_count - $ml_query->user_post_count;
		$real_offset     = $ml_query->user_offset + $new_posts_count;

		return $real_offset;
	}

	/**
	 * @param $post_types
	 * @param $term_arr
	 *
	 * @return mixed
	 */
	public function no_post_types( MLQuery $ml_query ) {
		$post_types       = $ml_query->post_types;
		$term_arr         = $ml_query->term_arr;
		$empty_post_types = ( empty( $post_types ) || ( isset( $post_types[0] ) && $post_types[0] == '' ) );

		return ( $empty_post_types && ! ( count( $term_arr ) && $term_arr['term'] ) );
	}

	/**
	 */
	public function no_post_id( MLQuery $ml_query ) {
		if ( isset( $ml_query->ml_request["permalink"] )  ) {

			if (!Mobiloud::get_option( 'ml_internal_links' )) {
				http_response_code(404); die();
			}

			//Return nothing if file
			$path_info = pathinfo($ml_query->ml_request["permalink"]);
			if (!empty($path_info['extension']) && $path_info['extension']!=='html')	die();

			$postIDfromURL = url_to_postid( $ml_query->ml_request["permalink"] );
			if ( $postIDfromURL ) {
				$ml_query->ml_request["post_id"] = $postIDfromURL;

				return false;
			} else {
				if (!$ml_query->permalink_is_taxonomy) {
				return true;
			}
		  }
		}

		return false;
	}

	/**
	 * Print content
	 *
	 * @param $data
	 */
	function send_response( $data ) {
		//header('content-type: application/json; charset=utf-8');
		// prevent newrelic injected JavaScript breaking JSON
		if ( extension_loaded( 'newrelic' ) ) {
			newrelic_disable_autorum();
		}
		echo $data;
	}


	/**
	 * @param bool|false $debug
	 */
	public function set_error_handlers( $debug = false ) {
		if ( $debug == true ) {
			ini_set( 'display_errors', 1 );
			ini_set( 'display_startup_errors', 1 );
			error_reporting( E_ALL );
		} else {
			ini_set( 'display_errors', 'Off' );
			error_reporting( 0 );
			function error_handler( $errno, $errstr, $errfile, $errline ) {
			}

			function warning_handler( $errno, $errstr ) {
			}

			function shutdown() {
				$error = error_get_last();
				if ( $error['type'] === E_ERROR ) {
					error_handler( '', '', '', '' );
				}
			}

			register_shutdown_function( 'shutdown' );
			set_error_handler( 'error_handler', E_ALL ^ ( E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING ) );
			set_error_handler( "warning_handler", E_WARNING | E_NOTICE | E_USER_NOTICE | E_USER_WARNING );
		}
	}
	// add "thumb" image size if it missed
	static function filter_add_thumbs($final_posts, $current_user) {
		foreach ($final_posts['posts'] as $key => &$item) {
			$images = $item['images'];
			if (!isset($image['thumb'])) {
				foreach ($images as $index => &$image) {
					$existing_size = '';
					if (isset($image['medium'])) {
						$existing_size = 'medium';
					} elseif (isset($image['medium_large'])) {
						$existing_size = 'medium_large';
					} elseif (isset($image['large'])) {
						$existing_size = 'large';
					} elseif (isset($image['full'])) {
						$existing_size = 'full';
					}
					if (!isset($image['thumb']) && !empty($existing_size)) {
						$item['images'][$index]['thumb'] =  $image[$existing_size];
					}
					if (!isset($images['big-thumb']) && !empty($existing_size)) {
						$item['images'][$index]['big-thumb'] =  $image[$existing_size];
					}
				}
			}

		}
		return $final_posts;
	}

	// prepend https: or http: prefix if url start from '//'
	private static function fix_url($url) {
		if (0 === strpos($url, '//')) {
			$url = (is_ssl() ? 'https:' : 'http:') . $url;
		}
		return $url;
	}

	// prepend "http:" or "https:" to image urls if it missed
	static function filter_fix_images_url($final_posts, $current_user) {
		foreach ($final_posts['posts'] as $key => &$item) {
			foreach ($item['images'] as $index => &$image) {
				if (empty($image)) {
				} elseif (is_string($image)) {
					$item['images'][$index] = self::fix_url($image);
				} elseif (is_array($image) && !empty($image['url'])){
					$item['images'][$index]['url'] = self::fix_url($image['url']);
				}
			}
			foreach ($item['featured_image'] as $index => &$image) {
				if (empty($image)) {
				} elseif (is_string($image)) {
					$item['featured_image'][$index] = self::fix_url($image);
				} elseif (is_array($image) && !empty($image['url'])){
					$item['featured_image'][$index]['url'] = self::fix_url($image['url']);
				}
			}

		}
		return $final_posts;
	}
}

?>