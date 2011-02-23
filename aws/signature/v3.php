<?php

	class AWS_Signature_v3 extends AWS_Signature_Abstract {
		
		public function sign ( AWS_Request $request, $secret_key ) {
			
			// first we need a date for the hash and headers
			$date = gmdate( DateTime::RFC2822 );
			
			// add the date header
			$request->add_header( 'X-AMZ-Date', $date );
			
			// if the SignatureMethod parameter isn't already set, use the default
			// this isn't actually used in v3 as a parameter, but we'll use it for consistency
			if ( !isset( $request->parameters['SignatureMethod'] ) ) {
				$request->parameters['SignatureMethod'] = 'HmacSHA256';
			}
			
			// figure out which algorithm we're using
			$alg = substr( strtolower( $request->parameters['SignatureMethod'] ), 4 );	// trim 'hmac'
			
			// now hash the date with the algorithm
			$hash = hash_hmac( $alg, $date, $secret_key, true );
			
			// ah HA! you thought we missed reading that line of the docs... well... we did
			$signature = base64_encode( $hash );
			
			// build the header - take note that headers are NOT urlencoded
			$header = array();
			$header[] = 'AWSAccessKeyId=' . $request->parameters['AWSAccessKeyId'];
			$header[] = 'Algorithm=' . $request->parameters['SignatureMethod'];
			$header[] = 'Signature=' . $signature;
			
			$header = 'AWS3-HTTPS ' . implode( ',', $header );
			
			// and finally, add the built auth header
			$request->add_header( 'X-Amzn-Authorization', $header );
			
		}
		
	}

?>