<div class='ml-preview <?php echo strlen( get_option( 'ml_preview_os' ) ) ? get_option( 'ml_preview_os' ) : 'ios'; ?>'>
	<div class='ml-preview-body'>
		<div class="ml-preview-top-bar <?php echo $iconShade; ?>"
			 style='background-color: <?php echo get_option( 'ml_preview_theme_color' ); ?>;'></div>
		<div class='ml-preview-menu-bar'
			 style='background-color: <?php echo get_option( 'ml_preview_theme_color' ); ?>;'>
			<a href='javascript:void(0);' class='ml-icon ml-icon-menu <?php echo $iconShade; ?>'></a>
			<a href='javascript:void(0);' class='ml-preview-logo-holder'>
				<?php
				if ( strlen( trim( get_option( "ml_preview_upload_image" ) ) ) > 0 ) {
					$logoPath = get_option( "ml_preview_upload_image" );
				} else {
					$logoPath = MOBILOUD_PLUGIN_URL . '/assets/img/ml_preview_nologo.png';
				}
				?>
				<img class='ml-preview-logo' src='<?php echo $logoPath; ?>'/>
			</a>
			<a href='javascript:void(0);' class='ml-icon ml-icon-search <?php echo $iconShade; ?>'></a>
		</div>
		<div class='ml-preview-article-list'>
			<div class='scroller'>
				<div id='ml-page-placeholder'>
				</div>
			</div>
		</div>
	</div>
</div>
</div>