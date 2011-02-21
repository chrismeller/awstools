<?php

	class AWS_Signature_v1 extends AWS_Signature_Abstract {
		
		public function sign ( $parameters = array() ) {
			
			// sort all parameters by key, ignoring case
			uksort( $parameters, 'strcasecmp' );
			
			// now loop through each one and concatenate the normal-case name and value
			$string = array();
			foreach ( $parameter as $k => $v ) {
				$string[] = $k . $v;
			}
			
			// concatenate them all together with no separator
			$string = implode( '', $string );
			
			// and finally hash it
			$hash = hash_hmac( 'sha1', $string, $this->key, true );
			
			// ah HA! you thought we missed reading that line of the docs... well... we did
			return base64_encode( $hash );
			
		}
		
	}

?>