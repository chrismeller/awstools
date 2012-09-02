<?php

	class AWS_Request {
		
		public $parameters = array();
		public $headers = array();
		
		public function add_header ( $header, $value = null ) {
			
			$this->headers[ $header ] = $value;
			
		}
		
	}

?>