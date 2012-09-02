<?php

	abstract class AWS_Service_JSON extends AWS_Service {

		const JSON_1 = 'application/x-amz-json-1.0';

		protected $json_version = self::JSON_1;

		protected $exceptions = array(
			'#AccessDeniedException' => 'AccessDenied',
			'#ConditionalCheckFailedException' => 'ConditionalCheckFailed',
			'#IncompleteSignatureException' => 'IncompleteSignature',
			'#LimitExceededException' => 'LimitExceeded',
			'#MissingAuthenticationTokenException' => 'MissingAuthenticationToken',
			'#ProvisionedThroughputExceededException' => 'ProvisionedThroughputExceeded',
			'#ResourceInUseException' => 'ResourceInUse',
			'#ResourceNotFoundException' => 'ResourceNotFound',
			'#ThrottlingException' => 'Throttling',
			'#ValidationException' => 'Validation',
			'#SerializationException' => 'Serialization',
			'#InternalFailure' => 'InternalFailure',
			'#InternalServerError' => 'InternalServerError',
			'#ServiceUnavailableException' => 'ServiceUnavailable',
		);

		protected function request ( $action, $options = array(), $headers = array() ) {

			$headers['Content-Type'] = $this->json_version;

			$response = parent::request( $action, $options, $headers );

			$response = json_decode( $response );

			if ( isset( $response->__type ) ) {

				// do some parsing on the type to determine specific classes of exceptions
				$type = $response->__type;
				$class = 'AWS_Exception';

				foreach ( $this->exceptions as $k => $v ) {
					if ( strpos( $type, $k ) !== false ) {
						$class .= '_' . $v;
					}
				}


				if ( isset( $response->message ) ) {
					$message = $response->message;
				}
				else if ( isset( $response->Message ) ) {
					$message = $response->Message;
				}
				else {
					$message = null;
				}

				// if the class is still the same, add the error type to the beginning of the message
				if ( $class == 'AWS_Exception' ) {
					$message = $type . ': ' . $message;
				}

				throw new $class( $message );
			}

			return $response;

		}

		protected function format_body ( $parameters = array() ) {

			return json_encode( $parameters );

		}

	}

?>