<?php

	class AWS_Response {
		
		public $request_id;
		public $box_usage;
		public $next_token;
		
		public $response;
		
		public $response_dom;
		public $response_xml;
		public $response_raw;
		
		public function __get ( $name ) {
			
			if ( isset( $this->response->{$name} ) ) {
				return (string)$this->response->{$name};
			}
			
			return false;
			
		}
		
	}

?>