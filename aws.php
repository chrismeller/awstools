<?php

	class AWS {
		
		private $aws_access_key = null;
		private $aws_secret = null;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null ) {
			
			// for compatibility with other libraries, accept constants as well
			if ( $aws_access_key == null && !defined('AWS_KEY') ) {
				throw new AWS_Exception( 'No access key provided and no AWS_KEY constant available.' );
			}
			
			if ( $aws_secret == null && !defined('AWS_SECRET_KEY') ) {
				throw new AWS_Exception( 'No secret key provided and no AWS_SECRET_KEY constant available.' );
			}
			
			$this->aws_access_key = $access_key;
			$this->aws_secret = $secret;
			
		}
		
	}
	
	class AWS_Exception extends Exception {}
	
?>