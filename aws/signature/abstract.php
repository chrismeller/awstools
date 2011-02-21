<?php

	abstract class AWS_Signature_Abstract {
		
		protected $secret_key = null;
		
		abstract public function sign ( AWS_Request $request, $secret_key );
		
	}

?>