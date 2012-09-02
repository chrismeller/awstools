<?php

	class AWS_Request {
		
		public $parameters = array();
		public $headers = array();

		public $endpoint;
		public $action;
		
		public function add_header ( $header, $value = null ) {
			
			$this->headers[ $header ] = $value;
			
		}
		
	}

?>