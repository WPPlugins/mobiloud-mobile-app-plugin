<?php
include_once dirname( __FILE__ ) . '/notification_categories.php';

//function that sets the last notified post
function ml_set_post_id_as_notified( $postID ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mobiloud_notifications";
	$wpdb->insert(
		$table_name,
		array(
			'time'    => current_time( "timestamp" ),
			'post_id' => $postID,
		)
	);
}

function ml_is_notified( $post_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mobiloud_notifications";
	$num        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE post_id = %d", $post_id ) );

	return $num > 0;
}

function ml_pb_post_published_notification_future( $post ) {
	ml_pb_post_published_notification( 'publish', 'future', $post, true );
}

// Do auto push notification
function ml_pb_post_published_notification( $new_status, $old_status, $post ) {

	if ( ml_is_notified( $post->ID ) || ! ml_check_post_notification_required( $post->ID ) ) {
		return;
	}

	$push_types = Mobiloud::get_option( "ml_push_post_types", "post" );
	if ( strlen( $push_types ) > 0 ) {
		$push_types = explode( ",", $push_types );

		if ( $new_status == 'publish' && $old_status != 'publish' && in_array( $post->post_type, $push_types ) ) {  // only send push if it's a new publish
			$payload = array(
				'post_id' => strval( $post->ID ),
			);

			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium_large' );
			if ( is_array( $image ) ) {
				$payload['featured_image'] = $image[0];
			}
			$tags       = ml_get_post_tag_ids( $post->ID );
			$tags[]     = 'all';
			$tagNames   = ml_get_post_tags( $post->ID );
			$tagNames[] = 'all';
			$data       = array(
				'platform' => array( 0, 1 ),
				'msg'      => strip_tags( trim( $post->post_title ) ),
				'sound'    => 'default',
				'badge'    => '+1',
				'payload'  => $payload
			);
			$value = get_post_meta($post->ID, 'ml_notification_notags', true);
			if (!Mobiloud::get_option( 'ml_pb_no_tags', false ) && empty($value)) {
				$data['notags'] = true;
				$data['tags'] = $tags;
			} else {
				$tagNames = array();
				update_post_meta($post->ID, 'ml_notification_notags', 1);
			}
			require_once(dirname(__FILE__) . '/push_notifications/class.mobiloud_notifications.php' );
			$push_api = Mobiloud_Notifications::get();

			$result = $push_api->send_notifications($data, $tagNames);
			if (true === $result) {
				if (!ml_is_notified( $post->ID )) {
					ml_set_post_id_as_notified($post->ID);
				}
			}
		}
	}
}

function ml_notifications( $limit = null ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mobiloud_notifications";
	$sql        = "SELECT * FROM $table_name ORDER BY time DESC";
	if ( $limit != null ) {
		$sql .= " LIMIT " . $limit;
	}

	return $wpdb->get_results( $sql );
}

function ml_get_notification_by( $filter = array() ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mobiloud_notifications";
	$sql        = "
	SELECT * FROM " . $table_name . "
	WHERE
	msg = '" . $wpdb->escape( $filter['msg'] ) . "'
	";
	if ( $filter['post_id'] != null ) {
		$sql .= " AND post_id = " . $wpdb->escape( $filter['post_id'] );
	}
	if ( $filter['url'] != null ) {
		$sql .= " AND url = '" . $wpdb->escape( $filter['url'] ) . "'";
	}
	$sql .= " AND android = '" . $wpdb->escape( $filter['android'] ) . "'";
	$sql .= " AND ios = '" . $wpdb->escape( $filter['ios'] ) . "'";

	$results = $wpdb->get_results( $sql );

	return $results;
}

function ml_get_post_tags( $postId ) {
	$post_categories = wp_get_post_categories( $postId );
	$tags            = array();

	foreach ( $post_categories as $c ) {
		$cat    = get_category( $c );
		$tags[] = $cat->slug;
	}

	return $tags;
}

function ml_get_post_tag_ids( $postId ) {
	$post_categories = wp_get_post_categories( $postId );
	$tags            = array();
	foreach ( $post_categories as $c ) {
		$tags[] = $c;
	}

	return $tags;
}

function ml_check_post_notification_required( $postId ) {
	$notification_categories = ml_get_push_notification_categories();
	$notification_taxonomies = ml_get_push_notification_taxonomies();

	if (empty($notification_categories) && empty($notification_taxonomies)) {
		return true;
	}

	if ( is_array( $notification_categories ) && count( $notification_categories ) > 0 ) {
		$post_categories = wp_get_post_categories( $postId );
		$found           = false;
		if ( is_array( $post_categories ) && count( $post_categories ) > 0 ) {
			foreach ( $post_categories as $post_category_id ) {
				foreach ( $notification_categories as $notification_category ) {
					if ( $notification_category->cat_ID == $post_category_id ) {
						return true;
					}
				}
			}
		}

	}

	if ( is_array( $notification_taxonomies ) && count( $notification_taxonomies ) > 0 ) {
		$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );
		$tax_list = array();
		foreach ( $taxonomies as $tax ) {
			if ($tax->query_var) {
				$tax_list[] = $tax->query_var;
			}
		}

		$post_tax = wp_get_object_terms($postId, $tax_list );
		if ( !is_wp_error( $post_tax ) && is_array( $post_tax ) && count( $post_tax ) > 0 ) {
			foreach ( $post_tax as $tax ) {
				if (in_array($tax->term_id, $notification_taxonomies)) {
					return true;
				}
			}
		}
	}


	return false;
}