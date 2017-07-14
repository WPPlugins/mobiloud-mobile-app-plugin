<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
*/
class MLCacheController {
	/**
	* Set cache item as wp transient record
	*
	* @since 3.3.3
	*
	* @param $type String - type of the record (for the flush cache by type)
	* @param $key String - unique key for the data
	* @param $data String - cached data
	*/
	function set_cache( $type, $key, $data ) {
		$hash = hash( 'crc32', $key );
		set_transient( $type . '_' . $hash, $data, 8 * HOUR_IN_SECONDS );
	}

	/**
	* Get cache from wp transient database
	*
	* @since 3.3.3
	*
	* @param $type String
	* @param $key String
	*
	* @return String | null
	*/
	function get_cache( $type, $key ) {
		$hash   = hash( 'crc32', $key );
		$cached = get_transient( $type . '_' . $hash );

		return ( ! empty( $cached ) ? $cached : null );
	}

	/**
	* Flush cache from wp transient database
	*
	* @since 3.5.1
	*
	* @param $type String
	*/
	function flush_cache($type) {
		global $wpdb;
		$json_transients = $wpdb->get_results(
			"SELECT option_name AS name FROM $wpdb->options
			WHERE option_name LIKE '_transient_{$type}%'"
		);

		foreach ( $json_transients as $transient ) {
			delete_transient( str_replace( "_transient_", "", $transient->name ) );
		}
	}

	/**
	* Flush post cache from wp transient database
	*
	* @since 3.5.1
	*
	* @param $post_id int
	*/
	function flush_post_cache($post_id) {
		$key  = http_build_query( array( 'post_id' => "$post_id", "type" => "ml_post" ) );
		$hash = hash( 'crc32', $key );
		delete_transient( 'ml_post_' . $hash );
	}

}