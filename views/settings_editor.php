<div class="ml2-block">
	<div class="ml2-header"><h2>Code Editor</h2></div>
	<div class="ml2-body">
		<div class='ml-col-row'>
			<p>You can use the editor to inject HTML, PHP, CSS and Javascript code in a number of positions within the
				post and page
				screens. You can reference the current post id using $post->id.</p>

			<p>Read more in our <a
				href="https://www.mobiloud.com/help/knowledge-base/using-the-code-editor/<?php echo get_option( 'affiliate_link', null ); ?>?utm_source=wp-plugin-admin&utm_medium=web&utm_campaign=editor"
				target="_blank">Knowledge Base</a>. Need any help? <a class="contact" href="mailto:support@mobiloud.com">Contact our
				support team</a>.</p>

			<p><em>Note: this is for developers and advanced users only.</em></p>

			<div class="ml-editor-controls">
				<select id="ml_admin_post_customization_select" name="ml_admin_post_customization_select">
					<option value="">
						Select a customization...
					</option>
					<?php foreach ( Mobiloud_Admin::$editor_sections as $editor_key => $editor_name ): ?>
						<option value='<?php echo esc_attr( $editor_key ); ?>' ?>
							<?php echo esc_html( $editor_name ); ?>
						</option>
						<?php endforeach; ?>
				</select>
				<a href="#" class='button-primary ml-save-editor-btn'>Save</a>
			</div>
			<textarea class='ml-editor-area ml-show'></textarea>
			<?php foreach ( Mobiloud_Admin::$editor_sections as $editor_key => $editor_name ): ?>
				<textarea class='ml-editor-area'
					name='<?php echo esc_attr( $editor_key ); ?>'><?php echo stripslashes( htmlspecialchars( Mobiloud::get_option( $editor_key, '' ) ) ); ?></textarea>
				<?php endforeach; ?>

			<h4>Preview the results</h4>
			<p>Select a post or page to preview the results of your edits.</p>
			<select id="preview_popup_post_select">
				<?php
				$posts_query         = array(
					'posts_per_page' => 10,
					'orderby'        => 'post_date',
					'order'          => 'DESC',
					'post_type'
				);
				$included_post_types = explode( ",", Mobiloud::get_option( 'ml_article_list_include_post_types', array() ) );
				foreach ( $included_post_types as $post_type ) {
					$posts_query['post_type'] = $post_type;
					$posts                    = get_posts( $posts_query );
					if ( count( $posts ) > 0 ) {
						?>
						<optgroup label="<?php echo ucfirst( $post_type ); ?>">
							<?php foreach ( $posts as $post ) { ?>

								<option
									value="<?php echo MOBILOUD_PLUGIN_URL; ?>post/post.php?post_id=<?php echo $post->ID; ?>">
									<?php if ( strlen( $post->post_title ) > 40 ) { ?>

										<?php echo substr( $post->post_title, 0, 40 ); ?>

										..
										<?php } else { ?>

										<?php echo $post->post_title; ?>

										<?php } ?>
								</option><?php } ?>
						</optgroup>
						<?php
					}
				}


				?>
				<?php $pages = get_pages( array( 'sort_order'  => 'ASC',
					'sort_column' => 'post_title',
					'post_type'   => 'page',
					'post_status' => 'publish'
				) ); ?>
				<optgroup label="Pages">
					<?php foreach ( $pages as $page ) { ?>

						<option value="<?php echo MOBILOUD_PLUGIN_URL; ?>post/post.php?post_id=<?php echo $page->ID; ?>">
							<?php if ( strlen( $page->post_title ) > 40 ) { ?>

								<?php echo substr( $page->post_title, 0, 40 ); ?>

								..
								<?php } else { ?>

								<?php echo $page->post_title; ?>

								<?php } ?>
						</option><?php } ?>
				</optgroup>
			</select>
			<a href='#' class='ml_open_preview_btn button-secondary ml-preview-phone-btn'>Preview on phone</a>
			<a href='#' class='ml_open_preview_btn button-secondary ml-preview-tablet-btn'>Preview on tablet</a>
		</div>

	</div>
</div>
<div class="ml2-block">
	<div class="ml2-header"><h2>Custom CSS for embedded pages</h2></div>
	<div class="ml2-body">

		<div class='ml-col-row'>
			<div class="ml-editor-controls">
				<div class='as-select'>Inject CSS in the website's theme when viewed in the app.</div>
				<a href="#" class='button-primary ml-save-editor-embed-btn'>Save</a>
			</div>
			<textarea class='ml-editor-area-embed ml-settings-embed'
				name='ml_embedded_page_css'><?php echo stripslashes( htmlspecialchars( Mobiloud::get_option( 'ml_embedded_page_css', '' ) ) ); ?></textarea>
			<p><em>Use this to add CSS rules to hide elements or change the display of your website when embedded in the app. This will
				affect pages loaded from internal links within articles or pages added from the Link section in the Menu configuration page.</em></p>
		</div>
		<div class='ml-col-row'>
			<div class="ml-form-row ml-checkbox-wrap">
				<input type="checkbox" id="ml_embedded_header_hide" name="ml_embedded_header_hide" class="ml-settings-embed"
					value="true" <?php echo Mobiloud::get_option( 'ml_embedded_header_hide' ) ? 'checked' : ''; ?>/>
				<label for="ml_embedded_header_hide">Hide the site's header</label>
			</div>
			<div class="ml-form-row ml-checkbox-wrap">
				<input type="checkbox" id="ml_embedded_footer_hide" name="ml_embedded_footer_hide" class="ml-settings-embed"
					value="true" <?php echo Mobiloud::get_option( 'ml_embedded_footer_hide' ) ? 'checked' : ''; ?>/>
				<label for="ml_embedded_footer_hide">Hide the site's footer</label>
			</div>
			<div class="ml-form-row ml-left-align clearfix">
				<div class="clearfix">
					<label for="ml_embedded_android_name">Android app package name:</label>
					<input type="text" class="ml-settings-embed" id="ml_embedded_android_name" name="ml_embedded_android_name" value="<?php echo esc_attr( stripslashes(  Mobiloud::get_option( 'ml_embedded_android_name', '' ) ) ); ?>">
				</div>
				<p><em>Enter the package name of your app to help identify requests from the app and inject CSS code in the site's theme only when they come from your app.</em></p>
			</div>
		</div>
	</div>
</div>
<div class="ml2-block">
	<div class="ml2-header"><h2>Need help from a pro?</h2></div>
	<div class="ml2-body">
		<div class='ml-col-row'>
			<p>The Mobiloud developer team can help you integrate custom fields, add video/audio embeds and
				much more to your app, for more information, contact <a href='mailto:support@mobiloud.com'>support@mobiloud.com</a>.
			</p>
		</div>
	</div>
</div>
<div id="preview_popup_content">
	<div class="iphone5s_device">
		<iframe id="preview_popup_iframe">
		</iframe>
	</div>
	<div class="ipadmini_device">
		<iframe id="preview_popup_iframe">
		</iframe>
	</div>
</div>