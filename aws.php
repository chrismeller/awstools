<?php

	// register the AWS autoloader
	spl_autoload_register( array( 'AWS', 'autoload' ) );

	class AWS {
		
		const VERSION = '0.2';
		
		protected $aws_access_key = null;
		protected $aws_secret = null;

		/**
		 * The last AWS_Response object generated.
		 * @var AWS_Response|null
		 */
		public $last_response = null;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null ) {
			
			// if there is a config file, load it
			if ( file_exists( dirname( __FILE__ ) . '/config.php' ) ) {
				include_once( dirname( __FILE__ ) . '/config.php' );
			}
			
			// for compatibility with other libraries, accept constants as well
			if ( $aws_access_key == null ) {
				
				if ( defined( 'AWS_KEY' ) ) {
					$aws_access_key = AWS_KEY;
				}
				else {
					throw new AWS_Exception( 'No access key provided and no AWS_KEY constant available.' );
				}
				
			}
			
			if ( $aws_secret == null ) {
				
				if ( defined( 'AWS_SECRET_KEY' ) ) {
					$aws_secret = AWS_SECRET_KEY;
				}
				else {
					throw new AWS_Exception( 'No secret key provided and no AWS_SECRET_KEY constant available.' );
				}
				
			}
			
			$this->aws_access_key = $aws_access_key;
			$this->aws_secret = $aws_secret;
			
		}
		
		protected function request ( $action, $options = array(), $headers = array() ) {
			
			$request = new AWS_Request();
			$request->endpoint = $this->endpoint;
			$request->action = $action;
			$request->parameters = $parameters;
			$request->body = $this->format_body( $request->parameters );
			
			// add the right content-type to the headers, if there isn't one already
			if ( !isset( $request->headers['Content-Type'] ) ) {
				$request->add_header( 'Content-Type', 'application/x-www-form-urlencoded; charset=utf-8' );
			}

			// sign the request!
			AWS_Signature::factory( $this->signature_version )->access_key( $this->aws_access_key )->sign( $request, $this->aws_secret );

			// put the headers into a format we can take
			$h = array();
			foreach ( $request->headers as $k => $v ) {
				$h[] = $k . ': ' . $v;
			}
			
			$options = array(
				'http' => array(
					'method' => 'POST',
					'user_agent' => 'AWSTools/' . self::VERSION,
					'content' => $this->format_body( $request->parameters ),
					'header' => $h,
					// ignore HTTP status code failures and return the result so we can check for the error message - requires 5.2.10+
					'ignore_errors' => true, 
				)
			);

			$context = stream_context_create( $options );
			
			$response = file_get_contents( 'https://' . $request->endpoint, false, $context );
			
			if ( $response === false ) {
				throw new AWS_Exception( 'Unable to complete request.' );
			}
			return $response;
			
		}

		protected function format_body ( $parameters = array() ) {

			// note that http_build_query's enc_type argument would solve this, but we don't have 5.3.6 reliably
			
			$body = array();
			foreach ( $parameters as $k => $v ) {
				
				$body[] = rawurlencode( $k ) . '=' . rawurlencode( $v );
				
			}
			
			$body = implode('&', $body);
			
			return $body;

		}

		public static function autoload ( $class_name ) {
			
			$class_file = strtolower( $class_name );
			
			// convert underscores to /
			$class_file = str_replace( '_', '/', $class_file );
			
			// now try to load the file with the default spl autoloader, which appends a standard list of extensions
			spl_autoload( $class_file );
			
		}
		
	}
	
?>