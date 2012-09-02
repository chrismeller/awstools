<?php

	abstract class AWS_Service_XML extends AWS_Service {

		protected function request ( $action, $options = array(), $xpath = null ) {

			$response = parent::request( $action, $options, $xpath );

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

	}

?>