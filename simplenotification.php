<?php

	class SimpleNotification extends AWS_Service {
		
		const REGION_US_E_1 = 'sns.us-east-1.amazonaws.com';
		const REGION_US_W_1 = 'sns.us-west-1.amazonaws.com';
		const REGION_EU_W_1 = 'sns.eu-west-1.amazonaws.com';
		const REGION_APAC_SE_1 = 'sns.ap-southeast-1.amazonaws.com';
		
		// define the API version, which is required for all sub-classes
		protected $api_version = '2010-03-31';
		
		protected $endpoint = null;
		protected $xml_namespace = 'http://sns.amazonaws.com/doc/2010-03-31/';		// the trailing slash is required
		
		// sns only supports v2 for REST requests
		protected $signature_version = 2;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null, $endpoint = self::REGION_US_E_1 ) {
			
			parent::__construct( $aws_access_key, $aws_secret );
			
			$this->endpoint = $endpoint;
			
		}
		
		public function list_topics ( $options = array() ) {
			
			$result = $this->request( 'ListTopics', $options, '//aws:Topics/aws:member/aws:TopicArn' );
			
			return $result;
			
		}
		
		public function create_topic ( $name, $options = array() ) {
			
			$options['Name'] = $name;
			
			$result = $this->request( 'CreateTopic', $options, '//aws:TopicArn' );
			
			$result->response = (string)$result->response[0];
			
			return $result;
			
		}
		
		public function delete_topic ( $topic_arn, $options = array() ) {
			
			$options['TopicArn'] = $topic_arn;
			
			$result = $this->request( 'DeleteTopic', $options );
			
			return $result;
			
		}
		
		/**
		 * Subscribe to an endpoint.
		 * 
		 * @param string $topic_arn The ARN of the topic you want to subscribe to.
		 * @param string $protocol The protocol you want to use:
		 *		- http: delivery of JSON-encoded message via HTTP POST
		 *		- https: delivery of JSON-encoded message via HTTPS POST
		 *		- email: delivery of message via SMTP
		 *		- email-json: delivery of JSON-encoded message via SMTP
		 *		- sqs: delivery of JSON-encoded message to an Amazon SQS queue
		 * @param string $endpoint The endpoint that you want to receive notifications:
		 *		- For the http protocol, a URL beginning with "http://"
		 *		- For the https protocol, a URL beginning with "https://"
		 *		- For the email protocol, an email address.
		 *		- For the email-json protocol, an email address.
		 *		- For the sqs protocol, the ARN of an Amazon SQS queue.
		 * @return AWS_Response The ARN of the subscription, if the service was able to create the subscription immediately (without pending confirmation). "pending confirmation" otherwise.
		 */
		public function subscribe ( $topic_arn, $protocol, $endpoint, $options = array() ) {
			
			$options['TopicArn'] = $topic_arn;
			$options['Protocol'] = $protocol;
			$options['Endpoint'] = $endpoint;
			
			$result = $this->request( 'Subscribe', $options, '//aws:SubscriptionArn' );
			
			$result->response = (string)$result->response[0];
			
			return $result;
			
		}
		
		/**
		 * Confirm a subscription that was pending confirmation.
		 * 
		 * @param string $topic_arn The ARN of the topic you want to subscribe to.
		 * @param string $token The short-lived token sent to the endpoint during the Subscribe action.
		 * @param boolean $require_authentication If true, unauthenticated unsubscriptions are forbidden and only the topic owner and subscription owner can unsubscribe.
		 * @param array $options Array of other options to hand to the request.
		 */
		public function confirm_subscription ( $topic_arn, $token, $require_authentication = false, $options = array() ) {
			
			$options['TopicArn'] = $topic_arn;
			$options['Token'] = $token;
			
			if ( $require_authentication == true ) {
				$options['AuthenticateOnUnsubscribe'] = 'true';
			}
			
			$result = $this->request( 'ConfirmSubscription', $options, '//aws:SubscriptionArn' );
			
			$result->response = (string)$result->response[0];
			
			return $result;
			
		}
		
	}

?>