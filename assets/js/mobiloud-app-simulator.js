jQuery(document).ready(function() {
	jQuery(".sim-btn-top").click(function() {
		jQuery('.sim-btn').click();

	});

	if(window.location.hash=="#start_live_preview") {

		setTimeout(continueExecution, 2000) //wait a second before continuing

		function continueExecution()
		{
			jQuery('.sim-btn').click();
		}
	}

	jQuery(".sim-btn").click(function() {
		var url = jQuery(this).attr('href');
		var new_url = url.substring(0, url.indexOf('&TB_iframe') !== -1 ? url.indexOf('&TB_iframe') : url.length);
		jQuery(this).attr('href', new_url
			+ '&TB_iframe=true&width=650&height='+(jQuery(window).height()-(jQuery(window).width()>850?60:20)));
	});


	jQuery(".ml-iframe").on('load', function() {
		jQuery(".ml-loader").hide();
		jQuery(".ml-iframe").show();
	});

	jQuery(".thickbox_full").on('click', function() {
		var url = jQuery(this).attr('href');
		var preview = window.open(url, 'mobiloud_live_preview', 'height=790,width=640,menubar=no,status=no');
		if (('undefined' == typeof(preview)) || ! preview) {
			tb_show('', url, false);
		} else {
			preview.focus();
		}
			return false;
	});
});