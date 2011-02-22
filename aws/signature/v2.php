<?php

	class AWS_Signature_v2 extends AWS_Signature_Abstract {
		
		public function sign ( AWS_Request $request, $secret_key ) {
			
			// first we make sure the required keys for signing are set
			
			// if the SignatureMethod parameter isn't already set, use the default
			if ( !isset( $request->parameters['SignatureMethod'] ) ) {
				$request->parameters['SignatureMethod'] = 'HmacSHA256';
			}
			
			// and this is version 2, so set that
			$request->parameters['SignatureVersion'] = '2';
			
			
			
			$string = array();
			
			// first up, the HTTP method verb, which is always POST for this library
			$string[] = 'POST';
			
			// next, the HTTP Host header, so just the domain name of the endpoint
			$string[] = parse_url( 'http://' . $request->endpoint, PHP_URL_HOST );
			
			// next, the path
			$uri = parse_url( rtrim( 'http://' . $request->endpoint ), PHP_URL_PATH );
			
			// if there is no path, use /
			if ( $uri == '' ) {
				$uri = '/';
			}
			
			// and URL encode it
			$uri = rawurlencode( $uri );
			
			// but restore the /'s
			$uri = str_replace( '%2F', '/', $uri );
			
			$string[] = $uri;
			
			// sort all parameters by key
			uksort( $request->parameters, 'strcmp' );
			
			// now loop through each parameter and concatenate the name and value, url-encoding both
			// note that currently all keys are url-safe, but that may not be so in the future, so we encode them as well
			$params = array();
			foreach ( $request->parameters as $k => $v ) {
				$params[] = rawurlencode( $k ) . '=' . rawurlencode( $v );
			}
			
			// and concatenate them all with &'s
			$params = implode( '&', $params );
			
			// add params to the list for the string
			$string[] = $params;
			
			// and concatenate them all together, separated by a newline
			$string = implode( "\n", $string );
			
			// figure out which algorithm we're using
			$alg = substr( strtolower( $request->parameters['SignatureMethod'] ), 4 );	// trim 'hmac'
			
			$hash = hash_hmac( $alg, $string, $secret_key, true );
			
			// ah HA! you thought we missed reading that line of the docs... well... we did
			$signature = base64_encode( $hash );
			
			// add it to the parameters, that's all this version does
			$request->parameters['Signature'] = $signature;
			
		}
		
	}

?>