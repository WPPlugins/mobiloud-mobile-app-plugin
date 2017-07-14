<?php

/**
* Parent Push API notifications class
*/
class Mobiloud_Push_Api {
	protected $log_enabled;
	protected $is_secondary;

	public function __construct($is_secondary_service = false) {
		$this->is_secondary = $is_secondary_service;
		$this->load_options();
	}

	protected function load_options() {
		$this->log_enabled = Mobiloud::get_option( 'ml_pb_log_enabled', false);
	}

	public function send_batch_notification($data, $tagNames = array()) {}

	protected function save_log($url, $parameters, $result) {
		$log = array(
			'timestamp' => current_time( "timestamp" ),
			'url' => $url,
			'params' => $parameters,
			'result' => $result
		);
		if ($this->is_secondary) {
			$log[ 'is_secondary' ] = true;
		}
		$string = "\r\n" . date('Y-m-d H:i:s') . "\t" . var_export($log, 1);
		file_put_contents(Mobiloud_Admin::get_pb_log_name(), $string, FILE_APPEND);
	}

	public function registered_devices_count() {}

	protected function save_to_db($data, $tagNames) {
		global $wpdb;
		if (!$this->is_secondary) {
			$table_name = $wpdb->prefix . "mobiloud_notifications";
			$values = array(
				'time'    => current_time( "timestamp" ),
				'post_id' => isset( $data[ 'payload' ][ 'post_id' ] ) ? $data[ 'payload' ][ 'post_id' ] : null,
				'url' => isset( $data[ 'payload' ][ 'url' ] ) ? $data[ 'payload' ][ 'url' ] : null,
				'msg'     => $data['msg'],
				'android' => is_array( $data[ 'platform' ] ) && in_array( 1, $data[ 'platform' ] ) ? 'Y' : 'N',
				'ios'     => is_array( $data[ 'platform' ] ) && in_array( 0, $data[ 'platform' ] ) ? 'Y' : 'N',
				'tags'    => count( $tagNames ) > 0 ? implode( ",", $tagNames ) : ''
			);

			$wpdb->insert(
				$table_name,
				$values
			);
			if (!empty($wpdb->last_error) && (false !== stripos($wpdb->last_error, 'unknown column'))) {
				Mobiloud::run_db_update_notifications();
				$wpdb->insert(
					$table_name,
					$values
				);
			}
		}

	}

}