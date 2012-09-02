<?php

	class AWS_Signature_v4 extends AWS_Signature_Abstract {

		public function sign ( AWS_Request $request, $secret_key ) {

			// the docs claim the date is in ISO8601 format. even though their examples aren't, let's go with it
			$date = new DateTime( 'UTC' );

			// add the date header
			$request->add_header( 'X-AMZ-Date', $date->format( 'Ymd\THis\Z' ) );

			// if the Algorithm isn't already set, use the default
			if ( !isset( $request->algorithm ) ) {
				$request->algorithm = 'AWS4-HMAC-SHA256';
			}

			// Task 1: Canonical Request
			$canonical_request = array();

			// 1) HTTP method - they're all POST
			$canonical_request[] = 'POST';

			// 2) CanonicalURI
			$uri = parse_url( rtrim( 'http://' . $request->endpoint ), PHP_URL_PATH );

			// if there is no path, use /
			if ( $uri == '' ) {
				$uri = '/';
			}

			// and URL encode it
			$uri = rawurlencode( $uri );

			// but restore the /'s
			$uri = str_replace( '%2F', '/', $uri );

			$canonical_request[] = $uri;		// 2) URI

			// 3) CanonicalQueryString
			//  all our requests are POST and shouldn't have a query string
			$canonical_request[] = '';

			// 4) CanonicalHeaders
			$can_headers = array(
				'host' => parse_url( 'http://' . $request->endpoint, PHP_URL_HOST ),		// you suck, amazon
			);
			foreach ( $request->headers as $k => $v ) {
				$can_headers[ strtolower( $k ) ] = trim( $v );
			}

			// sort them
			uksort( $can_headers, 'strcmp' );

			// add them to the string
			foreach ( $can_headers as $k => $v ) {
				$canonical_request[] = $k . ':' . $v;
			}

			// add a blank entry so we end up with an extra line break
			$canonical_request[] = '';

			// 5) SignedHeaders - seriously, what the fuck, amazon?
			$canonical_request[] = implode( ';', array_keys( $can_headers ) );

			// 6) Payload
			
			// figure out which algorithm we're using
			$alg = substr( strtolower( $request->algorithm ), strlen( 'AWS4-HMAC-' ) );	// trim 'aws4-hmac-'
			
			$canonical_request[] = hash( $alg, $request->body );

			$canonical_request = implode( "\n", $canonical_request );

			// Task 2: String to Sign
			$string = array();

			// 1) Algorithm
			$string[] = $request->algorithm;

			// 2) RequestDate
			$string[] = $date->format( 'Ymd\THis\Z' );

			// 3) CredentialScope
			$scope = array(
				$date->format( 'Ymd' ),
			);

			// calculate the service name and region from the endpoint hostname: dynamodb.us-east-1.amazonaws.com
			list( $service, $region, $junk ) = explode( '.', $request->endpoint );

			$scope[] = $region;
			$scope[] = $service;
			$scope[] = 'aws4_request';		// this is one of the stupidest things i've ever heard of

			$string[] = implode( '/', $scope );

			// 4) CanonicalRequest
			$string[] = hash( $alg, $canonical_request );

			$string = implode( "\n", $string );

			// Task 3: Signature

			// 1) HMACs
			$kSecret = 'AWS4' . $secret_key;
			$kDate = hash_hmac( $alg, $date->format( 'Ymd' ), $kSecret, true );		// remember, binary!
			$kRegion = hash_hmac( $alg, $region, $kDate, true );
			$kService = hash_hmac( $alg, $service, $kRegion, true );
			$kSigning = hash_hmac( $alg, 'aws4_request', $kService, true );		// seriously, you're not securing anything amazon, just being a pain in the ass

			$signature = hash_hmac( $alg, $string, $kSigning );		// the signature is the only part done in hex!

			// finally, for the bloody Authorization header
			$authorization = array(
				'Credential=' . $this->access_key . '/' . implode( '/', $scope ),
				'SignedHeaders=' . implode( ';', array_keys( $can_headers ) ),
				'Signature=' . $signature,
			);

			$authorization = $request->algorithm . ' ' . implode( ',', $authorization );

			$request->add_header( 'Authorization', $authorization );

		}


	}

?>