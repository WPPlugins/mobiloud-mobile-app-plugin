<?php
global $current_user;
?>
<script type="text/javascript">
	jQuery(function () {
		<?php
		// Set stored values if any
		$apptype = get_option( 'ml_user_apptype', '');
		$sitetype = get_option( 'ml_user_sitetype', '');
		if (!empty($apptype)) {
			?>jQuery('[name="apptype"][value="<?php echo esc_attr($apptype); ?>"]').prop('checked', true).trigger('click');<?php
		}
		?>
	});

	function ml_alert(message) {
		jQuery('#ml_alert').show();
		jQuery('#ml_alert').text(message);
	}

	var can_submit = false;
	function form_submit() {
		if (can_submit) {
			return true;
		}
		var ml_name = jQuery("#ml-user-name").val();
		var ml_email = jQuery("#ml-user-email").val();
		var ml_site = jQuery("#ml-user-site").val();
		var ml_apptype = jQuery('.radio_apptype:checked').val();

		if (ml_name.length <= 0 || ml_email.length <= 0 || ml_site.length <= 0 || ml_apptype == 'undefined') {
			ml_alert('Please complete all details');
			return false;
		} else {
			var data = {
				action: "ml_save_initial_data",
				ml_name: ml_name,
				ml_email: ml_email,
				ml_site: ml_site,
				ml_apptype: ml_apptype
			};
			jQuery.post(ajaxurl, data, function (response) {
				can_submit = true;
				window.location.href = '<?php echo admin_url( 'admin.php?page=mobiloud' ); ?>';
				//if ('other' == ml_apptype) {
				//	jQuery('#notice_link').trigger('click');
				//} else {
				//	jQuery('#submit').trigger('click');
				//}
			});

		}

		return can_submit;
	};
	jQuery(document).on('ready', function() {
		jQuery('#continue').on('click', function() {
			window.location.href = '<?php echo admin_url( 'admin.php?page=mobiloud' ); ?>';
			return false;
		})
		jQuery('#install').on('click', function() {
			window.location.href = 'https://mobiloud.com/';
			return false;
		})
	})

</script>
<div class="wrap about-wrap">
	<h1>Welcome to MobiLoud</h1>
	<div class="about-text">Get your app published without writing a single line of code.</div>

	<div class="ml-init-page">
		<div id="ml_alert" class="error" style="display: none;"></div>
		<!-- Start initial details block -->
		<div id='ml-initial-details' class="ml-col-onesecond-f card">
			<h3>Get a FREE demo of your app.</h3>
			<p>Preview your own app with no commitment. Pay only when you're ready to publish it.</p>
			<form action="" method="post" onsubmit="return form_submit();">
				<div class='ml-col-row'>
					<div class='ml-col-onethirds-f'>
						<p>Your Website URL</p>
					</div>
					<div class='ml-col-twothirds-f'>
						<input type="text" id="ml-user-site" name="url" placeholder="Enter your website"
							value='<?php echo Mobiloud::get_option( 'ml_site_url', get_site_url() ); ?>'
							required maxlength="256">
					</div>
				</div>

				<div class='ml-col-row'>
					<div class='ml-col-onethirds-f'>
						<p>Your Name</p>
					</div>
					<div class='ml-col-twothirds-f'>
						<input type="text" id="ml-user-name" name="name" placeholder="Enter your name"
							value='<?php echo Mobiloud::get_option( 'ml_user_name', '' ); ?>' required maxlength="256">
					</div>
				</div>

				<div class='ml-col-row'>
					<div class='ml-col-onethirds-f'>
						<p>Your Email</p>
					</div>
					<div class='ml-col-twothirds-f'>
						<input type="email" id="ml-user-email" name="email" placeholder="Enter your email"
							value='<?php echo Mobiloud::get_option( 'ml_user_email', $current_user->user_email ); ?>'
							required maxlength="256">
					</div>
				</div>
				<br/>
				<br/>
				<h3>What kind of app would you like to build?</h3>
				<div class='ml-col-row' id="form-apptype">
					<div class='ml-col-half'>
						<input type="radio" id="radio_apptype_news" class="radio_apptype" name="type" value="news" checked>
						<label for="radio_apptype_news">A News or Blog app</label>
					</div>
					<div class='ml-col-half'>
						<input type="radio" id="radio_apptype_other" class="radio_apptype" name="type" value="other">
						<label for="radio_apptype_other">Something else</label>
					</div>
				</div>

				<div class='ml-col-row ml-init-button'>
					<input type="submit" name="submit" id="submit" class="button button-hero button-primary"
						value="Get Started for FREE">

				</div>
				<div class='ml-col-row ml-any-questions'>
					<p>Got any questions? Contact us at <a href="mailto:support@mobiloud.com">support@mobiloud.com</a></p>
				</div>
			</form>
		</div>
		<!-- Learn more block -->
		<div class="ml-col-onesecond-f card">
			<h3>Learn more about MobiLoud</h3>
			<p>Effortlessly turn your WordPress website into mobile apps.</p>
			<p>Our solutions:<br>
				<a href="https://www.mobiloud.com/wordpress-news-app">News: for your blog/news sites</a><br>
				<a href="https://www.mobiloud.com/wordpress-mobile-app">Canvas: for any WordPress site</a><br>
			</p>
			<p>Learn more:<br>
				<a href="https://www.mobiloud.com/pricing">Plans &amp; Pricing</a><br>
				<a href="https://www.mobiloud.com/lifetme">Lifetime Licenses</a><br>
				<a href="https://www.mobiloud.com/help">Knowledge Base</a><br>
				<a href="https://calendly.com/pietro/mobiloud-welcome/">Schedule a call</a></p>
		</div>
	</div>
	<div class="clear"></div>

	<div id="ml_init_terms">
		<small>By signing up you agree to MobiLoud's <a target="_blank" href="https://www.mobiloud.com/terms">Terms
			of service</a> and <a target="_blank" href="https://www.mobiloud.com/privacy/">Privacy policy</a>.
		</small>
	</div>
</div> <!-- about-wrap -->

<?php add_thickbox(); ?>
<div id="notice" style="display:none;">
	<p> MobiLoud News lets you turn your site into a fully native news app.<br>
		It’s ideal for a popular news site or blog or a content site.</p>
	<p> For any site that is not a blog or news site, including directory,
		social and ecommerce sites, the new MobiLoud Canvas is the best
		solution, allowing you to convert your site into mobile apps using
		your own theme or any responsive theme.</p>

	<div class="ml-col-row ml-init-button">
		<input type="button" name="install" id="install" class="button button-hero button-primary" value="Install MobiLoud Canvas plugin">

		<input type="button" name="continue" id="continue" class="button button-hero button-primary" value="continue with MobiLoud News">
	</div>

</div>
<a href="#TB_inline?width=600&height=240&inlineId=notice" class="thickbox" style="display:none;" id="notice_link">Show Canvas</a>

