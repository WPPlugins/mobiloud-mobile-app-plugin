jQuery(function() {
	if (jQuery.fn.areYouSure) {
		jQuery('#get_started_design form').areYouSure({
			'fieldSelector': ":input:not(input[type=submit]):not(input[type=button]):not(#ml_preview_upload_image)"
		});

		jQuery("#get_started_menu_config form").areYouSure({
			'fieldSelector': ":input:not(input[type=submit]):not(input[type=button]):not(select):not(input[type=text])"
		});

		jQuery("#ml_settings_general form").areYouSure();

		jQuery("#ml_settings_analytics form").areYouSure();

		jQuery("#ml_settings_editor form").areYouSure({
			'fieldSelector': ":input:not(input[type=submit]):not(input[type=button]):not(select)"
		});

		jQuery("#ml_settings_membership form").areYouSure();

		jQuery("#ml_settings_license form").areYouSure();

		jQuery("#ml_push_settings form").areYouSure();

		jQuery("#ml_settings_advertising form").areYouSure({
			'fieldSelector': ":input:not(input[type=submit]):not(input[type=button]):not(#ml_ad_banner_position_select):not(#preview_popup_post_select):not(.ml-editor-area)"
		});
	}

	if (jQuery('.ml2-sidebar').length) {
		jQuery(window).on('scroll resize', function() {
			var current_y = jQuery(window).scrollTop();
			var current_width = jQuery(window).width();
			var main_y = jQuery('.ml2-main-area').offset().top;
			var $sidebar = jQuery('.ml2-sidebar');
			if ((current_y > main_y - 50) && current_width >= 571) {
				if (!$sidebar.hasClass('ml-fixed')) {
					$sidebar.css({position:'fixed', 'right': '20px', top: '50px'});
					$sidebar.addClass('ml-fixed');
				}
			} else {
				if ($sidebar.hasClass('ml-fixed')) {
					$sidebar.css({position:'static', 'right': 'auto', top: 'auto'});
					$sidebar.removeClass('ml-fixed');
				}
			}
		})
	}
});

