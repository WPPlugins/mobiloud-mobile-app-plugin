<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MLPostsController
 */
class MLPostsController {
	/**
	 * Generate posts array
	 *
	 * @param $posts
	 * @param $offset
	 * @param $taxonomy
	 * @param $post_type
	 * @param $final_posts
	 *
	 * @return mixed
	 */
	function get_final_posts( $posts, $offset, $taxonomy, $post_count, $cache_on, $image_format = 1 ) {
		$final_posts = array( "posts" => array(), "post-count" => $post_count );

		$media = new MLMediaController();
		$media->set_image_format( $image_format );

		$ml_cache = new MLCacheController();

		foreach ( $posts as $post ) {
			$format       = get_post_format( $post );
			$post_id      = $post->ID;
			$cached_posts = $cache_on ? $this->get_cached( $post_id, $image_format ) : NULL;

			if ( $offset > 0 && is_sticky( $post_id ) ) {
				continue;
			}

			if ( ! empty( $cached_posts ) && ( $cache_on !== false ) ) {
				$final_posts["posts"][] = $cached_posts;
				continue;
			}

			$final_post = $this->final_post( $taxonomy, $post_id, $post, $media, $format );

			$key = $this->post_cache_key( $post_id, $image_format );
			if ($cache_on) {
				$ml_cache->set_cache( 'ml_post', $key, serialize( $final_post ) );
			}

			$final_posts["posts"][] = $final_post;
		}

		return $final_posts;
	}


	/**
	 * @param $taxonomy
	 * @param $post_id
	 * @param $post
	 * @param $media
	 * @param $format
	 *
	 * @return array|mixed
	 */
	private function final_post( $taxonomy, $post_id, $post, $media, $format ) {
		$final_post = $this->new_post( $post_id, $post );
		$final_post = $this->add_comments( $post_id, $final_post );
		$final_post = $this->add_permalink( $post_id, $final_post, $post );
		$final_post = $this->add_categories( $taxonomy, $post_id, $final_post );
		$final_post = $this->add_date( $post, $final_post );
		$final_post = $this->add_content( $final_post, $post );
		$final_post = $this->add_custom_field( $post, $final_post );
		$final_post = $this->add_excerpt( $post, $final_post );
		$final_post = $this->set_sticky( $post, $final_post );
		$final_post = $media->add_media( $post, $final_post );
		if ( $format == 'status' ) {
			$final_post = $this->set_status_format( $post, $final_post );
		}

		return $final_post;
	}


	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function get_post_excerpt( $post_id ) {
		global $post;
		$save_post = $post;
		$post      = get_post( $post_id );
		$output    = get_the_excerpt();
		$post      = $save_post;

		return $output;
	}

	/**
	 * EscapeJson
	 *
	 * @param $data
	 */
	function escape_json( $value ) {
		$escapers     = array( "\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c" );
		$replacements = array( "\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b" );
		$result       = str_replace( $escapers, $replacements, $value );

		return $result;
	}


	/**
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_content( $final_post, $post ) {
		ob_start();
		include( MOBILOUD_PLUGIN_DIR . "views/post.php" );
		$html_content = ob_get_clean();

		//replace relative URLs with absolute
		$html_content = preg_replace(
			"#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http|/)([^\"'>]+)([\"'>]+)#", '$1' . (!empty($final_post["permalink"]) ? $final_post["permalink"] : '' ) . '/$2$3',
			$html_content
		);

		//$html_content = escape_json($html_content);
		$final_post["content"] = $html_content;

		return $final_post;
	}

	/**
	 * @param $taxonomy
	 * @param $post_id
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_categories( $taxonomy, $post_id, $final_post ) {
		$categories = get_the_category( $post_id );
		foreach ( $categories as $category ) {
			$final_post["categories"][] = array(
				"cat_id" => "$category->cat_ID",
				"name"   => $category->cat_name,
				"slug"   => $category->category_nicename
			);
		}

		if ( $taxonomy !== 'category' && ! empty( $taxonomy ) ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy );

			foreach ( $terms as $term ) {
				$final_post["categories"][] = array(
					"cat_id" => "$term->term_id",
					"name"   => $term->name,
					"slug"   => $term->slug
				);
			}

			return $final_post;
		}

		return $final_post;
	}

	/**
	 * @param $post
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_date( $post, $final_post ) {
		$final_post["date"] = $post->post_date;

		if ( get_option( 'ml_datetype', 'prettydate' ) == 'datetime' ) {
			$final_post["date_display"] = date_i18n(
				get_option( 'ml_dateformat', 'F j, Y' ), strtotime( $post->post_date ), get_option( 'gmt_offset' )
			);

			return $final_post;
		}

		return $final_post;
	}

	/**
	 * @param $post_id
	 * @param $final_post
	 * @param $post
	 *
	 * @return mixed
	 */
	public function add_permalink( $post_id, $final_post, $post ) {
		$final_post["permalink"] = get_permalink( $post_id );
		if (empty($final_post["permalink"])) {
			$final_post["permalink"] = '0';
		}
		if ( strlen( trim( get_option( 'ml_custom_field_url', '' ) ) ) > 0 ) {
			$custom_url_value = get_post_meta( $post->ID, get_option( 'ml_custom_field_url' ), true );
			if ( strlen( trim( $custom_url_value ) ) > 0 ) {
				$final_post["permalink"] = $custom_url_value;

				return $final_post;
			}

			return $final_post;
		}

		return $final_post;
	}

	/**
	 * @param $post_id
	 * @param $final_posts
	 *
	 * @return array
	 */
	private function get_cached( $post_id, $image_format ) {
		$cached = false;

		$key      = $this->post_cache_key( $post_id, $image_format );
		$ml_cache = new MLCacheController();

		$cache = $ml_cache->get_cache( 'ml_post', $key );


		if ( ! empty( $cache ) ) {
			if ( unserialize( $cache ) !== false ) {
				$cached = unserialize( $cache );
			}
		};

		return $cached;
	}


	/**
	 * @param $post_id
	 * @param $post
	 *
	 * @return array
	 */
	public function new_post( $post_id, $post ) {
		$final_post = array();

		$final_post["post_id"]   = "$post_id";
		$final_post["post_type"] = $post->post_type;

		$final_post["author"]     = array();
		$final_post["categories"] = array();

		$final_post["author"]["name"]      = html_entity_decode( get_the_author_meta( 'display_name', $post->post_author ) );
		$final_post["author"]["author_id"] = $post->post_author;

		$final_post["title"] = strip_tags( $post->post_title );
		$final_post["title"] = html_entity_decode( $final_post["title"], ENT_QUOTES );

		$final_post["videos"] = array();
		$final_post["images"] = array();

		$final_post['excerpt'] = "";

		return $final_post;
	}

	/**
	 * @param $post_id
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_comments( $post_id, $final_post ) {
		$comments_count = wp_count_comments( $post_id );

		$final_post["comments-count"] = 0;
		if ( $comments_count ) {
			$final_post["comments-count"] = intval( $comments_count->approved );

			return $final_post;
		}

		return $final_post;
	}

	/**
	 * @param $post
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_custom_field( $post, $final_post ) {
		if ( strlen( get_option( 'ml_custom_field_name', '' ) ) > 0 ) {

			if ( get_option( 'ml_custom_field_name', '' ) == "excerpt" ) {
				$custom_field_val = html_entity_decode( urldecode( strip_tags( $this->get_post_excerpt( $post->ID ) ) ) );

				$final_post['custom1'] = $custom_field_val;

				return $final_post;
			} else {
				$custom_field_val      = get_post_meta( $post->ID, get_option( 'ml_custom_field_name', '' ), true );
				$final_post['custom1'] = $custom_field_val;

				return $final_post;
			}
		}

		return $final_post;
	}

	/**
	 * @param $post
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function add_excerpt( $post, $final_post ) {
		$final_post['excerpt'] = html_entity_decode( urldecode( strip_tags( $this->get_post_excerpt( $post->ID ) ) ) );
		$final_post['excerpt'] = str_replace( 'Read More', '', $final_post['excerpt'] );
		//$final_post['excerpt'] = htmlentities( $final_post['excerpt'], ENT_QUOTES, 'utf-8', FALSE);

		return $final_post;
	}

	/**
	 * @param $post
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function set_status_format( $post, $final_post ) {
		$final_post["title"]   = $post->post_content;
		$final_post["content"] = "";
		$final_post['custom1'] = "";
		$final_post['excerpt'] = "";

		return $final_post;
	}

	/**
	 * @param $post
	 * @param $final_post
	 *
	 * @return mixed
	 */
	public function set_sticky( $post, $final_post ) {
		$final_post["sticky"] = is_sticky( $post->ID ) || $post->sticky;

		return $final_post;
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	private function post_cache_key( $post_id, $image_format ) {
		$key = http_build_query( array( 'post_id' => "$post_id", "type" => "ml_post", "i_f" => $image_format ) );

		return $key;
	}


}