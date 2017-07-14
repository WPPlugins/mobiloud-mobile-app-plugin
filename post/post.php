<?php

if ( ( ! isset( $_GET['post_id'] ) ) && ( ! isset( $_GET['page_ID'] ) ) ) {
	header( "HTTP/1.1 404 Not Found" );
	exit;
}

if ( extension_loaded( 'newrelic' ) ) {
	newrelic_disable_autorum();
}

if ( ! defined( 'ABSPATH' ) ) {
	include_once( dirname( __FILE__ ) . "/../../../../wp-blog-header.php" );
}


if ( empty( $post_id ) ) {
	$post_id = htmlspecialchars( esc_attr( sanitize_text_field( $_GET['post_id'] ) ) );
	$post    = get_post( $post_id );
}

if ( empty( $post ) ) {
	header( "HTTP/1.1 404 Not Found" );
	exit;
}

include dirname( __FILE__ ) . '/../views/post.php';

?>