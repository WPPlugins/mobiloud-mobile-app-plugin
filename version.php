<?php
if ( ! defined( 'ABSPATH' ) ) {
	include( "../../../wp-load.php" );
}
$info = array( "version" => "3.6.8" );
if ( isset( $_GET['callback'] ) ) {
	$callback = sanitize_text_field( $_GET['callback'] );
	if ( $callback ) {
		echo $callback . "(";
	}
}
if ( strpos( $_SERVER['REQUEST_URI'], 'version' ) !== false ) {
	echo json_encode( $info );
}
if ( isset( $callback ) ) {
	echo ")";
}
?>