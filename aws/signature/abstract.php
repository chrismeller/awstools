<?php

	interface AWS_Signature_Abstract {
		
		protected $key = null;
		protected $endpoint = null;
		
		abstract public function sign( $parameters = array() );
		
		public function key ( $key ) {
			$this->key = $key;
			
			// for chaining
			return $this;
		}
		
		public function endpoint ( $endpoint ) {
			$this->endpoint = $endpoint;
			
			// for chaining
			return $this;
		}
		
	}

?>