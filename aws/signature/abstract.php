<?php

	abstract class AWS_Signature_Abstract {
		
		protected $access_key = null;
		protected $secret_key = null;
		
		abstract public function sign ( AWS_Request $request, $secret_key );

		public function access_key( $access_key ) {

			$this->access_key = $access_key;

			return $this;

		}
		
	}

?>