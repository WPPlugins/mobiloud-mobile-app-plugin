<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// fix broken by rsssl json at articles list
if (Mobiloud::get_option( 'ml_fix_rsssl') && !function_exists( 'ml_rsssl_comment_remover' )) {
	function ml_rsssl_comment_remover($buffer) {
		//replace the comment with empty string
		$buffer = str_replace( 'data-rsssl="1"', "", $buffer);
		return $buffer;
	}
	add_filter( "rsssl_fixer_output", "ml_rsssl_comment_remover", 10, 1);
}
?>
<!DOCTYPE html>
<html dir="<?php echo( get_option( 'ml_rtl_text_enable' ) == '1' ? 'rtl' : 'ltr' ); ?>">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta name="language" content="en"/>
		<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1, user-scalable=no">
		<link href="<?php echo plugins_url( 'mobiloud-mobile-app-plugin/post/css/styles.css' ); ?>" rel="stylesheet"
			media="all"/>
		<link href="<?php echo plugins_url( 'mobiloud-mobile-app-plugin/post/css/_typeplate.css' ); ?>" rel="stylesheet"
			media="all"/>
		<?php
		$custom_css = stripslashes( get_option( 'ml_post_custom_css' ) );
		echo $custom_css ? '<style type="text/css" media="screen">' . $custom_css . '</style>' : '';

		$custom_js = stripslashes( get_option( 'ml_post_custom_js' ) );
		echo $custom_js ? '<script>' . $custom_js . '</script>' : '';
		$GLOBALS['post'] = $post;

		/* Next line of code (with eval function) required for MobiLoud Editor settings */
		eval( stripslashes( get_option( 'ml_post_head' ) ) ); // PHP in HEAD

		echo stripslashes( get_option( 'ml_html_post_head', '' ) ); // HTML in HEAD

		/*remove_all_actions('wp_head');
		add_action( 'wp_head',             'wp_enqueue_scripts',              1     );
		add_action( 'wp_head',             'locale_stylesheet'                      );
		add_action( 'wp_head',             'wp_print_styles',                  8    );
		add_action( 'wp_head',             'wp_print_head_scripts',            9    );
		add_action( 'wp_head',             'wp_shortlink_wp_head',            10, 0 );
		wp_head();*/

		?>
	</head>
	<body class="mb_body">
		<?php
		include dirname( __FILE__ ) . '/../views/body_content.php';
		?>
	</body>
</html>