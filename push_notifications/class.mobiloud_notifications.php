<?php

require_once(dirname(__FILE__) . '/class.mobiloud_push_api.php');


class Mobiloud_Notifications {
	/**
	* @var Mobiloud_Push_Api
	*/
	protected $api, $api2;
	const default_api = 'pushbots';

	/**
	* @var Mobiloud_Notifications
	*/
	static private $instance = NULL;

	public static function get() {
		if (is_null(self::$instance)){
			self::$instance = new Mobiloud_Notifications();
		}
		return self::$instance;
	}

	public function __construct() {
		$option = Mobiloud::get_option( 'ml_push_service' ); // onesignal or pushbot
		if (!in_array($option, array( '0', '1' ))) {
			$option = self::default_api;
		} else {
			$option = ('1' == $option) ? 'onesignal' : 'pushbots';
		}
		$class = 'Mobiloud_' . $option . '_Api';
		require_once(dirname(__FILE__) . '/class.mobiloud_' . $option . '_api.php' );
		$this->api = new $class(false);
		if (self::is_migrating_on()) {
			// second service
			$option = ( 'onesignal' == $option ? 'pushbots' : 'onesignal' );
			$class = 'Mobiloud_' . $option . '_Api';
			require_once(dirname(__FILE__) . '/class.mobiloud_' . $option . '_api.php' );
			$this->api2 = new $class(true);
		} else {
			$this->api2 = false;
		}
	}

	public function send_notifications($data, $tagNames = array()) {
		if (!Mobiloud::get_option( 'ml_pb_together', false)) {
			$data[ 'chunk' ] = Mobiloud::get_option( 'ml_pb_chunk', 2000);
			$data[ 'rate' ] = Mobiloud::get_option( 'ml_pb_rate', 60);
		}
		$result = $this->api->send_batch_notification( $data, $tagNames );
		if (!empty($this->api2)) {
			$result2 = $this->api2->send_batch_notification( $data, $tagNames );
			if (is_string($result2)) {
				$result = $result2;
			}
		}
		return $result;
	}

	public function registered_devices_count() {
		return $this->api->registered_devices_count();
	}

	private function is_migrating_on() {
		return Mobiloud::get_option( 'ml_push_migrate_mode', false);
	}

}