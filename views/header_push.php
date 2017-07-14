<?php if (count(Mobiloud_Admin::$push_tabs) > 1) { ?>
<div class="wp-filter">
	<ul class="filter-links">
		<?php foreach ( Mobiloud_Admin::$push_tabs as $tab_key => $tab_name ): ?>
			<?php
			$active_tab = false;
			if ( isset( $_GET['tab'] ) && $_GET['tab'] == '' ) {
				unset( $_GET['tab'] );
			}

			if ( ( ! isset( $_GET['tab'] ) && $tab_key == 'notifications' ) || ( isset( $_GET['tab'] ) && $_GET['tab'] == $tab_key ) ) {
				$active_tab = true;
			}
			?>
			<li><a href="<?php echo admin_url( 'admin.php?page=mobiloud_push&tab=' . $tab_key ); ?>"
				<?php if ($active_tab) { ?> class="current"<?php } ?>><?php echo esc_html( $tab_name ); ?></a> </li>
			<?php endforeach; ?>
	</ul>
</div>
<?php } ?>
<?php if (defined( 'ml_with_sidebar' ) && ml_with_sidebar) {
	?><div id="ml_push_notifications">
	<?php
	include MOBILOUD_PLUGIN_DIR . 'views/sidebar.php';
}
?>
