<?php
//use Intercom\IntercomBasicAuthClient;

//check if currently on mobiloud plugin page
function ml_using_mobiloud() {
	return isset( $_GET['page'] ) && strpos( $_GET['page'], 'mobiloud' ) !== false;
}

function ml_init_intercom() {

	//var_dump($user);exit;
	if ( is_admin() && current_user_can( 'administrator' ) && Mobiloud::get_option( 'ml_initial_details_saved' ) && ml_using_mobiloud() ) {
		$user_email    = Mobiloud::get_option( 'ml_user_email' );
		$user_name     = Mobiloud::get_option( 'ml_user_name' );
		$user_site     = Mobiloud::get_option( 'ml_user_site' );
		$user_apptype  = Mobiloud::get_option( 'ml_user_apptype' );
		$user_sitetype = Mobiloud::get_option( 'ml_user_sitetype' );
		$theme = wp_get_theme();
		$theme_name = $theme->Name;
		$theme_path = get_stylesheet_directory_uri();
		?>
		<script id="IntercomSettingsScriptTag">
			window.intercomSettings = {
				email: "<?php echo esc_js( $user_email ); ?>",
				name: "<?php echo esc_js( $user_name ); ?>",
				site: "<?php echo esc_js( $user_site ); ?>",
				sitetype: "<?php echo esc_js( $user_sitetype ); ?>",
				installurl: "<?php echo get_site_url(); ?>",
				sitename: "<?php echo get_bloginfo( 'name' ); ?>",
				pb_key: "<?php echo Mobiloud::get_option( 'ml_pb_app_id' ); ?>",
				version: "<?php echo MOBILOUD_PLUGIN_VERSION;?>",
				post_count: "<?php echo wp_count_posts()->publish; ?>",
				app_type: "<?php echo esc_js( $user_apptype ); ?>",
				homepage_type: "<?php echo get_option( 'show_on_front ' ); ?>",
				app_id: "h89uu5zu",
				user_id: "<?php echo esc_js( $user_email ); ?>",
				user_hash: "<?php echo hash_hmac( "sha256", $user_email, "2d8ReoNHhovD4NhWCb72DgrghadvKVwGJsR0t6YR" ); ?>",
				haswoocommerce:<?php echo( is_plugin_active( 'woocommerce/woocommerce.php' ) || class_exists( 'Woocommerce' ) ? '"yes"' : '"no"' ); ?>,
				hasbuddypress:<?php echo( is_plugin_active( 'buddypress/bp-loader.php' ) || class_exists( 'BuddyPress' ) ? '"yes"' : '"no"' ); ?>,
				theme_name: "<?php echo esc_js( $theme_name ); ?>",
				theme_path: "<?php echo esc_js( $theme_path ); ?>",
			};
		</script>
		<script>(function () {
				var w = window;
				var ic = w.Intercom;
				if (typeof ic === "function") {
					ic('reattach_activator');
					ic('update', intercomSettings);
				} else {
					var d = document;
					var i = function () {
						i.c(arguments)
					};
					i.q = [];
					i.c = function (args) {
						i.q.push(args)
					};
					w.Intercom = i;
					function l() {
						var s = d.createElement('script');
						s.type = 'text/javascript';
						s.async = true;
						s.src = 'https://widget.intercom.io/widget/h89uu5zu';
						var x = d.getElementsByTagName('script')[0];
						x.parentNode.insertBefore(s, x);
					}

					if (w.attachEvent) {
						w.attachEvent('onload', l);
					} else {
						w.addEventListener('load', l, false);
					}
				}
			})()</script>
		<?php
	}
}

function ml_track( $action, $services = array(), $loadInit = false ) {
	foreach ( $services as $service ) {
		switch ( $service ) {
			case 'mixpanel':
				ml_track_mixpanel( $action );
				break;
			case 'intercom':
				ml_track_intercom( $action, $loadInit );
				break;
		}
	}
}

function ml_track_mixpanel( $action ) {
	if ( function_exists( 'curl_version' ) ) {
		// get the Mixpanel class instance, replace with your project token
		$mp = Mixpanel::getInstance( "3e7cc38a0abe4ea3a16a0e7538144f23" );
		// track an event
		$mp->track( $action );
	}
}

function ml_track_intercom( $action, $loadInit = false ) {
	if ( is_admin() && current_user_can( 'administrator' ) ) {
		$user = wp_get_current_user();
		if ( $loadInit ) {
			ml_init_intercom();
		}
		?>
		<script type="text/javascript">
			Intercom("trackUserEvent", "<?php echo $action; ?>");
		</script>
		<?php
	}
}
?>