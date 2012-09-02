<?php

	abstract class AWS_Service_JSON extends AWS_Service {

		const JSON_1 = 'application/x-amz-json-1.0';

		protected $json_version = self::JSON_1;

		protected function request ( $action, $options = array(), $headers = array() ) {

			$headers['Content-Type'] = $this->json_version;

			$response = parent::request( $action, $options, $headers );

			$response = json_decode( $response );

			if ( isset( $response->__type ) ) {
				throw new AWS_Exception( $response->__type . ': ' . $response->message );
			}

			return $response;

		}

		protected function format_body ( $parameters = array() ) {

			return json_encode( $parameters );

		}

	}

?>