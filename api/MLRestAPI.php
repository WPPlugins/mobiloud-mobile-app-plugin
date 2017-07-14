<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Sketch for the Rest API
 */

class MLRestAPI {
	private $debug;

	public function __construct() {
		$this->debug = false;

		include_once MOBILOUD_PLUGIN_DIR . '/api/controllers/MLApiController.php';
		add_action( 'rest_api_init', array( $this, 'ml_rest_api' ) );
	}

	function ml_rest_api() {
		$namespace = 'ml-api/v2';

		register_rest_route( $namespace, '/version/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'version' ),
		) );
		register_rest_route( $namespace, '/config/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'config' ),
		) );
		register_rest_route( $namespace, '/menu/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'menu' ),
		) );
		register_rest_route( $namespace, '/login/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'login' ),
		) );
		register_rest_route( $namespace, '/comments/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'comments' ),
		) );
		register_rest_route( $namespace, '/comments/disqus/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'disqus' ),
		) );
		register_rest_route( $namespace, '/page/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'page' ),
		) );
		register_rest_route( $namespace, '/post/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'post' ),
		) );
		register_rest_route( $namespace, '/posts/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'posts' ),
		) );
	}


	function posts() {
		$api = new MLApiController();
		$api->set_error_handlers( $this->debug );

		$response_data = $api->handle_request( true );
		$response      = new WP_REST_Response( $response_data );

		return $response;
	}

	function version() {
		ob_start();
		include_once MOBILOUD_PLUGIN_DIR . 'version.php';
		$html_content = ob_get_clean();
		$data         = json_decode( $html_content );

		return $data;
	}

	function config() {
		ob_start();
		include_once MOBILOUD_PLUGIN_DIR . 'config.php';
		$html_content = ob_get_clean();
		$data         = json_decode( $html_content );

		return $data;
	}

	function login() {
		ob_start();
		include_once MOBILOUD_PLUGIN_DIR . '/subscriptions/login.php';
		$html_content = ob_get_clean();
		$data         = json_decode( $html_content );

		return $data;
	}

	function menu() {
		ob_start();
		include_once MOBILOUD_PLUGIN_DIR . 'get_categories.php';
		$html_content = ob_get_clean();
		$data         = json_decode( $html_content );

		return $data;
	}

	function post() {
		//include(MOBILOUD_PLUGIN_DIR . "post/post.php");
		return 'Api v2. Post. Not implemented. 200 OK';
	}


	function comments() {
		//include_once MOBILOUD_PLUGIN_DIR . 'comments.php';
		return 'Api v2. Comments. Not implemented. 200 OK';
	}

	function disqus() {
		return 'Api v2. Disqus comments. Not implemented. 200 OK';
	}

	function page() {
		//include_once MOBILOUD_PLUGIN_DIR . 'get_page.php';
		return 'Api v2. Page. Not implemented. 200 OK';
	}

}