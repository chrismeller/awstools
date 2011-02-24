<?php

	// register the AWS autoloader
	spl_autoload_register( array( 'AWS', 'autoload' ) );

	class AWS {
		
		const VERSION = '0.2';
		
		private $aws_access_key = null;
		private $aws_secret = null;

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
			if ( $aws_access_key == null && !defined('AWS_KEY') ) {
				throw new AWS_Exception( 'No access key provided and no AWS_KEY constant available.' );
			}
			else {
				$aws_access_key = AWS_KEY;
			}
			
			if ( $aws_secret == null && !defined('AWS_SECRET_KEY') ) {
				throw new AWS_Exception( 'No secret key provided and no AWS_SECRET_KEY constant available.' );
			}
			else {
				$aws_secret = AWS_SECRET_KEY;
			}
			
			$this->aws_access_key = $aws_access_key;
			$this->aws_secret = $aws_secret;
			
		}
		
		protected function request ( $action, $options = array(), $xpath = null ) {
			
			// start off our parameters
			$parameters = array();
			
			$parameters['Action'] = $action;
			$parameters['Timestamp'] = gmdate('c');		// in GMT, as recommended by Amazon
			$parameters['Version'] = $this->api_version;
			$parameters['AWSAccessKeyId'] = $this->aws_access_key;
			
			// merge in all our options, overwriting any parameters
			$parameters = array_merge( $parameters, $options );
			
			$request = new AWS_Request();
			$request->endpoint = $this->endpoint;
			$request->action = $action;
			$request->parameters = $parameters;
			
			// sign the request!
			AWS_Signature::factory( $this->signature_version )->sign( $request, $this->aws_secret );
			
			// add the right content-type to the headers
			$request->add_header( 'Content-Type', 'application/x-www-form-urlencoded; charset=utf-8' );
			
			$options = array(
				'http' => array(
					'method' => 'POST',
					'user_agent' => 'AWS/' . self::VERSION,
					'content' => $request->body,
					'header' => $request->headers,
					// ignore HTTP status code failures and return the result so we can check for the error message - requires 5.2.10+
					'ignore_errors' => true, 
				)
			);
			
			$context = stream_context_create( $options );
			
			$response = file_get_contents( 'https://' . $request->endpoint, false, $context );
			
			// start parsing it using DOM, which has the best universal support
			$response_dom = new DOMDocument( '1.0', 'utf-8' );
			$response_dom->formatOutput = true;
			$response_dom->validateOnParse = true;
			$response_dom->loadXML( $response );
			
			// once DOM has parsed the XML and we think it's safe, switch to SimpleXML, which is drastically easier to get results out of
			$response_xml = simplexml_import_dom( $response_dom );
			
			// check for Error elements
			if ( isset( $response_xml->Errors ) ) {
				
				// throw the first one as an exception
				$code = (string)$response_xml->Errors->Error[0]->Code;
				$message = (string)$response_xml->Errors->Error[0]->Message;
				
				throw new AWS_Exception( $code . ': ' . $message );
				
			}
			
			// of course some of the APIs wrap them differently just to screw us
			if ( isset( $response_xml->Error ) ) {
				$code = (string)$response_xml->Error[0]->Code;
				$message = (string)$response_xml->Error[0]->Message;
				
				throw new AWS_Exception( $code . ': ' . $message );
			}
			
			// since it's a valid result, pick out the meta data
			$request_id = (string)$response_xml->ResponseMetadata->RequestId;
			$box_usage = (string)$response_xml->ResponseMetadata->BoxUsage;
			
			// if a next token is set in the result pull it out and remove it
			if ( isset( $response_xml->{$action . 'Result'}->NextToken ) ) {
				$next_token = (string)$response_xml->{$action . 'Result'}->NextToken;
				unset( $response_xml->{$action . 'Result'}->NextToken );
			}
			else {
				$next_token = null;
			}
			
			// if there is an xpath query provided, run that
			if ( $xpath != null ) {
				$response_xml->registerXPathNamespace( 'aws', $this->xml_namespace );
				$result = $response_xml->xpath( $xpath );
			}
			else {
				// otherwise, the whole result
				$result = $response_xml->{$action . 'Result'};
			}
			
			// create the response object we hand back to the client
			$r = new AWS_Response();
			$r->request_id = $request_id;
			$r->box_usage = $box_usage;
			$r->next_token = $next_token;
			
			// convert the rest of the result to an array
			$r->response = $result;
			
			// save the DOM and SimpleXML versions too
			//$r->response_dom = $response_dom;
			//$r->response_xml = $response_xml;
			$r->response_raw = $response;
			
			$this->last_response = $r;
			
			
			return $r;
			
			
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