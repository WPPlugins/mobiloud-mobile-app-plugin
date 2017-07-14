<?php

define( 'MOBILOUD_PB_NOSSL_URL', 'http://api.pushbots.com' );
define( 'MOBILOUD_PB_SSL_URL', 'https://api.pushbots.com' );

/**
* Pushbots API notifications class
*/
class Mobiloud_Pushbots_Api extends Mobiloud_Push_Api {
	private $app_id;
	private $secret_key;
	private $endpoint_url;

	protected function load_options() {
		parent::load_options();

		$this->app_id = get_option( 'ml_pb_app_id' );
		$this->secret_key = get_option( 'ml_pb_secret_key' );

		$ml_pb_use_ssl = get_option( 'ml_pb_use_ssl', false );
		if ( $ml_pb_use_ssl ) {
			$this->endpoint_url = MOBILOUD_PB_SSL_URL;
		} else {
			$this->endpoint_url = MOBILOUD_PB_NOSSL_URL;
		}
	}

	public function send_batch_notification($data, $tagNames = array()) {
		if ($this->is_secondary) {
			// only Android devices
			if (!in_array(1, $data[ 'platform' ])) {
				return true;
			}
			$data[ 'platform' ] = array(1);
		}
		$data[ 'msg' ] = stripslashes( $data[ 'msg' ] );
		if (isset($data[ 'payload' ]) && isset($data[ 'payload' ][ 'post_id' ])) {
			$data[ 'data' ][ 'post_id' ] = absint($data[ 'payload' ][ 'post_id' ]);
		}
		$json_data   = json_encode( $data );

		$headers = array(
			'X-PUSHBOTS-APPID'  => $this->app_id,
			'X-PUSHBOTS-SECRET' => $this->secret_key,
			'Content-Type'      => 'application/json',
			'Content-Length'    => strlen( $json_data )
		);
		$url     = $this->endpoint_url . '/push/all';
		$request = new WP_Http;
		$parameters = array(
			'timeout'   => 10,
			'headers'   => $headers,
			'sslverify' => false,
			'body'      => $json_data
		);
		$result  = $request->post( $url, $parameters );
		if ($this->log_enabled) {
			// hide X-PUSHBOTS-SECRET value
			$parameters[ 'headers' ][ 'X-PUSHBOTS-SECRET' ] = '*****';

			$this->save_log($url, $parameters, $result);
		}

		$this->save_to_db($data, $tagNames);
		return true;
	}

	public function registered_devices_count() {
		$request  = new WP_Http;
		$headers  = array(
			'X-PUSHBOTS-APPID'  => get_option( 'ml_pb_app_id' ),
			'X-PUSHBOTS-SECRET' => get_option( 'ml_pb_secret_key' ),
			'platform'          => 0
		);
		$url    = $this->endpoint_url . '/deviceToken/count';
		$result = $request->get( $url, array(
			'timeout'   => 10,
			'headers'   => $headers,
			'sslverify' => false
		) );
		$iosCount = null;

		if ( $result instanceof WP_Error ) {
			$iosCount = Mobiloud::get_option( 'ml_count_ios', 0);
		} elseif ( isset( $result[ 'body' ] ) && isset( $result[ 'response' ] ) && isset( $result[ 'response' ][ 'code' ] )  && ( 200 == $result[ 'response' ][ 'code' ] ) ) {
			$responseJson = json_decode( $result[ 'body' ] );
			$iosCount     = ( isset( $responseJson->count ) ? $responseJson->count : 0 );
			if (!empty( $responseJson->count )) {
				Mobiloud::set_option( 'ml_count_ios', $iosCount);
			} else {
				$iosCount = Mobiloud::get_option( 'ml_count_ios', 0);
			}
		} else {
			$iosCount = Mobiloud::get_option( 'ml_count_ios', 0);
		}

		$request      = new WP_Http;
		$headers      = array(
			'X-PUSHBOTS-APPID'  => get_option( 'ml_pb_app_id' ),
			'X-PUSHBOTS-SECRET' => get_option( 'ml_pb_secret_key' ),
			'platform'          => 1
		);
		$url    = $this->endpoint_url . '/deviceToken/count';
		$result = $request->get( $url, array(
			'timeout'   => 10,
			'headers'   => $headers,
			'sslverify' => false
		) );
		$androidCount = null;
		if ( $result instanceof WP_Error ) {
			$androidCount = Mobiloud::get_option( 'ml_count_android', 0);
		} elseif ( isset( $result[ 'body' ] ) && isset( $result[ 'response' ] ) && isset( $result[ 'response' ][ 'code' ] )  && ( 200 == $result[ 'response' ][ 'code' ] ) ) {
			$responseJson = json_decode( $result[ 'body' ] );
			$androidCount = ( isset( $responseJson->count ) ? $responseJson->count : 0 );
			if (!empty( $responseJson->count )) {
				Mobiloud::set_option( 'ml_count_android', $androidCount);
			} else {
				$androidCount = Mobiloud::get_option('ml_count_android', 0);
			}
		} else {
			$androidCount = Mobiloud::get_option('ml_count_android', 0);
		}

		return array( 'ios' => $iosCount, 'android' => $androidCount );

	}

}