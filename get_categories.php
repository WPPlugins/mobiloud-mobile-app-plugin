<?php
if ( ! defined( 'ABSPATH' ) ) {
	include( "../../../wp-blog-header.php" );
}
include_once( "categories.php" );
include_once( "pages.php" );

function build_page_object( $dic ) {

	$childobject              = array();
	$childobject["title"]     = $dic->post_title;
	$childobject["link"]      = get_permalink( $dic->ID );
	$childobject["ml_link"]   = trailingslashit(get_site_url()) . 'ml-api/v1/post/?post_id=' . $dic->ID;
	$childobject["ml_render"] = ml_page_get_render( $dic->ID );
	$childobject["id"]        = "$dic->ID";

	$comments_count = wp_count_comments( $dic->ID );

	$childobject["comments-count"] = 0;
	if ( $comments_count ) {
		$childobject["comments-count"] = intval( $comments_count->approved );
	}

	$children = get_pages( array( 'parent' => $dic->ID ) );

	$childarray = array();

	foreach ( $children as $child ) {

		if ( $child->post_title != null && $child->ID != null && $child->post_parent == $dic->ID ) {

			array_push( $childarray, build_page_object( $child ) );

		}

	}

	$childobject["children"] = $childarray;

	return $childobject;

}

$categories       = ml_categories();
$final_categories = array();

$pages       = ml_pages();
$final_pages = array();

$final_urls = array();

$final_options = array();

//categories
foreach ( $categories as $c ) {
	$cat = array();
	if ( $c->cat_name != null && $c->slug != null && $c->cat_ID != null ) {
		$cat["name"] = html_entity_decode( $c->cat_name );
		$cat["slug"] = $c->slug;
		$cat["id"]   = "$c->cat_ID";
		array_push( $final_categories, $cat );
	}
}

$terms = get_option( 'ml_menu_terms', array() );
foreach ( $terms as $term ) {
	$term_data   = explode( "=", $term );
	$taxonomy    = $term_data[0];
	$term_id     = $term_data[1];
	$term_object = get_term_by( 'id', $term_id, $taxonomy );
	if ( $term_object ) {
		$final_categories[] = array(
			'name' => html_entity_decode( $term_object->name ),
			'slug' => $term_object->slug,
			'id'   => $term_object->term_id . ""
		);
	}
}

$tags = get_option( 'ml_menu_tags', array() );
foreach ( $tags as $tag ) {
	$term_object = get_term_by( 'id', $tag, 'post_tag' );
	if ( $term_object ) {
		$final_categories[] = array(
			'name' => html_entity_decode( $term_object->name ),
			'slug' => $term_object->slug,
			'id'   => $term_object->term_id . ""
		);
	}
}
//pages
foreach ( $pages as $p ) {
	$page = array();
	if ( $p->post_title != null && $p->ID != null ) {
		$page["title"]     = $p->post_title;
		$page["link"]      = get_permalink( $p->ID );
		$page["ml_link"]   = trailingslashit(get_site_url()) . 'ml-api/v1/post/?post_id=' . $p->ID;
		$page["ml_render"] = ml_page_get_render( $p->ID );
		$page["id"]        = "$p->ID";

		$comments_count = wp_count_comments( $p->ID );

		$page["comments-count"] = 0;
		if ( $comments_count ) {
			$page["comments-count"] = intval( $comments_count->approved );
		}

		if ( get_option( "ml_hierarchical_pages_enabled", true ) == true ) {


			$children   = get_pages( array( 'parent' => $p->ID ) );
			$childarray = array();

			foreach ( $children as $child ) {

				if ( $child->post_title != null && $child->ID != null && $child->post_parent == $p->ID ) {

					array_push( $childarray, build_page_object( $child ) );

				}

			}

			$page["children"] = $childarray;

		}

		array_push( $final_pages, $page );
	}
}

$urls = get_option( "ml_menu_urls", array() );
foreach ( $urls as $url ) {
	$urlObject          = array();
	$urlObject["url"]   = $url["url"];
	$urlObject["title"] = $url["urlTitle"];
	array_push( $final_urls, $urlObject );
}

$final_options                  = array();
$final_options["showFavorites"] = get_option( 'ml_menu_show_favorites', true );

echo json_encode( array(
	"categories" => $final_categories,
	"pages"      => $final_pages,
	"urls"       => $final_urls,
	"options"    => $final_options
) );


?>