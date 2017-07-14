<div id="wrap" class="mobiloud wrap">
<div id="ml-header">
	<a href="<?php echo admin_url( 'admin.php?page=mobiloud' ); ?>" class="ml-logo">
		<img src="<?php echo MOBILOUD_PLUGIN_URL; ?>assets/img/mobiloud-logo-black.png"/>
	</a>

	<?php if ( Mobiloud_Admin::no_push_keys() ): ?>
		<a href="https://www.mobiloud.com/publish/?email=<?php
			global $current_user;
			echo Mobiloud::get_option( 'ml_user_email', $current_user->user_email );
			?>&utm_source=wp-plugin-admin&utm_medium=web&utm_campaign=plugin-admin-header"
			target="_blank" class="pricing-btn button-primary">
			Publish Your App
		</a>
		<p class='ml-trial-msg'>When ready to go live, pick a plan &amp; publish your app.</p>
		<?php endif; ?>
</div>