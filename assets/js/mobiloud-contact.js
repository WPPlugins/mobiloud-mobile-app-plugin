jQuery(function() {
	jQuery(".ml-intercom").click(function () {
		var w=window;var ic=w.Intercom;
		Intercom('show');
		var elementExists = document.getElementById("intercom-messenger");
		if(elementExists == null) {
			window.location.href = "mailto:support@mobiloud.com";
		}
	});
});

