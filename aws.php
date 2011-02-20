<?php

	abstract class AWS {
		
		const VERSION = '0.1';
		
		private $aws_access_key = null;
		private $aws_secret = null;
		
		protected $api_version;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null ) {
			
			// for compatibility with other libraries, accept constants as well
			if ( $aws_access_key == null && !defined('AWS_KEY') ) {
				throw new AWS_Exception( 'No access key provided and no AWS_KEY constant available.' );
			}
			
			if ( $aws_secret == null && !defined('AWS_SECRET_KEY') ) {
				throw new AWS_Exception( 'No secret key provided and no AWS_SECRET_KEY constant available.' );
			}
			
			$this->aws_access_key = $aws_access_key;
			$this->aws_secret = $aws_secret;
			
		}
		
		protected function request ( $action, $options = array(), $endpoint = null, $xml_namespace = null, $signature_version = 2 ) {
		
			$dom = new DOMDocument( '1.0', 'utf-8' );
			$dom->formatOutput = true;
			
			$envelope = $dom->appendChild( new DOMElement( 'SOAP-ENV:Envelope', '', 'http://schemas.xmlsoap.org/soap/envelope/' ) );
			$envelope->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/' );
			$envelope->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:SOAP-ENC', 'http://http://schemas.xmlsoap.org/soap/encoding/' );
			$envelope->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
			$envelope->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema' );
			
			$body = $envelope->appendChild( new DOMElement( 'SOAP-ENV:Body', '', 'http://schemas.xmlsoap.org/soap/envelope/' ) );
			
			$request = $body->appendChild( new DOMElement( $action . 'Request', '', $xml_namespace ) );
			
			$timestamp = gmdate('c');		// GMT, as recommended by Amazon
			$signature = $this->generate_signature( $action, $timestamp );
			
			// append the authentication params and base info
			$timestamp = $request->appendChild( new DOMElement( 'Timestamp', $timestamp ) );
			$signature = $request->appendChild( new DOMElement( 'Signature', $signature ) );
			$access_key = $request->appendChild( new DOMElement( 'AWSAccessKeyId', $this->aws_access_key ) );
			$version = $request->appendChild( new DOMElement( 'Version', $this->api_version ) );
			$action = $request->appendChild( new DOMElement( 'Action', $action ) );
			
			echo $dom->saveXML();
			
			$options = array(
				'http' => array(
					'method' => 'POST',
					'user_agent' => 'AWS/' . self::VERSION,
					'content' => $dom->saveXML(),
					'ignore_errors' => true,		// ignore HTTP status code failures and return the result so we can check for the error message 
				)
			);
			
			$context = stream_context_create( $options );
			
			$response = file_get_contents( 'https://' . $endpoint, false, $context );
			
			echo $response;
			
			$response_dom = new DOMDocument( '1.0', 'utf-8' );
			$response_dom->validateOnParse = true;
			$response_dom->loadXML( $response );
			
			$errors = $response_dom->getElementsByTagName( 'Error' );
			
			if ( $errors->length > 0 ) {
				
				foreach ( $errors as $error ) {
					
					foreach ( $error->childNodes as $child ) {
						if ( $child->nodeName == 'Code' ) {
							$code = $child->nodeValue;
						}
						if ( $child->nodeName == 'Message' ) {
							$message = $child->nodeValue;
						}
					}
					
					throw new AWS_Exception( $code . ': ' . $message );
				}
				
			}
			
			echo $result;
			
		}
		
		private function generate_signature ( $action, $timestamp ) {
			
			return hash_hmac( 'sha1', $action . $timestamp, $this->aws_secret );
			
		}
		
	}
	
	class AWS_Exception extends Exception {}
	
?>