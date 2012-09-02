<?php

	class DynamoDB extends AWS_Service_JSON {

		const REGION_US_E_1 = 'dynamodb.us-east-1.amazonaws.com';
		const REGION_US_W_1 = 'dynamodb.us-west-1.amazonaws.com';
		const REGION_US_W_2 = 'dynamodb.us-west-2.amazonaws.com';
		const REGION_EU_W_1 = 'dynamodb.eu-west-1.amazonaws.com';
		const REGION_APAC_SE_1 = 'dynamodb.ap-southeast-1.amazonaws.com';
		const REGION_APAC_NE_1 = 'dynamodb.ap-northeast-1.amazonaws.com';

		const TYPE_STRING = 'S';
		const TYPE_NUMBER = 'N';
		const TYPE_BINARY = 'B';
		const TYPE_STRING_SET = 'SS';
		const TYPE_NUMBER_SET = 'NS';
		const TYPE_BINARY_SET = 'BS';

		// define the API version, which is required for all sub-classes
		protected $api_version = '20111205';		// it's annoying that this one doesn't use -'s like all the others... grr!

		protected $endpoint = null;

		protected $signature_version = 4;

		public function __construct ( $aws_access_key = null, $aws_secret = null, $endpoint = self::REGION_US_E_1 ) {

			parent::__construct( $aws_access_key, $aws_secret );

			$this->endpoint = $endpoint;

		}

		public function list_tables ( $limit = null, $start_table = null, $options = array() ) {

			if ( $limit != null ) {
				$options['Limit'] = $limit;
			}

			if ( $start_table != null ) {
				$options['ExclusiveStartTableName'] = $start_table;
			}

			$result = $this->request( 'ListTables', $options );

			return $result->TableNames;

		}

		protected function request ( $action, $options = array(), $headers = array() ) {

			$headers['X-AMZ-Target'] = 'DynamoDB_' . $this->api_version . '.' . $action;

			return parent::request( $action, $options, $headers );

		}

		public function get_item ( $table, $hash_key, $range_key = null, $attributes = array(), $consistent_read = false, $options = array() ) {

			$options['TableName'] = $table;
			$options['Key'] = $this->key( $hash_key, $range_key );

			if ( !empty( $attributes ) ) {
				$options['AttributesToGet'] = $attributes;
			}

			$options['ConsistentRead'] = $consistent_read;

			$result = $this->request( 'GetItem', $options );

			return $result;

		}

		public function delete_item ( $table, $hash_key, $range_key = null, $expected = array(), $return_values = 'NONE', $options = array() ) {

			$options['TableName'] = $table;
			$options['Key'] = $this->key( $hash_key, $range_key );

			if ( !empty( $expected ) ) {
				$options['Expected'] = $expected;
			}

			$options['ReturnValues'] = $return_values;

			$result = $this->request( 'DeleteItem', $options );

			return $result;

		}

		public function put_item ( $table, $attributes, $return_values = 'NONE', $options = array() ) {

			$options['TableName'] = $table;
			$options['Item'] = $attributes;
			$options['ReturnValues'] = $return_values;

			$result = $this->request( 'PutItem', $options );

			return $result;

		}

		public function describe_table ( $table, $options = array() ) {

			$options['TableName'] = $table;

			$result = $this->request( 'DescribeTable', $options );

			return $result;

		}

		public function string ( $value = null ) {
			return array( self::TYPE_STRING => (string)$value );
		}

		public function number ( $value = null ) {
			return array( self::TYPE_NUMBER => (string)$value );
		}

		public function binary ( $value = null ) {
			return array( self::TYPE_BINARY => $value );
		}

		public function string_set ( $value = null ) {
			return array( self::TYPE_STRING_SET => $value );
		}

		public function number_set ( $value = null ) {
			return array( self::TYPE_NUMBER_SET => $value );
		}

		public function binary_set ( $value = null ) {
			return array( self::TYPE_BINARY_SET => $value );
		}

		private function key ( $hash_key, $range_key = null ) {
			$key = array(
				'HashKeyElement' => $hash_key,
			);

			if ( $range_key != null ) {
				$key['RangeKeyElement'] = $range_key;
			}

			return $key;
		}

	}

?>