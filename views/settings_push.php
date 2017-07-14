<div class="ml2-block">
	<div class="ml2-header"><h2><?php echo Mobiloud_Admin::$settings_tabs[$active_tab][ 'title' ]; ?></h2></div>
	<div class="ml2-body">
		<?php
		// 0 - PushBots, 1 - OneSignal
		$service = Mobiloud::get_option( 'ml_push_service', false);
		if (false === $service) { // PushBots default for existing keys
			$service = (Mobiloud::get_option( 'ml_pb_app_id' ) || Mobiloud::get_option( 'ml_pb_secret_key' )) ? 0 : 1;
		}
		?>
		<h4>Automatic push notifications</h4>
		<div class='ml-col-row'>
			<div class='ml-col-half'>
				<p>Automatically send push notifications when a new post is published</p>
			</div>
			<div class='ml-col-half'>
				<div class="ml-form-row ml-checkbox-wrap">
					<input type="checkbox" id="ml_push_notification_enabled" name="ml_push_notification_enabled"
						value="true" <?php echo Mobiloud::get_option( 'ml_push_notification_enabled' ) ? 'checked' : ''; ?>/>
					<label for="ml_push_notification_enabled">Send notifications automatically</label>
				</div>
				<p>Select which categories will generate a push notification (empty for all)</p>
				<select id="ml_push_notification_categories" name='ml_push_notification_categories[]'
					data-placeholder="Select Categories..." style="width:100%;max-width:600px;" multiple class="chosen-select">
					<option></option>
					<?php $categories = get_categories(array( 'hide_empty' => 0)); ?>
					<?php $pushCategories = ml_get_push_notification_categories(); ?>
					<?php
					foreach ( $categories as $c ) {
						$selected = '';
						if ( is_array( $pushCategories ) && count( $pushCategories ) > 0 ) {
							foreach ( $pushCategories as $pushCategory ) {
								if ( $pushCategory->cat_ID == $c->cat_ID ) {
									$selected = 'selected';
								}
							}
						}
						echo "<option value='$c->cat_ID' $selected>Category: $c->cat_name</option>";
					}
					?>
					<?php
					$tax_list = ml_get_push_notification_taxonomies(); // current tax list
					$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );

					foreach ( $taxonomies as $tax ) {
						$terms = get_terms( $tax->query_var, array( 'hide_empty' => false ) );
						if ( count( $terms ) ) {
							foreach ( $terms as $term ) {
								$parent_name = '';
								if ( $term->parent ) {
									$parent_term = get_term_by( 'id', $term->parent, $tax->query_var );
									if ( $parent_term ) {
										$parent_name = $parent_term->name . ' - ';
									}
								}
								$selected = in_array($term->term_id, $tax_list) ? ' selected="selected"' : '';
								echo "<option value='tax:{$term->term_id}'$selected>{$tax->label}: {$parent_name}{$term->name}</option>";
							}
						}
					}
					?>
				</select>
			</div>
		</div>
		<h4>Push Post Types</h4>
		<div class='ml-col-row'>
			<div class='ml-col-half'>
				<p>Select which post types should be pushed.</p>
			</div>
			<div class='ml-col-half'>
				<?php
				$posttypes         = get_post_types( '', 'names' );
				$includedPostTypes = explode( ",", Mobiloud::get_option( "ml_push_post_types", "post" ) );
				foreach ( $posttypes as $v ) {
					if ( $v != "attachment" && $v != "revision" && $v != "nav_menu_item" ) {
						$checked = '';
						if ( in_array( $v, $includedPostTypes ) ) {
							$checked = "checked";
						}
						?>
						<div class="ml-form-row ml-checkbox-wrap no-margin">
							<input type="checkbox" id='postypes_<?php echo esc_attr( $v ); ?>' name="postypes[]"
								value="<?php echo esc_attr( $v ); ?>" <?php echo $checked; ?>/>
							<label for="postypes_<?php echo esc_attr( $v ); ?>"><?php echo esc_html( $v ); ?></label>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<!-- <h4>Security settings (advanced)</h4>
		<div class='ml-col-row'>
		<div class='ml-col-half'>
		<p>Choose whether to use SSL to communicate with our push service.</p>
		</div>
		<div class="ml-form-row ml-checkbox-wrap no-margin">
		<input type="checkbox" id="ml_pb_use_ssl" name="ml_pb_use_ssl"
		value="true" <?php echo Mobiloud::get_option( 'ml_pb_use_ssl' ) ? 'checked' : ''; ?>/>
		<label for="ml_pb_use_ssl">Enable SSL for push notifications</label>
		</div>
		</div> -->
		<h4 class="ml_system_0"<?php if (1 == $service) {echo ' style="display:none;"';}?>>Push notification delivery settings</h4>
		<div class='ml-col-row ml_system_0'<?php if (1 == $service) {echo ' style="display:none;"';}?>>
			<div class='ml-col-half'>
				<p>Push notifications can be sent in chunks in order to minimize the load on your server. You can change the size of each chunk of devices and the delay between each send. The default is 2000 devices every 60 seconds.</p>
			</div>
			<div class="ml-form-row ml-col-half ml-checkbox-wrap no-margin">
				<div class="ml-form-row ml-checkbox-wrap">
					<input type="checkbox" id="ml_pb_together" name="ml_pb_together"
						value="true" <?php echo Mobiloud::get_option( 'ml_pb_together', false ) ? 'checked' : ''; ?>/>
					<label for="ml_pb_together">Send notifications at the same time for all devices</label>
				</div>

				<div id="ml_pb_not_together_block"<?php echo (Mobiloud::get_option( 'ml_pb_together', false )) ? ' style="display:none;"' : '' ?>>
					<label for="ml_pb_chunk">Chunk size: </label>
					<input type="number" id="ml_pb_chunk" name="ml_pb_chunk" value="<?php echo esc_attr(Mobiloud::get_option( 'ml_pb_chunk', 2000 )); ?>" min="100" step="100"/>
					<p>This is the number of devices reached at once by the push server.</p>

					<label for="ml_pb_rate">Rate: </label>
					<input type="number" id="ml_pb_rate" name="ml_pb_rate" value="<?php echo esc_attr(Mobiloud::get_option( 'ml_pb_rate', 60 )); ?>" min="1"/>
					<p>Rate is expressed in seconds.</p>
				</div>
			</div>
		</div>

		<h4>Notification tags</h4>
		<div class='ml-col-row'>
			<div class='ml-col-half'>
				<p>If checked, all notifications will be sent to all devices, irrespective of the user's choices for different
					push categories. This speeds up sending, which can be desirable for breaking news.</p>
			</div>
			<div class="ml-form-row ml-col-half ml-checkbox-wrap no-margin">
				<div class="ml-form-row ml-checkbox-wrap">
					<input type="checkbox" id="ml_pb_no_tags" name="ml_pb_no_tags"
						value="true" <?php echo Mobiloud::get_option( 'ml_pb_no_tags', false ) ? 'checked' : ''; ?>/>
					<label for="ml_pb_no_tags">Send notifications without tags</label>
				</div>
			</div>
		</div>

		<h4>Enable logging for debugging</h4>
		<div class='ml-col-row'>
			<div class='ml-col-half'>
				<p>When you enable this, we'll store a log of the requests and responses received from the push server,
					in the order for us to debug any issues with push notifications. Logs will be saved to a file on your server.</p>
			</div>
			<div class="ml-form-row ml-col-half ml-checkbox-wrap no-margin">
				<div class="ml-form-row ml-checkbox-wrap">
					<div>
						<input type="checkbox" id="ml_pb_log_enabled" name="ml_pb_log_enabled"
							value="true" <?php echo Mobiloud::get_option( 'ml_pb_log_enabled', false ) ? 'checked' : ''; ?>/>
						<label for="ml_pb_log_enabled">Enable push logging</label>
					</div>
					<div id="ml_push_log_name_block"<?php echo Mobiloud::get_option( 'ml_pb_log_enabled', false ) ? '' : ' style="display:none;"'; ?>>
						<input type="text" value="<?php echo esc_attr(Mobiloud_Admin::get_pb_log_name( true )); ?>" readonly="readonly" class="ml-input-full">
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
<div class="ml2-block">
	<div class="ml2-header"><h2>Push Keys</h2></div>
	<div class="ml2-body">

		<div class='ml-col-row'>
			<p>Once your app has been published, enter here the Push keys we have sent you.</p>


			<div class='ml-col-row'>
				<div class='ml-col-half'>
					<h4>Push Service</h4>
				</div>
				<div class="ml-form-row ml-col-half ml-checkbox-wrap no-margin">
					<div class="ml-form-row ml-checkbox-wrap">
						<div class="ml-radio-wrap">
							<input type="radio" name="ml_push_service" value="0" <?php checked($service, 0, true);
								?> id="ml_system_0" class="ml_migrate_service"><label for="ml_system_0">PushBots</label>
						</div>
						<div class="ml-radio-wrap">
							<input type="radio" name="ml_push_service" value="1" <?php checked($service, 1, true);
								?> id="ml_system_1" class="ml_migrate_service"><label for="ml_system_1">OneSignal</label>
						</div>


						<div class="ml-radio-wrap ml_migrate ml-checkbox-wrap" style="display:none;">
							<input type="checkbox" name="ml_push_migrate_mode" value="1" <?php checked(Mobiloud::get_option( 'ml_push_migrate_mode', 0), 1, true);
								?> id="ml_push_migrate_mode"><label for="ml_push_migrate_mode">Send to both PushBots and Onesignal users</label>
						</div>


					</div>
				</div>
			</div>

			<h4>Push Service</h4>
			<div class="ml-col-row ml_system_0"<?php if (1 == $service) {echo ' style="display:none;"';}?>>
				<div class='ml-col-half'>
					Push App ID
				</div>
				<div class="ml-form-row ml-col-half no-margin">
					<input size="36" type="text" id="ml_pb_app_id" name="ml_pb_app_id"
						placeholder="Enter Push ID" class="ml_migrate_req ml-input-full"
						value='<?php echo Mobiloud::get_option( 'ml_pb_app_id' ); ?>'>
				</div>
			</div>
			<div class="ml-col-row ml_system_0"<?php if (1 == $service) {echo ' style="display:none;"';}?>>
				<div class='ml-col-half'>
					Secret Key
				</div>
				<div class="ml-form-row ml-col-half no-margin">
					<input size="36" type="text" id="ml_pb_secret_key" name="ml_pb_secret_key"
						placeholder="Enter Secret Key"  class="ml_migrate_req ml-input-full"
						value='<?php echo Mobiloud::get_option( 'ml_pb_secret_key' ); ?>'>
				</div>
			</div>

			<div class="ml-col-row ml_system_1"<?php if (0 == $service) {echo ' style="display:none;"';}?>>
				<div class='ml-col-half'>
					Push App ID
				</div>
				<div class="ml-form-row ml-col-half no-margin">
					<input size="36" type="text" id="ml_onesignal_app_id" name="ml_onesignal_app_id"
						placeholder="OneSignal App ID" class="ml_migrate_req ml-input-full"
						value='<?php echo Mobiloud::get_option( 'ml_onesignal_app_id' ); ?>'>
				</div>
			</div>
			<div class="ml-col-row ml_system_1"<?php if (0 == $service) {echo ' style="display:none;"';}?>>
				<div class='ml-col-half'>
					Secret Key
				</div>
				<div class="ml-form-row ml-col-half no-margin">
					<input size="36" type="text" id="ml_onesignal_secret_key" name="ml_onesignal_secret_key"
						placeholder="REST API Key" class="ml_migrate_req ml-input-full"
						value='<?php echo Mobiloud::get_option( 'ml_onesignal_secret_key' ); ?>'>
				</div>
			</div>

			<p>Can't find your keys? <a class="contact" href="mailto:support@mobiloud.com">Request your keys</a> from our support team.</p>
		</div>

	</div>
</div>