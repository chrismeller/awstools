<?php

	class SimpleEmail extends AWS_Service {
		
		const REGION_US_E_1 = 'email.us-east-1.amazonaws.com';
		
		// define the API version, which is required for all sub-classes
		protected $api_version = '2010-12-01';
		
		protected $endpoint = null;
		protected $xml_namespace = 'http://ses.amazonaws.com/doc/2010-12-01/';
		
		// simpleemail only supports v3
		protected $signature_version = 3;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null, $endpoint = self::REGION_US_E_1 ) {
			
			parent::__construct( $aws_access_key, $aws_secret );
			
			$this->endpoint = $endpoint;
			
		}
		
		public function list_verified_email_addresses ( $options = array() ) {
		
			$result = $this->request( 'ListVerifiedEmailAddresses', $options, '//aws:VerifiedEmailAddresses/aws:member' );
			
			return $result;
			
		}
		
		public function verify_email_address ( $email, $options = array() ) {
			
			$options['EmailAddress'] = $email;
			
			$result = $this->request( 'VerifyEmailAddress', $options );
			
			return $result;
			
		}
		
		public function get_send_quota ( $options = array() ) {
			
			$result = $this->request( 'GetSendQuota', $options );
			
			return $result;
			
		}
		
		public function get_send_statistics ( $options = array() ) {
			
			$result = $this->request( 'GetSendStatistics', $options );
			
			return $result;
			
		}
		
	}

?>