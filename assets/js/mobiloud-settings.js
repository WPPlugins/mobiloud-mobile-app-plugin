jQuery(document).ready(function() {
	jQuery("input[name='ml_show_email_contact_link']").on('click', function() {
		if (jQuery(this).is(':checked')) {
			jQuery('.ml-email-contact-row').show();
		} else {
			jQuery('.ml-email-contact-row').hide();
		}
	});
	jQuery("input[name='ml_comments_system']").on('click', function() {
		var sys = jQuery("input[name='ml_comments_system']:checked").val();
		if ( 'disqus' == sys) {
			jQuery(".ml-disqus-row").show();
		} else {
			jQuery(".ml-disqus-row").hide();
		}
	});
	jQuery("input[name='homepagetype']").on('change', function() {
		var type = jQuery("input[name='homepagetype']:checked").val();
		if ('ml_home_article_list_enabled' == type) {
			jQuery(".ml-list-enabled").show();
			jQuery(".ml-list-disabled").hide();
		} else {
			jQuery(".ml-list-enabled").hide();
			jQuery(".ml-list-disabled").show();
		}
	}).trigger('change');
});
