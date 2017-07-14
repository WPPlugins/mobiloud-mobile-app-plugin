jQuery(document).ready(function() {
	jQuery(".chosen-select:not(#ml_push_notification_categories)").chosen({width:'350px'});
	if (jQuery("#ml_push_notification_categories").length) {
		jQuery("#ml_push_notification_categories").chosen();
	}

	if (jQuery('#ml_pb_together').length) {
		jQuery('#ml_pb_together').on('change', function() {
			if (jQuery('#ml_pb_together').is(':checked')) {
				jQuery('#ml_pb_not_together_block').hide();
			} else {
				jQuery('#ml_pb_not_together_block').show();
			}
		})
	}
	if (jQuery('#ml_pb_log_enabled').length) {
		jQuery('#ml_pb_log_enabled').on('change', function() {
			if (jQuery('#ml_pb_log_enabled').is(':checked')) {
				jQuery('#ml_push_log_name_block').show();
			} else {
				jQuery('#ml_push_log_name_block').hide();
			}
		})
	}
	if (jQuery('[name="ml_push_service"]').length) {
		jQuery('[name="ml_push_service"]').on('change', function() {
			if ('1' == jQuery('[name="ml_push_service"]:checked').val()) {
				jQuery('.ml_system_0').hide();
				jQuery('.ml_system_1').show();
			} else {
				jQuery('.ml_system_1').hide();
				jQuery('.ml_system_0').show();
			}
		})
	}
	if (jQuery('#ml_push_notification_data_id').length) {

		jQuery.post(ajaxurl, {action: 'ml_push_attachment_content', async: true}, function(response) {
			if (response.search('<option') > -1) {
				jQuery("#ml_push_notification_data_id").html(response);
			}
		});
	}
	if (jQuery('.ml_migrate_req').length) {
		jQuery('.ml_migrate_req, .ml_migrate_service').on('change', function() {
			var show = jQuery('#ml_system_1').is(':checked');

			jQuery('.ml_migrate_req').each(function(){
				if (jQuery(this).val() == '') {
					show = false;
				}
			})
			if (show) {
				jQuery('.ml_migrate').show();
			} else {
				jQuery('.ml_migrate').hide();
			}
		});
		jQuery('.ml_migrate_req:first').trigger('change');
	}

});