<?php

	class AWS_Request {
		
		public $parameters = array();
		public $headers = array();
		
		public function add_header ( $header, $value = null ) {
			
			if ( $value !== null ) {
				$this->headers[ $header ] = $header . ': ' . $value;
			}
			else {
				// some headers don't have a value, so don't require it
				$this->headers[ $header ] = $header;
			}
			
		}
		
		public function __get ( $name ) {
			
			if ( $name == 'body' ) {
				return $this->generate_body();
			}
			
			return null;
			
		}
		
		private function generate_body ( ) {
			
			// note that http_build_query's enc_type argument would solve this, but we don't have 5.3.6 reliably
			
			$body = array();
			foreach ( $this->parameters as $k => $v ) {
				
				$body[] = rawurlencode( $k ) . '=' . rawurlencode( $v );
				
			}
			
			$body = implode('&', $body);
			
			return $body;
			
		}
		
	}

?>