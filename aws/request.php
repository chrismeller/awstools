<?php

	class AWS_Request {
		
		public $parameters = array();
		public $headers = array();
		
		public function add_header ( $header, $value = null ) {
			
			if ( $value !== null ) {
				$this->headers[] = $header . ': ' . $value;
			}
			else {
				// some headers don't have a value, so don't require it
				$this->headers[] = $header;
			}
			
		}
		
		public function __get ( $name ) {
			
			if ( $name == 'body' ) {
				return $this->generate_body();
			}
			
			return null;
			
		}
		
		private function generate_body ( ) {
			
			
			
		}
		
	}

?>