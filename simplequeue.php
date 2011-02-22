<?php

	class SimpleQueue extends AWS_Service {
		
		const REGION_US_E_1 = 'sqs.us-east-1.amazonaws.com';
		const REGION_US_W_1 = 'sqs.us-west-1.amazonaws.com';
		const REGION_EU_W_1 = 'sqs.eu-west-1.amazonaws.com';
		const REGION_APAC_SE_1 = 'sqs.ap-southeast-1.amazonaws.com';
		
		// define the API version, which is required for all sub-classes
		protected $api_version = '2009-02-01';
		
		protected $endpoint = null;
		protected $xml_namespace = 'http://queue.amazonaws.com/doc/2009-02-01/';		// the trailing slash is required
		
		// simpledb only supports v2 for REST requests
		protected $signature_version = 2;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null, $endpoint = self::REGION_US_E_1 ) {
			
			parent::__construct( $aws_access_key, $aws_secret );
			
			$this->endpoint = $endpoint;
			
		}
		
		public function list_queues ( $name_prefix = null, $options = array() ) {
		
			if ( $name_prefix != null ) {
				$options['QueueNamePrefix'] = $name_prefix;
			}
		
			$result = $this->request( 'ListQueues', $options, '//aws:QueueUrl' );
			
			return $result;
			
		}
		
	}

?>