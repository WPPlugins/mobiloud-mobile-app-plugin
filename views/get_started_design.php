<div class="ml2-block">
	<div class="ml2-header"><h2><?php echo Mobiloud_Admin::$settings_tabs[$active_tab][ 'title' ]; ?></h2></div>
	<div class="ml2-body">

		<input type="hidden" id="hidden_homepagetype" value="<?php echo get_option( 'ml_home_article_list_enabled', true ) ? 'ml_home_article_list_enabled' : ''; ?>"/>
		<div class="ml2-wide50-l">
			<div class="ml-form-row">
				<label>Upload Your Logo</label>
				<input id="ml_preview_upload_image" type="text" size="36" name="ml_preview_upload_image"
					value="<?php echo get_option( "ml_preview_upload_image" ); ?>"/>
				<input id="ml_preview_upload_image_button" type="button" value="Upload Image" class="browser button"/>
			</div>
			<?php $logoPath = Mobiloud::get_option( "ml_preview_upload_image" ); ?>
			<div
				class="ml-form-row ml-preview-upload-image-row" <?php echo ( strlen( $logoPath ) === 0 ) ? 'style="display:none;"' : ''; ?>>
				<div class='ml-preview-image-holder'>
					<img src='<?php echo $logoPath; ?>'/>
				</div>
				<a href='#' class='ml-preview-image-remove-btn'>Remove logo</a>
			</div>
			<div class="ml-form-row">
				<label>Navigation Bar Color</label>
				<input name="ml_preview_theme_color" id="ml_preview_theme_color" type="text"
					value="<?php echo get_option( "ml_preview_theme_color" ); ?>"/>
			</div>
		</div>
		<div class="ml2-wide50-r">
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
			<div class="get_started_preview shrinked <?php echo strlen( get_option( 'ml_preview_os' ) ) ? get_option( 'ml_preview_os' ) : 'ios'; ?>">
				<div class="ml-preview-app" onlclick=""></div>
				<div id="ml_preview_loading"></div>
			</div>

			<div class="get_started_preview">
				<div style='clear:both;'></div>

			</div>
			<div class="small ml_below_preview">This is a preview for your app's navigation bar. Use the <a class="sim-btn btn thickbox_full"
				target="_blank"
				href="https://simulator.mobiloud.com/?name=<?php echo urlencode( esc_attr( $user_name ) ); ?>&email=<?php echo urlencode( esc_attr( $user_email ) ); ?>&site=<?php echo urlencode( esc_url( $user_site ) ); ?>&p=<?php echo urlencode( esc_url( $plugin_url ) ); ?>&v=<?php echo urlencode( esc_attr( $plugin_version ) ); ?>">Live
				Preview</a> to test your app.
			</div>
		</div>

		<div style='clear:both;'></div>


		<div class='ml-form-row'>
			<label>Show categories tab</label>
			<div class="ml-checkbox-wrap">
				<input type="checkbox" id="ml_show_android_cat_tabs" name="ml_show_android_cat_tabs"
					value="true" <?php echo Mobiloud::get_option( 'ml_show_android_cat_tabs' ) ? 'checked' : ''; ?>/>
				<label for="ml_show_android_cat_tabs">Show categories tab menu at the top of the screen</label>
			</div>
		</div>

		<div class="ml-form-row">
			<label>Article List Style</label>
			<div class="ml-radio-wrap">
				<input type="radio" id="ml_article_list_view_type_extended" name="ml_article_list_view_type"
					value="extended" <?php echo get_option( 'ml_article_list_view_type', 'extended' ) == 'extended' ? 'checked' : ''; ?>/>
				<label for="ml_article_list_view_type_extended">Extended (large thumbnails)</label>
			</div>
			<div class="ml-radio-wrap">
				<input type="radio" id="ml_article_list_view_type_compact" name="ml_article_list_view_type"
					value="compact" <?php echo get_option( 'ml_article_list_view_type' ) == 'compact' ? 'checked' : ''; ?>/>
				<label for="ml_article_list_view_type_compact">Compact (square thumbnails)</label>
			</div>
		</div>
		<div style='clear:both;'></div>


		<?php
		?>
	</div>
</div>