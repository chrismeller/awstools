<?php

	class AWS_Signature_v2 extends AWS_Signature_Abstract {
		
		public function sign ( $parameters = array() ) {
			
			$string = array();
			
			// first up, the HTTP method verb, which is always POST for this library
			$string[] = 'POST';
			
			// next, the HTTP Host header, so just the domain name of the endpoint
			$string[] = parse_url( $this->endpoint, PHP_URL_HOST );
			
			// next, the path - we trim and append a trailing slash always so we easily get a / back if there's no path
			$uri = parse_url( rtrim( $this->endpoint ) . '/', PHP_URL_PATH );
			
			// and URL encode it
			$uri = rawurlencode( $uri );
			
			// but restore the /'s
			$uri = str_replace( '%2F', '/', $uri );
			
			$string[] = $uri;
			
			// sort all parameters by key, not we're NOT ignoring case for this one
			uksort( $parameters, 'strcmp' );
			
			// now loop through each parameter and concatenate the name and value, url-encoding both
			// note that currently all keys are UTF8-safe, but that may not be so in the future, so we encode them as well
			$params = array();
			foreach ( $parameter as $k => $v ) {
				$params[] = rawurlencode( $k ) . '=' . rawurlencode( $v );
			}
			
			// and concatenate them all with &'s
			$params = implode( '&', $params );
			
			// add params to the list for the string
			$string[] = $params;
			
			// and concatenate them all together, separated by a newline
			$string = implode( "\n", $string );
			
			// figure out which algorithm we're using
			$alg = substr( strtolower( $parameters['SignatureMethod'] ), 4 );	// trim 'hmac'
			
			$hash = hash_hmac( $alg, $string, $this->key, true );
			
			// ah HA! you thought we missed reading that line of the docs... well... we did
			return base64_encode( $hash );
			
		}
		
	}

?>