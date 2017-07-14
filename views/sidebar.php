<?php
$user_email     = Mobiloud::get_option( 'ml_user_email' );
$user_name      = Mobiloud::get_option( 'ml_user_name' );
$user_site      = get_site_url();
$plugin_url     = plugins_url();
$plugin_version = MOBILOUD_PLUGIN_VERSION;

$http_prefix = 'http';
if ( ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443 ) {
	$http_prefix = 'https';
}
?>
<div class="ml2-sidebar">
	<div class="ml2-preview">
		<a href="https://simulator.mobiloud.com/?name=<?php echo urlencode( esc_attr( $user_name ) ); ?>&email=<?php echo urlencode( esc_attr( $user_email ) ); ?>&site=<?php echo urlencode( esc_url( $user_site ) ); ?>&p=<?php echo urlencode( esc_url( $plugin_url ) ); ?>&v=<?php echo urlencode( esc_attr( $plugin_version ) ); ?>"
			target="_blank" class="sim-btn thickbox_full button button-hero button-primary">
			See Live Preview
		</a>
	</div>

	<div class="ml2-side-block">
		<div class="ml2-side-header">Test on your device</div>
		<div class="ml2-side-body">
			<p>You can easily test your own app using your phone or tablet.</p>
			<p><a
				href="https://www.mobiloud.com/help/knowledge-base/preview/<?php echo get_option( 'affiliate_link', null ); ?>?utm_source=wp-plugin-admin&utm_medium=web&utm_campaign=plugin-admin-get-started"
				target="_blank">Follow the guide</a></p>
		</div>
	</div>

	<?php if (Mobiloud_Admin::no_push_keys()) { ?>
		<div class="ml2-preview">
			<a href="https://www.mobiloud.com/publish/?email=<?php echo Mobiloud::get_option( 'ml_user_email', $current_user->user_email ); ?>&utm_source=wp-plugin-admin&utm_medium=web&utm_campaign=plugin-admin-get-started"
				target="_blank" class="pricing-btn button button-hero button-primary">
				See Pricing &amp; Publish Your App
			</a>
		</div>
		<?php } ?>

	<div class="ml2-side-block">
		<div class="ml2-side-header">Help & Support</div>
		<div class="ml2-side-body">
			<p>Make sure to check our FAQ section for more detail on how to build your app</p>
			<p><a href="https://www.mobiloud.com/help/" target="_blank">Frequently Asked Questions</a></p>
		</div>
	</div>
	<?php if (!Mobiloud_Admin::no_push_keys()) { ?>

		<div class="ml2-side-block">
			<div class="ml2-side-header">Like our plugin?</div>
			<div class="ml2-side-body">
				<p>If you enjoyed our plugin, don't forget to rate it with 5 stars
					at WordPress.org that helps us a lot!</p>
				<p><a href="https://wordpress.org/support/plugin/mobiloud-mobile-app-plugin/reviews/#new-post" target="_blank">Rate this plugin at WordPress.org</a></p>
			</div>
		</div>
		<?php } ?>
</div>