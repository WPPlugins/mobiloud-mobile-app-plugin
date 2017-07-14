<?php

class Mobiloud_Admin {

	private static $initiated = false;

	public static $settings_tabs = array(
		'design'      => array(
			'title'  => 'Design',
			'form_wrap_id' => 'get_started_design',
			'form_id' => '',
		),
		'menu_config' => array(
			'title'  => 'Menu',
			'form_wrap_id' => 'get_started_menu_config',
		),
		'settings'    => array(
			'title' => 'Settings',
			'form_wrap_id' => 'ml_settings_general',
		),
		'advertising' => array(
			'title' => 'Advertising',
			'form_wrap_id' => 'ml_settings_advertising',
		),
		'analytics'   => array(
			'title' => 'Analytics',
			'form_wrap_id' => 'ml_settings_analytics',
		),
		'editor'      => array(
			'title' => 'Editor',
			'form_wrap_id' => 'ml_settings_editor',
			'form_id' => 'form_editor',
			'no_submit_button' => true,
		),
		'push'        => array(
			'title' => 'Push',
			'form_wrap_id' => 'ml_push_settings',
		),
	);
	public static $push_tabs = array(
		'notifications' => 'Notifications',
	);
	public static $editor_sections = array(
		'ml_post_head'                => 'PHP Inside HEAD tag',
		'ml_html_post_head'           => 'HTML Inside HEAD tag',
		'ml_post_custom_js'           => 'Custom JS',
		'ml_post_custom_css'          => 'Custom CSS',
		'ml_post_start_body'          => 'PHP at the start of body tag',
		'ml_html_post_start_body'     => 'HTML at the start of body tag',
		'ml_post_before_details'      => 'PHP before post details',
		'ml_html_post_before_details' => 'HTML before post details',
		'ml_post_right_of_date'       => 'PHP right of date',
		'ml_post_after_details'       => 'PHP after post details',
		'ml_html_post_after_details'  => 'HTML after post details',
		'ml_post_before_content'      => 'PHP before Content',
		'ml_html_post_before_content' => 'HTML before Content',
		'ml_post_after_content'       => 'PHP after Content',
		'ml_html_post_after_content'  => 'HTML after Content',
		'ml_post_after_body'          => 'PHP at the end of body tag',
		'ml_html_post_after_body'     => 'HTML at the end of body tag',
		'ml_post_footer'              => 'PHP Footer'
	);
	public static $banner_positions = array(
		'ml_banner_above_content' => 'Above Content',
		'ml_banner_above_title'   => 'Above Title',
		'ml_banner_below_content' => 'Below Content',
	);
	private static $admin_screens = array();

	public static function init() {
		include_once MOBILOUD_PLUGIN_DIR . 'categories.php';
		include_once MOBILOUD_PLUGIN_DIR . 'pages.php';

		if ( ! self::$initiated ) {
			self::init_hooks();
		}

		Mobiloud_App_Preview::init();
	}

	/**
	* Initializes WordPress hooks
	*/
	private static function init_hooks() {
		self::$initiated = true;
		add_action( 'admin_init', array( 'Mobiloud_Admin', 'admin_init' ) );
		add_action( 'current_screen', array( 'Mobiloud_Admin', 'current_screen' ) );
		add_action( 'admin_menu', array( 'Mobiloud_Admin', 'admin_menu' ) );
		add_action( 'admin_head', 'ml_init_intercom' );
		add_action( 'admin_head', array( 'Mobiloud_Admin', 'check_mailing_list_alert' ) );
		add_action( 'wp_ajax_ml_save_initial_data', array( 'Mobiloud_Admin', 'save_initial_data' ) );
		add_action( 'wp_ajax_ml_save_editor', array( 'Mobiloud_Admin', 'save_editor' ) );
		add_action( 'wp_ajax_ml_save_editor_embed', array( 'Mobiloud_Admin', 'save_editor_embed' ) );
		add_action( 'wp_ajax_ml_save_banner', array( 'Mobiloud_Admin', 'save_banner' ) );
		add_action( 'wp_ajax_ml_tax_list', array( 'Mobiloud_Admin', 'get_tax_list' ) );

		add_action( 'save_post', array( 'Mobiloud_Admin', 'flush_cache_on_save' ) );
		add_action( 'transition_post_status', array( 'Mobiloud_Admin', 'flush_cache_on_transition' ), 10, 3 );
		if (is_admin() && Mobiloud::get_option( 'ml_push_notification_enabled', false)) {
			add_action( 'add_meta_boxes', array( 'Mobiloud_Admin', 'add_push_metabox' ), 1, 2 );
			add_action( 'pre_post_update', array( 'Mobiloud_Admin', 'save_push_metabox' ) );
		}

	}

	public static function flush_cache_on_save( $post_id ) {
		include_once MOBILOUD_PLUGIN_DIR . 'api/controllers/MLCacheController.php';
		$ml_cache = new MLCacheController();
		$ml_cache->flush_cache( 'ml_json' );
		$ml_cache->flush_post_cache( $post_id );
	}

	public static function flush_cache_on_transition( $new_status, $old_status, $post ) {
		include_once MOBILOUD_PLUGIN_DIR . 'api/controllers/MLCacheController.php';
		$ml_cache = new MLCacheController();
		$ml_cache->flush_cache( 'ml_json' );
		$ml_cache->flush_post_cache( $post->ID );
	}

	public static function flush_cache() {
		include_once MOBILOUD_PLUGIN_DIR . 'api/controllers/MLCacheController.php';
		$ml_cache = new MLCacheController();
		$ml_cache->flush_cache( 'ml_json' );
		$ml_cache->flush_cache( 'ml_post' );
	}

	public static function admin_init() {
		self::set_default_options();
		self::admin_redirect();
		// for old Wordpress versions
		if (!function_exists( 'set_current_screen' )) {
			self::register_scripts();
		}
	}

	public static function current_screen() {
		if (is_admin()) {
			$screen = get_current_screen();
			if ($screen instanceof WP_Screen && in_array($screen->id, self::$admin_screens)) {
				self::register_scripts();
			}
		}
	}

	public static function admin_menu() {
		$image = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiICAgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiICAgdmVyc2lvbj0iMS4wIiAgIGlkPSJMYXllcl8xIiAgIHg9IjBweCIgICB5PSIwcHgiICAgd2lkdGg9IjI0cHgiICAgaGVpZ2h0PSIyNHB4IiAgIHZpZXdCb3g9IjAgMCAyNCAyNCIgICBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAyNCAyNCIgICB4bWw6c3BhY2U9InByZXNlcnZlIiAgIGlua3NjYXBlOnZlcnNpb249IjAuNDguNCByOTkzOSIgICBzb2RpcG9kaTpkb2NuYW1lPSJtbC1tZW51LWljb250ci5zdmciPjxtZXRhZGF0YSAgICAgaWQ9Im1ldGFkYXRhMjkiPjxyZGY6UkRGPjxjYzpXb3JrICAgICAgICAgcmRmOmFib3V0PSIiPjxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PjxkYzp0eXBlICAgICAgICAgICByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPjxkYzp0aXRsZSAvPjwvY2M6V29yaz48L3JkZjpSREY+PC9tZXRhZGF0YT48ZGVmcyAgICAgaWQ9ImRlZnMyNyI+PGNsaXBQYXRoICAgICAgIGlkPSJTVkdJRF8yXy0yIj48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTktMSIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDE4Ij48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTMwMjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAyMiI+PHVzZSAgICAgICAgIGhlaWdodD0iMTA1Mi4zNjIyIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgeT0iMCIgICAgICAgICB4PSIwIiAgICAgICAgIHN0eWxlPSJvdmVyZmxvdzp2aXNpYmxlIiAgICAgICAgIHhsaW5rOmhyZWY9IiNTVkdJRF8xXy04IiAgICAgICAgIG92ZXJmbG93PSJ2aXNpYmxlIiAgICAgICAgIGlkPSJ1c2UzMDI0IiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMjYiPjx1c2UgICAgICAgICBoZWlnaHQ9IjEwNTIuMzYyMiIgICAgICAgICB3aWR0aD0iNzQ0LjA5NDQ4IiAgICAgICAgIHk9IjAiICAgICAgICAgeD0iMCIgICAgICAgICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8tOCIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICBpZD0idXNlMzAyOCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDMwIj48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTMwMzIiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAzNCI+PHVzZSAgICAgICAgIGhlaWdodD0iMTA1Mi4zNjIyIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgeT0iMCIgICAgICAgICB4PSIwIiAgICAgICAgIHN0eWxlPSJvdmVyZmxvdzp2aXNpYmxlIiAgICAgICAgIHhsaW5rOmhyZWY9IiNTVkdJRF8xXy04IiAgICAgICAgIG92ZXJmbG93PSJ2aXNpYmxlIiAgICAgICAgIGlkPSJ1c2UzMDM2IiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMzgiPjx1c2UgICAgICAgICBoZWlnaHQ9IjEwNTIuMzYyMiIgICAgICAgICB3aWR0aD0iNzQ0LjA5NDQ4IiAgICAgICAgIHk9IjAiICAgICAgICAgeD0iMCIgICAgICAgICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8tOCIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICBpZD0idXNlMzA0MCIgLz48L2NsaXBQYXRoPjxkZWZzICAgICAgIGlkPSJkZWZzNSI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0iU1ZHSURfMV8iIC8+PC9kZWZzPjxjbGlwUGF0aCAgICAgICBpZD0iU1ZHSURfMl8iPjx1c2UgICAgICAgICBpZD0idXNlOSIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8iIC8+PC9jbGlwUGF0aD48ZGVmcyAgICAgICBpZD0iZGVmczUtMiI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0iU1ZHSURfMV8tOCIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9kZWZzPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDQ1Ij48dXNlICAgICAgICAgaWQ9InVzZTMwNDciICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9IlNWR0lEXzJfLTgiPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTktMiIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAxOC0wIj48cmVjdCAgICAgICAgIGhlaWdodD0iMjQiICAgICAgICAgd2lkdGg9IjI0IiAgICAgICAgIGlkPSJ1c2UzMDIwLTkiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMjItNSI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0idXNlMzAyNC05IiAgICAgICAgIHg9IjAiICAgICAgICAgeT0iMCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDI2LTciPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTMwMjgtMyIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAzMC0xIj48cmVjdCAgICAgICAgIGhlaWdodD0iMjQiICAgICAgICAgd2lkdGg9IjI0IiAgICAgICAgIGlkPSJ1c2UzMDMyLTEiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMzQtNiI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0idXNlMzAzNi04IiAgICAgICAgIHg9IjAiICAgICAgICAgeT0iMCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDM4LTQiPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTMwNDAtMyIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48L2RlZnM+PHNvZGlwb2RpOm5hbWVkdmlldyAgICAgcGFnZWNvbG9yPSIjZmZmZmZmIiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiICAgICBib3JkZXJvcGFjaXR5PSIxIiAgICAgb2JqZWN0dG9sZXJhbmNlPSIxMCIgICAgIGdyaWR0b2xlcmFuY2U9IjEwIiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIiAgICAgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAiICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSI3MzAiICAgICBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSI0ODAiICAgICBpZD0ibmFtZWR2aWV3MjUiICAgICBzaG93Z3JpZD0iZmFsc2UiICAgICBpbmtzY2FwZTp6b29tPSI5LjgzMzMzMzMiICAgICBpbmtzY2FwZTpjeD0iMy4wMjQxMzI1IiAgICAgaW5rc2NhcGU6Y3k9IjIxLjIwNTUwNSIgICAgIGlua3NjYXBlOndpbmRvdy14PSI1MjUiICAgICBpbmtzY2FwZTp3aW5kb3cteT0iNjYiICAgICBpbmtzY2FwZTp3aW5kb3ctbWF4aW1pemVkPSIwIiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iTGF5ZXJfMSIgLz48cGF0aCAgICAgc3R5bGU9ImZpbGw6Izk5OTk5OTtmaWxsLW9wYWNpdHk6MSIgICAgIGNsaXAtcGF0aD0idXJsKCNTVkdJRF8yXykiICAgICBkPSJNIDQsMCBDIDEuNzkxLDAgMCwxLjc5MSAwLDQgbCAwLDE2IGMgMCwyLjIwOSAxLjc5MSw0IDQsNCBsIDE2LDAgYyAyLjIwOSwwIDQsLTEuNzkxIDQsLTQgTCAyNCw0IEMgMjQsMS43OTEgMjIuMjA5LDAgMjAsMCBMIDQsMCB6IG0gOS41LDMuNSBjIDAuMTI2NDcsMCAwLjI2MDA3NSwwLjAyNzgwOCAwLjM3NSwwLjA2MjUgMC4wODkzMiwwLjAyNTUxMSAwLjE2OTU2NiwwLjA1MDkyIDAuMjUsMC4wOTM3NSAwLjAyMTI2LDAuMDEyMDMzIDAuMDQxOTgsMC4wMTgwNzMgMC4wNjI1LDAuMDMxMjUgMC4xMTA4OTUsMC4wNjcwMTIgMC4xOTQ5MzcsMC4xNTQyOTg2IDAuMjgxMjUsMC4yNSAwLjA3OTE5LDAuMDg2OTk3IDAuMTMyNTAzLDAuMTc2NjQwOSAwLjE4NzUsMC4yODEyNSBsIDAuMDMxMjUsMCBjIDAuMDE1MjIsMC4wMjk2NTcgMC4wMTYyLDAuMDYzOTkyIDAuMDMxMjUsMC4wOTM3NSAwLjEzMjc5MiwwLjI2MjYwNjMgMC4yNTU2MTEsMC41MTEwNDY2IDAuMzc1LDAuNzgxMjUgMC4wMTMzNCwwLjAzMDE0NiAwLjAxODA4LDAuMDYzNTE5IDAuMDMxMjUsMC4wOTM3NSAwLjExODAzLDAuMjcxNDExMyAwLjIzOTY0OCwwLjUzMzk1OTUgMC4zNDM3NSwwLjgxMjUgMC4xMjU1MjgsMC4zMzQ4MTMyIDAuMjM5NDI0LDAuNjg3MTQ4MyAwLjM0Mzc1LDEuMDMxMjUgMC4wODY3NiwwLjI4NzQ3OTUgMC4xNzgyMjYsMC41ODEzMzQ2IDAuMjUsMC44NzUgMC4wMDQ5LDAuMDE5ODg3IC0wLjAwNDgsMC4wNDI1ODUgMCwwLjA2MjUgMC4wNzM3NywwLjMwNjUyNDcgMC4xNjE3ODksMC42MjQ3NzkgMC4yMTg3NSwwLjkzNzUgMC4wMDE4LDAuMDEwMDI3IC0wLjAwMTgsMC4wMjEyMTYgMCwwLjAzMTI1IDAuMDU4MTQsMC4zMjI1MjQzIDAuMDg1MjgsMC42NDAyMDM1IDAuMTI1LDAuOTY4NzUgMC4wODExMSwwLjY3NjgxMiAwLjEyNSwxLjM2MDc3NCAwLjEyNSwyLjA2MjUgbCAwLDAuMDMxMjUgMC4wMzEyNSwwIDAsMC4wMzEyNSBjIDAsMC42ODUgLTAuMDQ0OCwxLjM3MDEyMiAtMC4xMjUsMi4wMzEyNSAtMC4wMDEyLDAuMDEwMTkgMC4wMDEyLDAuMDIxMDYgMCwwLjAzMTI1IC0wLjAzOTQzLDAuMzE5OTc5IC0wLjA5OTAxLDAuNjIzNTk0IC0wLjE1NjI1LDAuOTM3NSAtMC4wMDM2LDAuMDIwMzEgMC4wMDM3LDAuMDQyMjEgMCwwLjA2MjUgLTAuMDU2NTEsMC4zMDM1NTQgLTAuMTE0OTIxLDAuNjA4Njg3IC0wLjE4NzUsMC45MDYyNSAtMC4wNjUyLDAuMjczNTIzIC0wLjE0MDU0OSwwLjU0NDI2NiAtMC4yMTg3NSwwLjgxMjUgLTAuMTA0OTk4LDAuMzUyNDM4IC0wLjIxNzAxNywwLjY4ODM2NSAtMC4zNDM3NSwxLjAzMTI1IC0wLjIxNjUwMSwwLjU5NjI3NSAtMC40NzEwMDIsMS4xNTU2MzcgLTAuNzUsMS43MTg3NSAtMC4wMTAzMSwwLjAyMDgxIC0wLjAyMDg2LDAuMDQxNzQgLTAuMDMxMjUsMC4wNjI1IC0wLjAwNywwLjAxODkzIDAuMDA3OCwwLjA0Mzk5IDAsMC4wNjI1IC0wLjAxNjg3LDAuMDMzNDMgLTAuMDQ1NDEsMC4wNjA0NSAtMC4wNjI1LDAuMDkzNzUgLTAuMDU1MDcsMC4xMDQ1MjUgLTAuMTA4Mjk4LDAuMTk0MjY5IC0wLjE4NzUsMC4yODEyNSAtMC4wNTQ2LDAuMDYwNDQgLTAuMTIyNjI0LDAuMTA2Nzg5IC0wLjE4NzUsMC4xNTYyNSBDIDE0LjA5NDcxLDIwLjM4OTM2NiAxMy44Mjg2NzQsMjAuNSAxMy41MzEyNSwyMC41IGMgLTAuMTAxMjg3LDAgLTAuMTg2NTU4LC0wLjAwOTYgLTAuMjgxMjUsLTAuMDMxMjUgLTAuMDc1NDYsLTAuMDE1NDQgLTAuMTQ4NTcyLC0wLjAzNDcyIC0wLjIxODc1LC0wLjA2MjUgLTAuMDA3OSwtMC4wMDMzIC0wLjAyMzM5LDAuMDAzNSAtMC4wMzEyNSwwIC0wLjE1NzI2NiwtMC4wNjY0OCAtMC4yODcxODcsLTAuMTYyMzEyIC0wLjQwNjI1LC0wLjI4MTI1IC0wLjIzNzUsLTAuMjM3MjUgLTAuMzc1LC0wLjU3NCAtMC4zNzUsLTAuOTM3NSAwLC0wLjA5OTYxIDAuMDA5MSwtMC4xOTIxMzEgMC4wMzEyNSwtMC4yODEyNSAwLjAwMjMsLTAuMDExMzIgLTAuMDAyNiwtMC4wMjAwNCAwLC0wLjAzMTI1IDAuMDA2MSwtMC4wMjIyMSAwLjAyMzkyLC0wLjA0MDc0IDAuMDMxMjUsLTAuMDYyNSAwLjAyNDU2LC0wLjA4MjgyIDAuMDU0MjIsLTAuMTQzNjQgMC4wOTM3NSwtMC4yMTg3NSBsIC0wLjAzMTI1LDAgYyAxLjAxMSwtMS45NjkgMS41NjI1LC00LjE5NzUgMS41NjI1LC02LjU2MjUgbCAwLC0wLjAzMTI1IDAsLTAuMDMxMjUgYyAwLC0wLjI5NTYyNSAtMC4wMTMzMiwtMC41ODM4ODEgLTAuMDMxMjUsLTAuODc1IEMgMTMuODM5ODgzLDEwLjUxMTI2NiAxMy43NTg1NTUsOS45MzY4NTk0IDEzLjY1NjI1LDkuMzc1IDEzLjU1MTQwNiw4LjgwODY1NzggMTMuNDE5MDc4LDguMjU5ODgwOSAxMy4yNSw3LjcxODc1IDEzLjE2NzI4NSw3LjQ1MDE5NTMgMTMuMDk3NzM0LDcuMTk5MDkzOCAxMyw2LjkzNzUgMTIuODAzMjQyLDYuNDE0NzM0NCAxMi41NjUsNS44OTg1IDEyLjMxMjUsNS40MDYyNSAxMi4zMDgyLDUuMzk3NTMgMTIuMzE2NSw1LjM4MzkwMSAxMi4zMTI1LDUuMzc1IDEyLjI4ODIxNyw1LjMyMjczMTYgMTIuMjY3MzQ3LDUuMjc0NDY4OCAxMi4yNSw1LjIxODc1IDEyLjIzNzk5LDUuMTc2NjM0NiAxMi4yMjY3MzksNS4xMzc2MzU4IDEyLjIxODc1LDUuMDkzNzUgMTIuMjAxMTk5LDUuMDA4MDY4NCAxMi4xODc1LDQuOTAzMzc1IDEyLjE4NzUsNC44MTI1IDEyLjE4NzUsNC4wODU1IDEyLjc3MywzLjUgMTMuNSwzLjUgeiBNIDguNzUsNS45Mzc1IGMgMC4zNzk0MTEzLDAgMC43MzExNDMzLDAuMTc2NTA4OSAwLjk2ODc1LDAuNDM3NSAwLjA3OTIwMiwwLjA4Njk5NyAwLjEzMjQyODksMC4xNzY2NDA5IDAuMTg3NSwwLjI4MTI1IEwgOS45Mzc1LDYuNjI1IGMgMC4wMTkyMzIsMC4wMzc1MjcgMC4wMTI0MTEsMC4wODcyNDEgMC4wMzEyNSwwLjEyNSAwLjU4OTAzMywxLjE4MDYyMTkgMC45ODg3NiwyLjQ4MDY5NjQgMS4xNTYyNSwzLjg0Mzc1IDAuMDU1ODMsMC40NTQzNTEgMC4wOTM3NSwwLjkwNTI2OCAwLjA5Mzc1LDEuMzc1IGwgMCwwLjAzMTI1IDAsMC4wMzEyNSBjIDAsMS45MjQgLTAuNDU5MjUsMy43NDA3NSAtMS4yODEyNSw1LjM0Mzc1IEwgOS45MDYyNSwxNy4zNDM3NSBjIC0wLjIyMDI4NDMsMC40MTg0NzkgLTAuNjE5MTE4MiwwLjcxODc1IC0xLjEyNSwwLjcxODc1IC0wLjcyNiwwIC0xLjMxMjUsLTAuNTg0NSAtMS4zMTI1LC0xLjMxMjUgMCwtMC4yMDU3NDQgMC4wNDA1NjUsLTAuMzg5MDQ5IDAuMTI1LC0wLjU2MjUgTCA3LjU2MjUsMTYuMTU2MjUgYyAwLjYzNCwtMS4yMzcgMSwtMi42NCAxLC00LjEyNSBsIDAsLTAuMDMxMjUgLTAuMDMxMjUsMCAwLC0wLjAzMTI1IGMgMCwtMS40ODUgLTAuMzM0NzUsLTIuODg5IC0wLjk2ODc1LC00LjEyNSBsIDAuMDMxMjUsMCBDIDcuNTQ1NTU3LDcuNzUyMzAxOCA3LjQ5NTc2NDIsNy42NjA0MzU3IDcuNDY4NzUsNy41NjI1IDcuNDQxNzM1Nyw3LjQ2NDU2NDMgNy40Mzc1LDcuMzYwNTU5MSA3LjQzNzUsNy4yNSA3LjQzNzUsNi41MjMgOC4wMjQsNS45Mzc1IDguNzUsNS45Mzc1IHoiICAgICBpZD0icGF0aDExIiAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIgICAgIHRyYW5zZm9ybT0ibWF0cml4KDAuODQ3NDU3NjIsMCwwLDAuODQ3NDU3NjIsMS44MzA1MDg1LDEuODMwNTA4NSkiIC8+PC9zdmc+";
		if (!get_option( 'ml_pb_app_id', false ) && Mobiloud_Admin::no_push_keys() && !get_option( 'ml_user_email' )) {
			self::$admin_screens[] = add_menu_page( 'MobiLoud', 'MobiLoud', 'activate_plugins', 'mobiloud', array(
				'Mobiloud_Admin',
				'menu_get_init'
				), $image, '25.90239843109' );
		} else {
			self::$admin_screens[] = add_submenu_page( 'mobiloud', 'Configuration', 'Configuration', "activate_plugins", 'mobiloud', array(
				'Mobiloud_Admin',
				'menu_get_started'
			) );
			self::$admin_screens[] = add_menu_page( 'MobiLoud', 'MobiLoud', 'activate_plugins', 'mobiloud', array(
				'Mobiloud_Admin',
				'menu_get_started'
				), $image, '25.90239843209' );
			self::$admin_screens[] = add_submenu_page( 'mobiloud', 'Push Notification', 'Push Notifications', "publish_posts", 'mobiloud_push', array(
				'Mobiloud_Admin',
				'menu_push'
			) );
		}
	}

	private static function set_default_options() {
		if ( is_null( get_option( 'ml_popup_message_on_mobile_active', null ) ) ) {
			add_option( "ml_popup_message_on_mobile_active", false );
		}
		if ( is_null( get_option( 'ml_automatic_image_resize', null ) ) ) {
			add_option( "ml_automatic_image_resize", false );
		}

		if ( get_option( 'affiliate_link', null ) == null ) {

			Mobiloud::set_option( 'affiliate_link', null );

			$affiliates = array( "themecloud" => "#_l_1c" );

			foreach ( $affiliates as $affiliate => $id ) {
				if ( isset( $_SERVER[ $affiliate ] ) ) {
					Mobiloud::set_option( 'affiliate_link', $id );

				}
			}

		}

	}

	private static function admin_redirect() {
		if ( get_transient( 'ml_activation_redirect' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			delete_transient( 'ml_activation_redirect' );
			if ( isset( $_GET[ 'activate-multi' ] ) ) {
				return;
			}

			wp_safe_redirect( add_query_arg( array( 'page' => 'mobiloud' ), get_admin_url( NULL, 'admin.php' ) ) );
		}
	}

	private static function register_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_register_script( 'google_chart', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'google_chart' );

		wp_register_script( 'mobiloud-forms', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-forms.js', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-forms' );

		wp_register_script( 'mobiloud-contact', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-contact.js', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-contact' );

		wp_register_script( 'mobiloud-push', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-push.js', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-push' );

		wp_register_script( 'mobiloud-editor', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-editor.js', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-editor' );

		wp_register_script( 'mobiloud-menu-config', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-menu-config.js', array(
			'jquery',
			'jquery-ui-sortable'
			), MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-menu-config' );

		wp_register_script( 'mobiloud-app-simulator', MOBILOUD_PLUGIN_URL . 'assets/js/mobiloud-app-simulator.js', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'mobiloud-app-simulator' );

		wp_register_style( 'mobiloud-iphone', MOBILOUD_PLUGIN_URL . "/css/iphone.css", false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_style( "mobiloud-iphone" );

		wp_register_script( 'jquerychosen', MOBILOUD_PLUGIN_URL . '/libs/chosen/chosen.jquery.min.js', array( 'jquery' ), false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'jquerychosen' );

		wp_register_script( 'iscroll', MOBILOUD_PLUGIN_URL . '/libs/iscroll/iscroll.js', array( 'jquery' ), false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'iscroll' );

		wp_register_script( 'resizecrop', MOBILOUD_PLUGIN_URL . '/libs/jquery.resizecrop-1.0.3.min.js', array( 'jquery' ), false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'resizecrop' );

		wp_register_script( 'imgliquid', MOBILOUD_PLUGIN_URL . '/libs/imgliquid/jquery.imgliquid.js', array( 'jquery' ), false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'imgliquid' );

		wp_register_script( 'areyousure', MOBILOUD_PLUGIN_URL . 'libs/jquery.are-you-sure.js', array( 'jquery' ), false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_script( 'areyousure' );

		wp_register_style( 'jquerychosen-css', MOBILOUD_PLUGIN_URL . "/libs/chosen/chosen.css", false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_style( "jquerychosen-css" );

		wp_register_style( 'mobiloud-dashicons', MOBILOUD_PLUGIN_URL . "/libs/dashicons/css/dashicons.css", false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_style( "mobiloud-dashicons" );

		wp_register_style( 'mobiloud-style', MOBILOUD_PLUGIN_URL . "/assets/css/mobiloud-style-33.css", false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_style( "mobiloud-style" );

		wp_register_style( 'mobiloud_admin_post', MOBILOUD_PLUGIN_URL . '/post/css/post.css', false, MOBILOUD_PLUGIN_VERSION );
		wp_enqueue_style( "mobiloud_admin_post" );


		if ( get_bloginfo( 'version', 'raw' ) < 4.4 ) {
			wp_register_style( 'mobiloud-style-legacy', MOBILOUD_PLUGIN_URL . "/assets/css/mobiloud-style-legacy.css", false, MOBILOUD_PLUGIN_VERSION );
			wp_enqueue_style( "mobiloud-style-legacy" );
		}
	}

	public static function render_view( $view, $parent = null, $data = array() ) {
		if ( $parent === null ) {
			$parent = $view;
		}
		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $val ) {
				$$key = $val;
			}
		}
		if ( 'get_started' == $parent) {
			define( 'ml_with_sidebar', true);
			define( 'ml_with_form', true);
			if ( 'settings_editor' == $view) {
				define( 'no_submit_button', true);
			}
		} elseif ('push' == $parent) {
			define( 'ml_with_sidebar', true);
		}

		include MOBILOUD_PLUGIN_DIR . 'views/header.php';

		if ( file_exists( MOBILOUD_PLUGIN_DIR . 'views/header_' . $parent . '.php' ) ) {
			include MOBILOUD_PLUGIN_DIR . 'views/header_' . $parent . '.php';
		}

		include MOBILOUD_PLUGIN_DIR . 'views/' . $view . '.php';

		include MOBILOUD_PLUGIN_DIR . 'views/footer.php';
	}

	public static function render_part_view( $view, $data = array(), $static = false ) {
		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $val ) {
				$$key = $val;
			}
		}
		if ( $static ) {
			include MOBILOUD_PLUGIN_DIR . 'views/static/' . $view . '.php';
		} else {
			include MOBILOUD_PLUGIN_DIR . 'views/' . $view . '.php';
		}
	}

	public static function check_mailing_list_alert() {
		//check if maillist not alerted and initial details saved
		if ( Mobiloud::get_option( 'ml_maillist_alert', '' ) === '' && Mobiloud::get_option( 'ml_initial_details_saved', '' ) === true ) {
			self::track_user_event( 'mailinglist_signup' );
			Mobiloud::set_option( 'ml_maillist_alert', true );
		}

		// testing
		// Mobiloud::set_option('ml_maillist_alert', false);
		// Mobiloud::set_option('ml_initial_details_saved', false);

	}

	public static function menu_get_init() {
		self::render_part_view( 'init' );
	}

	public static function menu_get_started() {
		if ( count( $_POST ) ) {
			self::flush_cache();
		}
		if ( ! isset( $_GET['tab'] ) ) {
			$_GET['tab'] = 'design';
		}
		$tab = sanitize_text_field( $_GET['tab'] );
		switch ( $tab ) {
			default:
			case 'design':
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_media();
				wp_enqueue_style( 'wp-color-picker' );

				wp_register_script( 'mobiloud-app-preview-js', MOBILOUD_PLUGIN_URL . '/assets/js/mobiloud-app-preview.js', array( 'jquery' ), MOBILOUD_PLUGIN_VERSION );
				wp_enqueue_script( 'mobiloud-app-preview-js' );

				wp_register_style( 'mobiloud-app-preview', MOBILOUD_PLUGIN_URL . "/assets/css/mobiloud-app-preview.css", false, MOBILOUD_PLUGIN_VERSION );
				wp_enqueue_style( "mobiloud-app-preview" );

				global $current_user;
				wp_get_current_user();

				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					Mobiloud::set_option( 'ml_preview_upload_image', sanitize_text_field( $_POST['ml_preview_upload_image'] ) );
					Mobiloud::set_option( 'ml_preview_theme_color', sanitize_text_field( $_POST['ml_preview_theme_color'] ) );

					Mobiloud::set_option( 'ml_article_list_view_type', sanitize_text_field( $_POST['ml_article_list_view_type'] ) );

					if ( ! isset( $_POST['ml_show_android_cat_tabs'] ) ) {
						$_POST['ml_show_android_cat_tabs'] = 'false';
					}
					Mobiloud::set_option( 'ml_show_android_cat_tabs', ( $_POST['ml_show_android_cat_tabs'] == 'true' ) );

					self::set_task_status( 'design', 'complete' );
				}

				if ( strlen( trim( get_option( 'ml_preview_theme_color' ) ) ) <= 2 ) {
					update_option( "ml_preview_theme_color", '#1e73be' );
				}

				$root_url              = network_site_url( '/' );
				$plugins_url           = plugins_url();
				$mobiloudPluginUrl     = MOBILOUD_PLUGIN_URL;
				$mobiloudPluginVersion = MOBILOUD_PLUGIN_VERSION;
				$appname               = get_bloginfo( 'name' );

				self::render_view( 'get_started_design', 'get_started' );
				break;
			case 'menu_config':


				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					ml_remove_all_categories();
					if ( isset( $_POST['ml-menu-categories'] ) && count( $_POST['ml-menu-categories'] ) ) {
						foreach ( $_POST['ml-menu-categories'] as $cat_ID ) {
							ml_add_category( sanitize_text_field( $cat_ID ) );
						}
					}

					$menu_terms = array();
					if ( !empty( $_POST['ml-menu-terms'] ) ) {
						foreach ( $_POST['ml-menu-terms'] as $term ) {
							$menu_terms[] = $term;
						}
					}
					Mobiloud::set_option( 'ml_menu_terms', $menu_terms );

					$menu_tags = array();
					if ( isset( $_POST['ml-menu-tags'] ) && count( $_POST['ml-menu-tags'] ) ) {
						foreach ( $_POST['ml-menu-tags'] as $tag ) {
							$menu_tags[] = $tag;
						}
					}
					Mobiloud::set_option( 'ml_menu_tags', $menu_tags );

					ml_remove_all_pages();
					if ( isset( $_POST['ml-menu-pages'] ) && count( $_POST['ml-menu-pages'] ) ) {
						foreach ( $_POST['ml-menu-pages'] as $page_ID ) {
							ml_add_page( sanitize_text_field( $page_ID ) );
						}
					}

					$menu_links = array();
					if ( isset( $_POST['ml-menu-links'] ) && count( $_POST['ml-menu-links'] ) ) {
						foreach ( $_POST['ml-menu-links'] as $menu_link ) {
							$menu_link_vals = explode( ":=:", $menu_link );
							$menu_links[]   = array(
								'urlTitle' => sanitize_text_field( $menu_link_vals[0] ),
								'url'      => sanitize_text_field( $menu_link_vals[1] ),
							);
						}
					}
					Mobiloud::set_option( 'ml_menu_urls', $menu_links );

					Mobiloud::set_option( 'ml_menu_show_favorites', $_POST['ml_menu_show_favorites'] == 'true' );

					self::set_task_status( 'menu_config', 'complete' );
					self::track_user_event( 'menu_config_saved' );
				}
				self::render_view( 'get_started_menu_config', 'get_started' );
				self::track_user_event( 'view_get_started_menu_config' );
				break;
			case 'test_app':
				$plugin_url    = str_replace( "mobiloud-mobile-app-plugin/", "", MOBILOUD_PLUGIN_URL );
				$check_url     = 'https://simulator.mobiloud.com/check.php?url=' . urlencode( MOBILOUD_PLUGIN_URL );
				$loadDemo      = false;
				$check_content = @file_get_contents( $check_url );
				$error_reason  = '';
				if ( self::isJson( $check_content ) ) {
					$check_result = json_decode( $check_content, true );
					if ( isset( $check_result['error'] ) ) {
						$loadDemo     = true;
						$error_reason = $check_result['error'];
					}
				} else {
					$loadDemo     = true;
					$error_reason = 'we are unable to reach your site';
				}
				$params_array = array( 'plugin_url' => urldecode( $plugin_url ) );
				$params       = urlencode( json_encode( $params_array ) );

				self::render_view( 'get_started_test_app', 'get_started', compact( 'loadDemo', 'params', 'error_reason' ) );
				self::track_user_event( 'view_get_started_test_app' );
				self::set_task_status( 'test_app', 'complete' );
				break;
			case 'settings':
				wp_enqueue_media();
				wp_register_script( 'mobiloud-settings', MOBILOUD_PLUGIN_URL . '/assets/js/mobiloud-settings.js', array( 'jquery' ) );
				wp_enqueue_script( 'mobiloud-settings' );
				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					if ( isset( $_POST['ml_app_name'] ) ) {
						Mobiloud::set_option( 'ml_app_name', sanitize_text_field( $_POST['ml_app_name'] ) );
					}
					Mobiloud::set_option( 'ml_show_email_contact_link', isset( $_POST['ml_show_email_contact_link'] ) );
					Mobiloud::set_option( 'ml_contact_link_email', sanitize_text_field( $_POST['ml_contact_link_email'] ) );
					Mobiloud::set_option( 'ml_copyright_string', sanitize_text_field( $_POST['ml_copyright_string'] ) );

					switch ( $_POST['homepagetype'] ) {
						case 'ml_home_article_list_enabled':
							Mobiloud::set_option( 'ml_home_article_list_enabled', true );
							Mobiloud::set_option( 'ml_home_page_enabled', false );
							Mobiloud::set_option( 'ml_home_url_enabled', false );
							break;
						case 'ml_home_page_enabled':
							Mobiloud::set_option( 'ml_home_article_list_enabled', false );
							Mobiloud::set_option( 'ml_home_page_enabled', true );
							Mobiloud::set_option( 'ml_home_url_enabled', false );
							break;
						case 'ml_home_url_enabled':
							Mobiloud::set_option( 'ml_home_article_list_enabled', false );
							Mobiloud::set_option( 'ml_home_page_enabled', false );
							Mobiloud::set_option( 'ml_home_url_enabled', true );
							break;
					}
					Mobiloud::set_option( 'ml_home_page_id', sanitize_text_field( $_POST['ml_home_page_id'] ) );
					Mobiloud::set_option( 'ml_home_url', sanitize_text_field( $_POST['ml_home_url'] ) );

					Mobiloud::set_option( 'ml_show_article_list_menu_item', isset( $_POST['ml_show_article_list_menu_item'] ) );
					Mobiloud::set_option( 'ml_article_list_menu_item_title', sanitize_text_field( $_POST['ml_article_list_menu_item_title'] ) );

					if ( isset( $_POST['ml_datetype'] ) ) {
						Mobiloud::set_option( 'ml_datetype', sanitize_text_field( $_POST['ml_datetype'] ) );
					}
					if ( isset( $_POST['ml_dateformat'] ) ) {
						Mobiloud::set_option( 'ml_dateformat', sanitize_text_field( $_POST['ml_dateformat'] ) );
					}
					Mobiloud::set_option( 'ml_article_list_enable_dates', isset( $_POST['ml_article_list_enable_dates'] ) );
					Mobiloud::set_option( 'ml_article_list_show_excerpt', isset( $_POST['ml_article_list_show_excerpt'] ) );
					Mobiloud::set_option( 'ml_article_list_show_comment_count', isset( $_POST['ml_article_list_show_comment_count'] ) );
					Mobiloud::set_option( 'ml_original_size_image_list', isset( $_POST['ml_original_size_image_list'] ) );

					$ml_articles_per_request = !empty($_POST['ml_articles_per_request']) ? absint( $_POST['ml_articles_per_request'] ) : 15;
					$ml_articles_per_request = max(array(1,  min(array($ml_articles_per_request, 100))));
					Mobiloud::set_option( 'ml_articles_per_request', $ml_articles_per_request );

					Mobiloud::set_option( 'ml_main_screen_tax_list', !empty($_POST['ml_main_screen_tax_list']) ? $_POST['ml_main_screen_tax_list'] : array() );

					Mobiloud::set_option( 'sticky_category_1', sanitize_text_field( $_POST['sticky_category_1'] ) );
					Mobiloud::set_option( 'ml_sticky_category_1_posts', sanitize_text_field( $_POST['ml_sticky_category_1_posts'] ) );
					Mobiloud::set_option( 'sticky_category_2', sanitize_text_field( $_POST['sticky_category_2'] ) );
					Mobiloud::set_option( 'ml_sticky_category_2_posts', sanitize_text_field( $_POST['ml_sticky_category_2_posts'] ) );

					$include_post_types = '';
					if ( isset( $_POST['postypes'] ) && count( $_POST['postypes'] ) ) {
						$include_post_types = implode( ",", $_POST['postypes'] );
					}
					Mobiloud::set_option( 'ml_article_list_include_post_types', sanitize_text_field( $include_post_types ) );

					$categories         = get_categories(array( 'hide_empty' => false));
					$exclude_categories = array();
					if ( count( $categories ) ) {
						foreach ( $categories as $category ) {
							if ( ! isset( $_POST['categories'] ) || count( $_POST['categories'] ) === 0 || ( isset( $_POST['categories'] ) && ! in_array( wp_slash( html_entity_decode( $category->cat_name ) ), $_POST['categories'] ) ) ) {
								$exclude_categories[] = $category->cat_name;
							}
						}
					}

					Mobiloud::set_option( 'ml_article_list_exclude_categories', implode( ",", $exclude_categories ) );
					Mobiloud::set_option( 'ml_restrict_search_results', isset( $_POST['ml_restrict_search_results'] ) );

					Mobiloud::set_option( 'ml_custom_field_enable', isset( $_POST['ml_custom_field_enable'] ) );
					Mobiloud::set_option( 'ml_custom_field_name', sanitize_text_field( $_POST['ml_custom_field_name'] ) );

					Mobiloud::set_option( 'ml_eager_loading_enable', isset( $_POST['ml_eager_loading_enable'] ) );
					Mobiloud::set_option( 'ml_hierarchical_pages_enabled', isset( $_POST['ml_hierarchical_pages_enabled'] ) );
					Mobiloud::set_option( 'ml_cache_enabled', isset( $_POST['ml_cache_enabled'] ) );
					Mobiloud::set_option( 'ml_image_cache_preload', isset( $_POST['ml_image_cache_preload'] ) );
					Mobiloud::set_option( 'ml_remove_unused_shortcodes', isset( $_POST['ml_remove_unused_shortcodes'] ) );
					Mobiloud::set_option( 'ml_fix_rsssl', isset( $_POST['ml_fix_rsssl'] ) );
					Mobiloud::set_option( 'ml_rtl_text_enable', isset( $_POST['ml_rtl_text_enable'] ) );
					Mobiloud::set_option( 'ml_internal_links', isset( $_POST['ml_internal_links'] ) );
					Mobiloud::set_option( 'ml_followimagelinks', ( isset( $_POST['ml_followimagelinks'] ) ? intval( $_POST['ml_followimagelinks'] ) : 0 ) );
					Mobiloud::set_option( 'ml_show_article_featuredimage', isset( $_POST['ml_show_article_featuredimage'] ) );
					Mobiloud::set_option( 'ml_original_size_featured_image', isset( $_POST['ml_original_size_featured_image'] ) );
					Mobiloud::set_option( 'ml_post_author_enabled', isset( $_POST['ml_post_author_enabled'] ) );
					Mobiloud::set_option( 'ml_page_author_enabled', isset( $_POST['ml_page_author_enabled'] ) );
					Mobiloud::set_option( 'ml_post_date_enabled', isset( $_POST['ml_post_date_enabled'] ) );
					Mobiloud::set_option( 'ml_page_date_enabled', isset( $_POST['ml_page_date_enabled'] ) );
					Mobiloud::set_option( 'ml_post_title_enabled', isset( $_POST['ml_post_title_enabled'] ) );
					Mobiloud::set_option( 'ml_page_title_enabled', isset( $_POST['ml_page_title_enabled'] ) );

					Mobiloud::set_option( 'ml_custom_field_url', sanitize_text_field( $_POST['ml_custom_field_url'] ) );
					Mobiloud::set_option( 'ml_custom_featured_image', sanitize_text_field( $_POST['ml_custom_featured_image'] ) );

					Mobiloud::set_option( 'ml_comments_system', sanitize_text_field( $_POST['ml_comments_system'] ) );
					Mobiloud::set_option( 'ml_disqus_shortname', sanitize_text_field( $_POST['ml_disqus_shortname'] ) );

					Mobiloud::set_option( 'ml_subscriptions_enable', isset( $_POST['ml_subscriptions_enable'] ) );

					Mobiloud::set_option( 'ml_cache_expiration', !empty($_POST['ml_cache_expiration']) ? absint( $_POST['ml_cache_expiration'] ) : 30 );
				}
				self::render_view( 'settings_settings', 'get_started' );
				self::track_user_event( 'view_settings_settings' );
				break;
			case 'analytics':
				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					Mobiloud::set_option( 'ml_google_tracking_id', sanitize_text_field( $_POST['ml_google_tracking_id'] ) );
					Mobiloud::set_option( 'ml_fb_app_id', sanitize_text_field( $_POST['ml_fb_app_id'] ) );
					Mobiloud::set_option( 'ml_qm_api_key', sanitize_text_field( $_POST['ml_qm_api_key'] ) );
				}
				self::render_view( 'settings_analytics', 'get_started' );
				self::track_user_event( 'view_settings_analytics' );
				break;
			case 'advertising':
				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					Mobiloud::set_option( 'ml_advertising_platform', sanitize_text_field( $_POST['ml_advertising_platform'] ) );

					//iOS
					Mobiloud::set_option( 'ml_ios_phone_banner_unit_id', sanitize_text_field( $_POST['ml_ios_phone_banner_unit_id'] ) );
					Mobiloud::set_option( 'ml_ios_tablet_banner_unit_id', sanitize_text_field( $_POST['ml_ios_tablet_banner_unit_id'] ) );
					Mobiloud::set_option( 'ml_ios_banner_position', sanitize_text_field( $_POST['ml_ios_banner_position'] ) );
					Mobiloud::set_option( 'ml_ios_interstitial_unit_id', sanitize_text_field( $_POST['ml_ios_interstitial_unit_id'] ) );
					Mobiloud::set_option( 'ml_ios_interstitial_interval', (int) sanitize_text_field( $_POST['ml_ios_interstitial_interval'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_unit_id', sanitize_text_field( $_POST['ml_ios_native_ad_unit_id'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_interval', (int) sanitize_text_field( $_POST['ml_ios_native_ad_interval'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_type', sanitize_text_field( $_POST['ml_ios_native_ad_type'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_article_unit_id', sanitize_text_field( $_POST['ml_ios_native_ad_article_unit_id'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_article_position', sanitize_text_field( $_POST['ml_ios_native_ad_article_position'] ) );
					Mobiloud::set_option( 'ml_ios_native_ad_article_type', sanitize_text_field( $_POST['ml_ios_native_ad_article_type'] ) );


					//Android
					Mobiloud::set_option( 'ml_android_phone_banner_unit_id', sanitize_text_field( $_POST['ml_android_phone_banner_unit_id'] ) );
					Mobiloud::set_option( 'ml_android_tablet_banner_unit_id', sanitize_text_field( $_POST['ml_android_tablet_banner_unit_id'] ) );
					Mobiloud::set_option( 'ml_android_banner_position', sanitize_text_field( $_POST['ml_android_banner_position'] ) );
					Mobiloud::set_option( 'ml_android_interstitial_unit_id', sanitize_text_field( $_POST['ml_android_interstitial_unit_id'] ) );
					Mobiloud::set_option( 'ml_android_interstitial_interval', (int) sanitize_text_field( $_POST['ml_android_interstitial_interval'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_unit_id', sanitize_text_field( $_POST['ml_android_native_ad_unit_id'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_interval', (int) sanitize_text_field( $_POST['ml_android_native_ad_interval'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_type', sanitize_text_field( $_POST['ml_android_native_ad_type'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_article_unit_id', sanitize_text_field( $_POST['ml_android_native_ad_article_unit_id'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_article_position', sanitize_text_field( $_POST['ml_android_native_ad_article_position'] ) );
					Mobiloud::set_option( 'ml_android_native_ad_article_type', sanitize_text_field( $_POST['ml_android_native_ad_article_type'] ) );
				}
				self::render_view( 'settings_advertising', 'get_started' );
				self::track_user_event( 'view_settings_advertising' );
				break;
			case 'editor':
				self::render_view( 'settings_editor', 'get_started' );
				self::track_user_event( 'view_settings_editor' );
				break;
			case 'push':
				/**
				* Process Form
				*/
				if ( count( $_POST ) && check_admin_referer( 'ml-form-' . $tab ) ) {
					Mobiloud::set_option( 'ml_push_notification_enabled', isset( $_POST['ml_push_notification_enabled'] ) );
					Mobiloud::set_option( 'ml_pb_use_ssl', isset( $_POST['ml_pb_use_ssl'] ) );

					$include_post_types = '';
					if ( isset( $_POST['postypes'] ) && count( $_POST['postypes'] ) ) {
						$include_post_types = implode( ",", $_POST['postypes'] );
					}
					Mobiloud::set_option( 'ml_push_post_types', sanitize_text_field( $include_post_types ) );

					if ( isset( $_POST['ml_push_notification_categories'] ) ) {
						ml_push_notification_categories_clear();
						ml_push_notification_taxonomies_clear();
						if ( is_array( $_POST['ml_push_notification_categories'] ) ) {
							$tax_list = array();
							foreach ( $_POST['ml_push_notification_categories'] as $categoryID ) {
								if (0 === strpos($categoryID, 'tax:' )) {
									$tax_list[] = absint(str_replace('tax:', '', $categoryID));
								} else {
									ml_push_notification_categories_add( $categoryID );
								}
							}
							ml_push_notification_taxonomies_set($tax_list);
						}
					} else {
						ml_push_notification_categories_clear();
						ml_push_notification_taxonomies_clear();
					}

					Mobiloud::set_option( 'ml_pb_together', isset( $_POST['ml_pb_together'] ) );
					Mobiloud::set_option( 'ml_pb_chunk', max(array(100, absint( $_POST['ml_pb_chunk'] ))));
					Mobiloud::set_option( 'ml_pb_rate', max(array(1, absint( $_POST['ml_pb_rate'] ))));

					Mobiloud::set_option( 'ml_pb_no_tags', isset( $_POST['ml_pb_no_tags'] ) );
					Mobiloud::set_option( 'ml_pb_log_enabled', isset( $_POST['ml_pb_log_enabled'] ) );

					Mobiloud::set_option( 'ml_push_service', absint( $_POST['ml_push_service'] ) );
					Mobiloud::set_option( 'ml_pb_app_id', sanitize_text_field( $_POST['ml_pb_app_id'] ) );
					Mobiloud::set_option( 'ml_pb_secret_key', sanitize_text_field( $_POST['ml_pb_secret_key'] ) );
					Mobiloud::set_option( 'ml_onesignal_app_id', sanitize_text_field( $_POST['ml_onesignal_app_id'] ) );
					Mobiloud::set_option( 'ml_onesignal_secret_key', sanitize_text_field( $_POST['ml_onesignal_secret_key'] ) );

					$migrate_allowed = !empty( $_POST['ml_push_migrate_mode'] ) && Mobiloud::get_option( 'ml_pb_app_id' ) && Mobiloud::get_option( 'ml_pb_secret_key' )
					&& Mobiloud::get_option( 'ml_onesignal_app_id' ) && Mobiloud::get_option( 'ml_onesignal_secret_key' );
					Mobiloud::set_option( 'ml_push_migrate_mode', $migrate_allowed );
				}
				self::render_view( 'settings_push', 'get_started' );
				self::track_user_event( 'view_push_settings' );
				break;
		}
		if ( is_null( get_option( 'ml_license_tracked', null ) ) && strlen( Mobiloud::get_option( 'ml_pb_app_id' ) ) >= 0
		&& strlen( Mobiloud::get_option( 'ml_pb_secret_key' ) ) >= 0
		) {
			ml_track( 'License details saved', array( 'perfect_audience' ) );
			update_option( 'ml_license_tracked', true );
		}
	}

	public static function menu_push() {
		if ( count( $_POST ) ) {
			self::flush_cache();
		}

		if ( ! isset( $_GET['tab'] ) ) {
			$_GET['tab'] = '';
		}

		$tab = sanitize_text_field( $_GET['tab'] );
		switch ( $tab ) {
			default:
			case 'notifications':
				self::render_view( 'push_notifications', 'push' );
				self::track_user_event( 'view_push_notifications' );
				break;
		}
	}

	/**
	* Get list of tasks for "Get Started" page
	* @return array
	*/
	public static function get_started_tasks() {
		return self::$get_started_tasks;
	}

	/**
	* Get task CSS class (default, act ve, complete)
	*
	* @param string $task
	*/
	public static function get_task_class( $task ) {
		$class = '';
		if ( ! isset( $_GET['tab'] ) ) {
			$_GET['tab'] = '';
		}

		$tab = sanitize_text_field( $_GET['tab'] );
		if ( $task == $tab || ( ! isset( $_GET['tab'] ) && $task == 'design' ) ) {
			$class = 'current';
		}

		$class .= ' ' . self::get_task_status( $task );

		return $class;
	}

	public static function set_task_status( $task, $status ) {
		$task_statuses = Mobiloud::get_option( 'ml_get_start_tasks', false );
		if ( $task_statuses === false ) {
			$task_statuses = array(
				$task => $status
			);
		} else {
			$task_statuses[ $task ] = $status;
		}
		Mobiloud::set_option( 'ml_get_start_tasks', $task_statuses );
	}

	public static function get_task_status( $task ) {
		$task_statuses = Mobiloud::get_option( 'ml_get_start_tasks', false );
		if ( $task_statuses !== false && isset( $task_statuses[ $task ] ) ) {
			return $task_statuses[ $task ];
		}

		return 'incomplete';
	}

	private static function isJson( $string ) {
		json_decode( $string );

		return strlen( $string ) > 0;
	}

	public static function save_initial_data() {
		$email = $_POST['ml_email'];
		$url = $_POST['ml_site'];
		$name = !empty($_POST['ml_name']) ? $_POST['ml_name'] : '';
		$type = !empty($_POST['ml_apptype']) ? $_POST['ml_apptype'] : '';
		$source = 'plugin-mobiloud';

		Mobiloud::set_option( 'ml_initial_details_saved', true );
		Mobiloud::set_option( 'ml_user_name', sanitize_text_field( $name ) );
		Mobiloud::set_option( 'ml_user_email', sanitize_text_field( $email ) );
		Mobiloud::set_option( 'ml_user_site', sanitize_text_field( $url ) );
		Mobiloud::set_option( 'ml_user_apptype', sanitize_text_field( $type ) );
		//Mobiloud::set_option( 'ml_user_sitetype', sanitize_text_field( $_POST['ml_sitetype'] ) );
		//Mobiloud::set_option( 'ml_join_mailinglist', sanitize_text_field( $_POST['ml_maillist'] ) );
		wp_remote_post('https://mobiloud.com/account-create/', array( 'body' => array('email' => $email, 'site' => $url, 'name' => $name, 'type' => $type, 'source' => $source), 'timeout' => 15, 'sslverify' => false)); // call endpoint

		echo "1";
		die();
	}

	public static function save_editor() {
		if ( isset( self::$editor_sections[ $_POST['editor'] ] ) ) {
			Mobiloud::set_option( $_POST['editor'], $_POST['value'] );
			echo "1";
			die();
		}
	}

	public static function save_editor_embed() {
		$items = isset($_POST['items']) ? $_POST['items'] : array();
		if (is_array($items) && count($items)) {
			Mobiloud::set_option( 'ml_embedded_page_css', !empty($items[ 'ml_embedded_page_css' ]) ? $items[ 'ml_embedded_page_css' ] : '' );
			Mobiloud::set_option( 'ml_embedded_header_hide', !empty($items[ 'ml_embedded_header_hide' ]));
			Mobiloud::set_option( 'ml_embedded_footer_hide', !empty($items[ 'ml_embedded_footer_hide' ]));
			Mobiloud::set_option( 'ml_embedded_android_name', !empty($items[ 'ml_embedded_android_name' ]) ? $items[ 'ml_embedded_android_name' ] : '' );
			echo "1";
			die();
		}
	}

	public static function save_banner() {
		if ( isset( self::$banner_positions[ $_POST['position'] ] ) ) {
			Mobiloud::set_option( $_POST['position'], $_POST['value'] );
		}
	}

	public static function track_user_event( $event ) {
		if ( Mobiloud::get_option( 'ml_initial_details_saved' ) ) {
			ml_track_mixpanel( $event );
			?>
			<script type='text/javascript'>
				Intercom("trackUserEvent", "<?php echo esc_js( $event ); ?>");
			</script>

			<?php
		}
	}

	public static function get_tax_list() {
		$list = array();
		if ( isset( $_POST['group'] ) ) {
			$group = sanitize_text_field( $_POST['group'] );
			$terms = get_terms( $group, array( 'hide_empty' => false ) );
			if ( count( $terms ) ) {

				foreach ( $terms as $term ) {
					$parent_name = '';
					if ( $term->parent ) {
						$parent_term = get_term_by( 'id', $term->parent, $group );
						if ( $parent_term ) {
							$parent_name = $parent_term->name . ' - ';
						}
					}
					$list[ $term->term_id ] = array(
						'id'       => $term->term_id,
						'fullname' => $parent_name . $term->name,
						'title'    => $term->name
					);
				}
			}
		}
		header( 'Content-Type: application/json' );
		wp_send_json( array( 'terms' => $list ) );
	}

	public static function get_pb_log_name( $web_path = false ) {
		$filename = Mobiloud::get_option('ml_pb_log_name');
		if (empty($filename)) {
			$site = str_replace(array('https://', 'http://', '/', ':'), array('', '', '_', ''), get_site_url());
			$filename = $site . '-mlpush' .  rand(10000000, 99999999) . '.txt';
			Mobiloud::set_option('ml_pb_log_name', $filename);
		}
		$paths = wp_upload_dir();
		if ($web_path) {
			return $paths[ 'baseurl' ] . '/' . $filename;
		} else {
			return $paths[ 'basedir' ] . '/' . $filename;
		}
	}

	/**
	* Add "Breaking news notification" metabox
	*
	* @param string $post_type
	* @param WP_POST $post
	*/
	public static function add_push_metabox( $post_type, $post ) {
		// show only for selected post types
		$post_types = explode( ',', Mobiloud::get_option( 'ml_push_post_types', 'post' ));
		if (in_array( $post_type, $post_types)) {
			foreach ($post_types as $post1) {
				add_meta_box(
					'ml-push-matabox',
					'Breaking news notification',
					array( 'Mobiloud_Admin', 'render_ml_push_metabox' ),
					$post1,
					'advanced',
					( 'publish' != $post->post_status ? 'high' : 'low')
				);
			}
		}
	}

	/**
	* Show an option at metabox
	*
	* @param WP_POST $post
	*/
	public static function render_ml_push_metabox($post) {
		$value = get_post_meta($post->ID, 'ml_notification_notags', true);
		$published = 'publish' == $post->post_status; // show option disabled when post published
		$global_value = Mobiloud::get_option( 'ml_pb_no_tags', false );
		$globally_enabled = !empty($global_value); // show option checked and disabled when global "send notifications without tags" checked
		?><input type="checkbox" name="ml_notification_notags<?php echo ($published || $globally_enabled) ? '_show': '' ?>" id="ml_notification_notags" value="1"
			<?php echo (!empty($value) || $globally_enabled) ? ' checked="checked"' : ''; ?><?php echo ($published || $globally_enabled) ? ' disabled="disabled"' :''; ?>>
		<label for="ml_notification_notags">Send a notification to all app users when this post is published.</label><?php
		if ($published || $globally_enabled) {
			?><input type="hidden" name="ml_notification_notags" value="<?php echo !empty($value) ? 1 : 0; ?>"><?php
		}
		?><p><em>When this option is checked, when a notification for this post is sent automatically, it will be delivered to all devices,
			irrespective of user's choices for notifications, resulting in a faster delivery.</em></p>
		<?php

	}

	public static function save_push_metabox($post_id) {
		$value = empty($_POST['ml_notification_notags']) ? 0 : 1;
		update_post_meta($post_id, 'ml_notification_notags', $value);
	}

	/**
	* Current push keys values are empty
	*
	*/
	public static function no_push_keys() {
		return ( strlen( Mobiloud::get_option( 'ml_pb_app_id' ) ) <= 0 && strlen( Mobiloud::get_option( 'ml_pb_secret_key' ) ) <= 0
			&& strlen( Mobiloud::get_option( 'ml_onesignal_app_id' ) ) <= 0 && strlen( Mobiloud::get_option( 'ml_onesignal_secret_key' ) ) <= 0);
	}

}