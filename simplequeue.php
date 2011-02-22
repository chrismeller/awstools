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
		
		/**
		 * Returns one or all attributes of a queue.
		 * 
		 * @param $queue_name string The name of the queue to fetch attributes for.
		 * @param $attributes string|array The attribute name (or array of names) to fetch. Default: All
		 * 		Available attributes:
		 * 			- All
		 * 			- ApproximateNumberOfMessages - approximate number of visible messages in the queue
		 * 			- ApproximateNumberOfMessagesNotVisibile - approximate number of messages that are not timed-out and not deleted
		 * 			- VisibilityTimeout
		 * 			- CreatedTimestamp
		 * 			- LastModifiedTimestamp
		 * 			- Policy
		 * 			- MaximumMessageSize
		 * 			- MessageRetentionPeriod
		 * 			- QueueArn
		 */
		public function get_queue_attributes ( $queue_url, $attributes = array('All'), $options = array() ) {
		
			// sqs uses the peculiar method of setting the endpoint to the queue_url, not adding a QueueName attribute like every other service would
			$this->endpoint = $queue_url;
			
			// we prepend http:// or https:// as necessary, so trim that off the beginning of the URL
			$this->endpoint = str_replace( 'http://', '', $this->endpoint );
			$this->endpoint = str_replace( 'https://', '', $this->endpoint );
		
			if ( !is_array( $attributes ) ) {
				$attributes = array( $attributes );
			}
			
			$i = 1;
			foreach ( $attributes as $v ) {
				$options['AttributeName.' . $i] = $v;
				$i++;
			}
			
			$result = $this->request( 'GetQueueAttributes', $options, '//aws:Attribute' );
			
			// the results for this call are particularly cumbersome, so make them better
			$a = array();
			foreach ( $result->response as $attribute ) {
				$name = (string)$attribute->Name;
				$value = (string)$attribute->Value;
				
				$a[ $name ] = $value;
			}
			
			// replace the response
			$result->response = $a;
			
			return $result;
			
		}
		
	}

?>