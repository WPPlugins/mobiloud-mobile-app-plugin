<div class="wp-filter">
	<ul class="filter-links">
		<?php
		// current tab
		$active_tab = 'design';
		if (isset($_GET[ 'tab' ]) && !empty(Mobiloud_Admin::$settings_tabs[$_GET[ 'tab' ]])) {
			$active_tab = $_GET[ 'tab' ];
		}
		foreach ( Mobiloud_Admin::$settings_tabs as $tab_key => $tab_values ): ?>
			<?php
			$tab_name = $tab_values[ 'title' ];
			?>
			<li><a href="<?php echo admin_url( 'admin.php?page=mobiloud&tab=' . $tab_key ); ?>"
				<?php if ($tab_key == $active_tab) { ?> class="current"<?php } ?>><?php echo esc_html( $tab_name ); ?></a> </li>
			<?php endforeach;
		$tab_values = Mobiloud_Admin::$settings_tabs[ $active_tab ];
		?>
	</ul>
</div>

<?php if (defined( 'ml_with_sidebar' ) && ml_with_sidebar) {
	?><div id="<?php echo $tab_values[ 'form_wrap_id' ]; ?>">
	<?php
	include MOBILOUD_PLUGIN_DIR . 'views/sidebar.php';
}
if (defined( 'ml_with_form' ) && ml_with_form) {
	?>
	<form class="ml2-main-area" method="post" action="<?php echo admin_url( 'admin.php?page=mobiloud&tab=' . $active_tab ); ?>"
		<?php if (!empty($tab_values[ 'form_id' ])) { ?> id="<?php echo $tab_values[ 'form_id' ]; ?>"<?php } ?>>
	<?php
	wp_nonce_field( 'ml-form-' . $active_tab );
} ?>
