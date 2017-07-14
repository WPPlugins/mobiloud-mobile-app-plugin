<?php

define( 'MOBILOUD_ONESIGNAL_URL', 'https://onesignal.com/api/v1/' );

/**
* Pushbots API notifications class
*/
class Mobiloud_Onesignal_Api extends Mobiloud_Push_Api {
	private $app_id;
	private $secret_key;
	private $endpoint_url;

	protected function load_options() {
		parent::load_options();
		$this->app_id = get_option( 'ml_onesignal_app_id' );
		$this->secret_key = get_option( 'ml_onesignal_secret_key' );
		$this->endpoint_url = MOBILOUD_ONESIGNAL_URL;
	}

	public function send_batch_notification($data, $tagNames = array()) {
		// convert platforms
		// https://documentation.onesignal.com/v3.0/reference#section-platform-to-deliver-to
		$fields = array(
			'app_id' => $this->app_id,
			'ios_badgeType' => 'Increase',
			'ios_badgeCount' => 1
		);
		if (in_array(0, $data[ 'platform' ])) {
			$fields[ 'isIos' ] = true;
		}
		if (in_array(1, $data[ 'platform' ])) {
			$fields[ 'isAndroid' ] = true;
		}
		// message
		$fields[ 'contents' ] = array( 'en' => stripslashes($data[ 'msg' ]));
		// tags
		if (! empty($data[ 'tags' ])) {
			$filters = array();
			// Tag "all" to always include when we are sending with tags
			if (!in_array( 'all', $data[ 'tags' ])) {
				$data[ 'tags' ][] = 'all';
			}
			foreach ($data['tags'] as $value) {
				if (!empty($filters)) {
					$filters[] = array( 'operator' => 'OR' );
				}
				$filters[] = array(
					'field' => 'tag',
					'key' => $value,
					'relation' => '=',
					'value' => $value
				);
			}
			$fields[ 'filters' ] = $filters;
		} else {
			// Segment "All" to use when we send with no tags
			$fields[ 'included_segments' ] = array( 'All' );
		}
		// payload
		if (!empty($data[ 'payload' ])) {
			$fields[ 'data' ] = array();
			if (isset($data[ 'payload' ][ 'post_id' ])) {
				$fields[ 'data' ][ 'post_id' ] = absint($data[ 'payload' ][ 'post_id' ]);
			}
			if (isset($data[ 'payload' ][ 'featured_image' ])) {
				$fields[ 'big_picture' ] = $data[ 'payload' ][ 'featured_image' ];
				$fields[ 'ios_attachments' ] = array( 'id1' => $data[ 'payload' ][ 'featured_image' ]);
			}
			if (isset($data['payload']['url'])) {
				$fields[ 'data' ][ 'url' ] = $data[ 'payload' ][ 'url' ];
				$fields[ 'url' ] = $data[ 'payload' ][ 'url' ];
			}
		}

		$json_data   = json_encode( $fields );

		$headers = array(
			'Content-Type'      => 'application/json; charset=uft-8',
			'Authorization'     => 'Basic ' . $this->secret_key,
			'Content-Length'    => strlen( $json_data )
		);
		$url     = $this->endpoint_url . 'notifications';

		$request = new WP_Http;
		$parameters = array(
			'timeout'   => 10,
			'headers'   => $headers,
			'sslverify' => false,
			'body'      => $json_data
		);
		$result  = $request->post( $url, $parameters );
		if ($this->log_enabled) {
			// hide secret key value
			$parameters[ 'headers' ][ 'Authorization' ] = 'Basic *****';
			$this->save_log($url, $parameters, $result);
		}
		$error = false;
		if (!empty($result) && !is_wp_error($result) && isset($result[ 'body' ])) {
			$result = json_decode($result[ 'body' ], true);
			if (!empty($result[ 'id' ]) && !empty($result[ 'recipients' ])) {
				$this->save_to_db($data, $tagNames);
				return true;
			} elseif (!empty($result['errors']) || !empty($result['warnings'])) {
				$messages = !empty($result['errors']) ? $result['errors'] : array();
				if (!empty($result['warnings'])) {
					$messages = array_merge($messages, $result['warnings']);
				}
				foreach ($messages as $key => $text) {
					if ( 'All included players are not subscribed' == $text) {
						$messages[$key] = "There's no users meeting the criteria used, this includes the categories in any post attached and/or your selection of platforms.";
					}
				}
				return implode('<br>', $messages);
			}
		}
		return "There was an error sending this notification";
	}

	protected function registered_devices() {
		$fields = array(
			'app_id' => $this->app_id,
			'limit' => 1,
			'offset' => 0
		);
		$json_data   = json_encode( $fields );

		$headers = array(
			'Content-Type'      => 'application/json; charset=uft-8',
			'Authorization'     => 'Basic ' . $this->secret_key,
		);
		$url     = $this->endpoint_url . 'players?app_id=' . $this->app_id . '&limit=1&offset=0';

		$request = new WP_Http;
		$parameters = array(
			'timeout'   => 10,
			'headers'   => $headers,
			'sslverify' => false,
		);
		$result  = $request->get( $url, $parameters );

		$count = null;
		if (!empty($result) && !is_wp_error($result) && isset($result[ 'body' ])) {
			$result = json_decode($result[ 'body' ], true);
			if (!empty($result[ 'total_count' ])) {
				$count = $result[ 'total_count' ];
			}
		}
		return $count;
	}

	public function registered_devices_count() {
		$total = $this->registered_devices();
		if (is_null($total)) {
			$androidCount = Mobiloud::get_option( 'ml_count_total', 0);
		} else {
			Mobiloud::set_option( 'ml_count_total', $total);
		}
		return array( 'total' => $total, 'ios' => null, 'android' => null);
	}

}