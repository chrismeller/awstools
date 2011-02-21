<?php

	class AWS_Signature_v0 extends AWS_Signature_Abstract {
		
		public function sign ( $parameters = array() ) {
			
			$string = $parameters['Action'] . $parameters['Timestamp'];
			
			$hash = hash_hmac( 'sha1', $string, $this->key, true );
			
			// ah HA! you thought we missed reading that line of the docs... well... we did
			return base64_encode( $hash );
			
		}
		
	}

?>