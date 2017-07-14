<?php
/**
* Show feedback dialog on plugin deactivation. Send report with user's choice via Intercom.
*
* Use:
* Simply include this file and add line: "$feedback = new Mobiloud_Feedback();"
* after main admin module initialization (after string "add_action( 'init', array( 'Mobiloud_Admin', 'init' ) );")
*/
class Mobiloud_Feedback {
	const ENDPOINT = 'https://www.mobiloud.com/plugin/deactivation.php'; // deactivation endpoint
	const REPORT_SUBJECT = 'Mobiloud deactivation report'; // email subject
	const CONFIRMATION_MESSAGE = ''; // text shown before feedback on deactivation
	const SAVE_USED_TIME = false; // send plugin used time with feedback info
	const DEFAULT_PRIORITY = 10;
	const PLUGIN_SLUG = 'mobiloud-mobile-app-plugin';

	public function __construct() {
		if (self::SAVE_USED_TIME) {
			$this->set_initial_date();
		}
		$this->hook_plugin_action_links();
	}

	/* Copyright (c) 2015, Freemius, Inc.
	 * The following code is a derivative work of the code from the Freemius plugin, which is licensed GPLv2.
	 * This code is also licensed under the terms of the GNU Public License, verison 2.
	 */

	/**
	* Add html item used for deactivation feedback
	*
	*/
	private function hook_plugin_action_links() {
		$name =  plugin_basename(MOBILOUD_PLUGIN_DIR . '/mobiloud.php'); // name of main plugin's file

		add_filter( 'plugin_action_links_' . $name, array(
			&$this,
			'modify_plugin_action_links_hook'
			), self::DEFAULT_PRIORITY, 2 );
		add_filter( 'network_admin_plugin_action_links_' . $name, array(
			&$this,
			'modify_plugin_action_links_hook'
			), self::DEFAULT_PRIORITY, 2 );
		// deactivation hook
		if ( is_admin() ) {
			add_action( 'wp_ajax_mobiloud-uninstall-reason', array( &$this, 'submit_uninstall_reason_action' ) );
			global $pagenow;

			if ( 'plugins.php' === $pagenow ) {
				add_action( 'admin_footer', array( &$this, 'deactivation_feedback_dialog_box' ) );
			}
		}
	}

	function modify_plugin_action_links_hook( $links, $file ) {
		// This HTML element is used to identify the correct plugin when attaching an event to its Deactivate link.
		if ( isset( $links['deactivate'] ) ) {
			$links['deactivate'] .= '<i class="fs-slug" data-slug="' . self::PLUGIN_SLUG . '"></i>';
		}
		return $links;
	}
	function submit_uninstall_reason_action() {
		if ( !isset( $_POST['reason_id'] ) ) {
			exit;
		}
		$reason_info = isset( $_REQUEST['reason_info'] ) ? trim( stripslashes( $_REQUEST['reason_info'] ) ) : '';

		$reason = array(
			'id'      => $_POST['reason_id'],
			'text'    => $_POST['reason_text'],
		);
		if (!empty($reason_info)) {
			$reason['message'] = substr( $reason_info, 0, 1024 );
		} else {
			$reason['message'] = '';

		}
		$this->send_intercom($reason);
		// Print '1' for successful operation.
		echo 1;
		die();
	}

	private function send_intercom($items) {
		$current_user = wp_get_current_user();
		$admin_email  = $current_user->user_email;

		$items = $this->get_install_data_for_api($items);

		$params = array(
			'email' => $items['email'],
			'url' => $items['url'],
			'name' => $items['name'],
			'reason' => $items['text'],
			'message' => $items['message'],
		);

		$result = wp_remote_post(self::ENDPOINT, array( 'body' => $params));
	}

	private function get_install_data_for_api( $override = array() ) {
		$user_email = Mobiloud::get_option('ml_user_email');
		$user_name = Mobiloud::get_option('ml_user_name');
		if (self::SAVE_USED_TIME) {
			$time_used = human_time_diff( $this->get_initial_date(), time() );
		} else {
			$time_used = 'unknown';
		}
		return array_merge( array(
			'url'                           => get_site_url(),
			'email' 						=> $user_email,
			'name' 							=> $user_name,
			'used' 							=> $time_used,
			), $override );
	}

	/**
	* Store plugin's initial install timestamp if not exists
	*/
	private function set_initial_date() {
		if ( false === get_option('install_timestamp', false) ) {
			Mobiloud::set_option('install_timestamp', time());
		}
	}

	/**
	* Get plugin's initial install timestamp or current time if option not exists
	*/
	private function get_initial_date() {
		return get_option('install_timestamp', time());
	}

	private function prepare_reason($id, $text, $input_type = '', $input_placeholder = '') {
		return array(
			'id'                => $id,
			'text'              => $text,
			'input_type'        => $input_type,
			'input_placeholder' => $input_placeholder
		);
	}

	private function get_uninstall_reasons( $user_type = 'anonymous' ) {
		$uninstall_reasons = array();

		if ( 'customers' == $user_type ) {
			$uninstall_reasons = array (
				array( 1,  "I no longer need the app",                    '',      ''),
				array( 2,  "I found a better plugin",        			  'input', "What's the plugin's name?"),
				array( 3,  "I only needed the app for a short period",    '',      ''),
				array( 4,  "The plugin is causing problems with my site", '',      ''),
				array( 5,  "The plugin stopped working",                  '',      ''),
				array( 6,  "Too expensive",                               '',      ''),
				array( 20, "Other",                         			  'input', ''),
			);
		} elseif ( 'short-term') {
			$uninstall_reasons = array (
				array( 7,  "I couldn't understand how to make it work",        '', 	       ''),
				array( 2,  "I found a better plugin",            			   'input',    "What's the plugin's name?"),
				array( 8,  "I need a specific feature that you don't support", 'textarea', 'What feature?'),
				array( 9,  "The plugin is not working", 					   'textarea', "Kindly share what didn't work so we can fix it for future users..."),
				array( 10, "It's not what I was looking for", 				   'textarea', "What you've been looking for?"),
				array( 20, "Other", 										   'input',    ''),
			);
		} elseif ( 'anonymous' == $user_type ) {
			$uninstall_reasons = array (
				array( 11, "The plugin didn't work",                  '',      '' ),
				array( 12, "I don't like sharing my contact details", '',      '' ),
				array( 2,  "I found a better plugin",        		  'input', "What's the plugin's name?"),
				array( 20, "Other", 							      'input', '' ),
			);
		}

		foreach ($uninstall_reasons as $key => $value) {
			$uninstall_reasons[$key] = $this->prepare_reason($value[0], $value[1], $value[2], $value[3]);
		}
		return $uninstall_reasons;
	}

	function deactivation_feedback_dialog_box() {
		wp_register_style('mobiloud_deactivation_feedback', MOBILOUD_PLUGIN_URL . '/css/deactivation-feedback.css', false, MOBILOUD_PLUGIN_VERSION);
		wp_enqueue_style("mobiloud_deactivation_feedback");

		$pb_key = Mobiloud::get_option('ml_pb_app_id');
		$user_name = Mobiloud::get_option('ml_user_name');
		$user_email = Mobiloud::get_option('ml_user_email');

		$user_type = 'anonymous'; // haven't submitted info on registration
		if (!empty($pb_key)) { // have pb_key filled in
			$user_type = 'customers';
		} elseif (!empty($user_name) && !empty($user_email)) { // don't have pb_key filled in, but completed initial registration step
			$user_type = 'short-term';
		}

		// get different questions for user type
		$uninstall_reasons = $this->get_uninstall_reasons( $user_type );
		// Load the HTML template for the deactivation feedback dialog box.
		$this->show_template($uninstall_reasons);
	}

	private function show_template($reasons) {
		$slug = self::PLUGIN_SLUG; // use plugin's slug as constant
		$texts = array(
			'deactivation-share-reason' => 'If you have a moment, please let us know why you are deactivating',
			'deactivation-modal-button-cancel' => 'Cancel',
			'deactivation-modal-button-submit' => 'Submit & Deactivate',
			'deactivation-modal-button-confirm' => 'Yes - Deactivate',
			'deactivation-modal-button-deactivate' => 'Deactivate',
			'deactivation-modal-button-send-and-deactivate' => 'Send feedback & Deactivate',
			'deactivation-modal-button-just-deactivate' => 'Just Deactivate',
		);
		$reasons_list_items_html = '';
		$confirmation_message = self::CONFIRMATION_MESSAGE;

		foreach ( $reasons as $reason ) {
			$list_item_classes = 'reason' . ( ! empty( $reason['input_type'] ) ? ' has-input' : '' );
			$reasons_list_items_html .= '<li class="' . $list_item_classes . '" data-input-type="' . $reason['input_type'] . '" data-input-placeholder="' . $reason['input_placeholder'] . '"><label><span><input type="radio" name="selected-reason" value="' . $reason['id'] . '"/></span><span>' . $reason['text'] . '</span></label></li>';
		}
		?>
		<script>
/* Copyright (c) 2015, Freemius, Inc.
* The following code is a derivative work of the code from the Freemius plugin, which is licensed GPLv2.
* This code is also licensed under the terms of the GNU Public License, verison 2.
*/
			(function( $ ) {
				var reasonsHtml		= <?php echo json_encode( $reasons_list_items_html ); ?>,
				modalHtml		=
				'<div class="fs-modal<?php echo empty( $confirmation_message ) ? ' no-confirmation-message' : ''; ?>">'
				+	'	<div class="fs-modal-dialog">'
				+	'		<div class="fs-modal-body">'
				+	'			<div class="fs-modal-panel" data-panel-id="confirm"><p><?php echo $confirmation_message; ?></p></div>'
				+	'			<div class="fs-modal-panel active" data-panel-id="reasons"><h3><strong><?php echo $texts[ 'deactivation-share-reason' ]; ?>:</strong></h3><ul id="reasons-list">' + reasonsHtml + '</ul></div>'
				+	'		</div>'
				+	'		<div class="fs-modal-footer">'
				+	'			<a href="#" class="button button-secondary button-just-deactivate"><?php echo $texts[ 'deactivation-modal-button-just-deactivate' ]; ?></a>'
				+	'			<a href="#" class="button button-primary button-deactivate"><?php echo $texts[ 'deactivation-modal-button-send-and-deactivate' ]; ?></a>'
				+	'		</div>'
				+	'	</div>'
				+	'</div>',
				$modal			= $( modalHtml ),
				$deactivateLink = $( '#the-list .deactivate > [data-slug=<?php echo $slug; ?>].fs-slug' ).prev();

				$modal.appendTo( $( 'body' ) );

				registerEventHandlers();

				function registerEventHandlers() {
					$deactivateLink.click(function ( evt ) {
						evt.preventDefault();

						showModal();
					});

					$modal.on( 'click', '.button', function( evt ) {
						evt.preventDefault();

						if ( $( this ).hasClass( 'disabled' ) ) {
							return;
						}

						var _parent = $( this ).parents( '.fs-modal:first' );
						var _this = $( this );

						if ( _this.hasClass( 'button-just-deactivate' )) {
							// If no selected reason, just deactivate the plugin.
							window.location.href = $deactivateLink.attr( 'href' );
							return;
						}

						if ( _this.hasClass( 'allow-deactivate' ) ) {
							var $radio           = $( 'input[type="radio"]:checked' );

							if ( (0 === $radio.length)) {
								// If no selected reason, just deactivate the plugin.
								window.location.href = $deactivateLink.attr( 'href' );
								return;
							}

							var	$selected_reason = $radio.parents( 'li:first' ),
							$input           = $selected_reason.find( 'textarea, input[type="text"]' );

							$.ajax({
								url: ajaxurl,
								method: 'POST',
								data: {
									'action'      : 'mobiloud-uninstall-reason',
									'reason_id'   : $radio.val(),
									'reason_text'   : $radio.closest('span').next().text(),
									'reason_info' : ( 0 !== $input.length ) ? $input.val().trim() : ''
								},
								beforeSend: function() {
									_parent.find( '.button' ).addClass( 'disabled' );
									_parent.find( '.button-secondary' ).text( 'Processing...' );
								},
								complete: function() {
									// Do not show the dialog box, deactivate the plugin.
									window.location.href = $deactivateLink.attr( 'href' );
								}
							});
						} else if ( _this.hasClass( 'button-deactivate' ) ) {
							// Change the Deactivate button's text and show the reasons panel.
							_parent.find( '.button-deactivate').addClass( 'allow-deactivate' );

							showPanel( 'reasons' );
						}
					});

					$modal.on( 'click', 'input[type="radio"]', function() {
						var _parent = $( this ).parents( 'li:first' );

						$modal.find( '.reason-input' ).remove();
						//$modal.find( '.button-deactivate').text( '<?php echo $texts[ 'deactivation-modal-button-submit' ]; ?>' );

						if ( _parent.hasClass( 'has-input' ) ) {
							var inputType		 = _parent.data( 'input-type' ),
							inputPlaceholder = _parent.data( 'input-placeholder' ),
							reasonInputHtml  = '<div class="reason-input">' + ( ( 'textfield' === inputType ) || ( 'input' === inputType ) ? '<input type="text" maxlength="1000"/>' : '<textarea rows="5" maxlength="1000"></textarea>' ) + '</div>';

							_parent.append( $( reasonInputHtml ) );
							_parent.find( 'input, textarea' ).attr( 'placeholder', inputPlaceholder ).focus();
						}
					});

					// If the user has clicked outside the window, cancel it.
					$modal.on( 'click', function( evt ) {
						var $target = $( evt.target );

						// If the user has clicked anywhere in the modal dialog, just return.
						if ( $target.hasClass( 'fs-modal-body' ) || $target.hasClass( 'fs-modal-footer' ) ) {
							return;
						}

						// If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
						if ( ! $target.hasClass( 'button-close' ) && ( $target.parents( '.fs-modal-body').length > 0 ||  $target.parents( '.fs-modal-footer').length > 0 ) ) {
							return;
						}

						closeModal();
					});
				}

				function showModal() {
					resetModal();

					// Display the dialog box.
					$modal.addClass( 'active' );

					$( 'body' ).addClass( 'has-fs-modal' );
				}

				function closeModal() {
					$modal.removeClass( 'active' );

					$( 'body' ).removeClass( 'has-fs-modal' );
				}

				function resetModal() {
					$modal.find( '.button' ).removeClass( 'disabled' );

					// Uncheck all radio buttons.
					$modal.find( 'input[type="radio"]' ).prop( 'checked', false );

					// Remove all input fields ( textfield, textarea ).
					$modal.find( '.reason-input' ).remove();

					var $deactivateButton = $modal.find( '.button-deactivate' );

					/*
					* If the modal dialog has no confirmation message, that is, it has only one panel, then ensure
					* that clicking the deactivate button will actually deactivate the plugin.
					*/
					if ( $modal.hasClass( 'no-confirmation-message' ) ) {
						$deactivateButton.addClass( 'allow-deactivate' );

						showPanel( 'reasons' );
					} else {
						$deactivateButton.removeClass( 'allow-deactivate' );

						showPanel( 'confirm' );
					}
				}

				function showPanel( panelType ) {
					$modal.find( '.fs-modal-panel' ).removeClass( 'active ');
					$modal.find( '[data-panel-id="' + panelType + '"]' ).addClass( 'active' );

					updateButtonLabels();
				}

				function updateButtonLabels() {
					var $deactivateButton = $modal.find( '.button-deactivate' );

					// Reset the deactivate button's text.
					if ( 'confirm' === getCurrentPanel() ) {
						$deactivateButton.text( '<?php echo $texts[ 'deactivation-modal-button-confirm' ]; ?>' );
					} else {
						$deactivateButton.text( '<?php echo $texts[ 'deactivation-modal-button-deactivate' ]; ?>' );
					}
				}

				function getCurrentPanel() {
					return $modal.find( '.fs-modal-panel.active' ).attr( 'data-panel-id' );
				}
			})( jQuery );
		</script>
		<?php
	}
}