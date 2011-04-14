<?php

	class SimpleDB extends AWS_Service {
		
		const REGION_US_E_1 = 'sdb.amazonaws.com';
		const REGION_US_W_1 = 'sdb.us-west-1.amazonaws.com';
		const REGION_EU_W_1 = 'sdb.eu-west-1.amazonaws.com';
		const REGION_APAC_SE_1 = 'sdb.ap-southeast-1.amazonaws.com';
		const REGION_APAC_NE_1 = 'sdb.ap-northeast-1.amazonaws.com';
		
		// define the API version, which is required for all sub-classes
		protected $api_version = '2009-04-15';
		
		protected $endpoint = null;
		protected $xml_namespace = 'http://sdb.amazonaws.com/doc/2009-04-15/';		// the trailing slash is required
		
		// simpledb only supports v2 for REST requests
		protected $signature_version = 2;
		
		public function __construct ( $aws_access_key = null, $aws_secret = null, $endpoint = self::REGION_US_E_1 ) {
			
			parent::__construct( $aws_access_key, $aws_secret );
			
			$this->endpoint = $endpoint;
			
		}
		
		public function list_domains ( $max_number_of_domains = null, $options = array() ) {
		
			if ( $max_number_of_domains != null ) {
				$options['MaxNumberOfDomains'] = $max_number_of_domains;
			}
		
			$result = $this->request( 'ListDomains', $options, '//aws:DomainName' );
			
			return $result;
			
		}
		
		public function create_domain ( $name, $options = array() ) {
		
			$options['DomainName'] = $name;
			
			$result = $this->request( 'CreateDomain', $options );
			
			return $result;
			
		}
		
		public function delete_domain ( $name, $options = array() ) {
			
			$options['DomainName'] = $name;
			
			$result = $this->request( 'DeleteDomain', $options );
			
			return $result;
			
		}
		
		public function delete_item ( $domain, $item_name, $options = array() ) {
			
			$options['DomainName'] = $domain;
			$options['ItemName'] = $item_name;
			
			$result = $this->request( 'DeleteAttributes', $options );
			
			return $result;
			
		}
		
		public function domain_metadata ( $name, $options = array() ) {
			
			$options['DomainName'] = $name;
			
			$result = $this->request( 'DomainMetadata', $options );
			
			return $result;
			
		}
		
		public function select ( $query, $consistent_read = false, $options = array() ) {
			
			if ( $consistent_read ) {
				$options['ConsistentRead'] = 'true';
			}
			
			$options['SelectExpression'] = $query;
			
			$result = $this->request( 'Select', $options, '//aws:Item' );
			
			return $result;
			
		}
		
		public function select_count_all ( $query, $consistent_read = false, $options = array() ) {
			
			$total = 0;
			do {
			
				$result = $this->select( $query, $consistent_read, $options );
				$options['NextToken'] = $result->next_token;
				
				$total = $total + $result->response[0]->Attribute->Value;
				
			}
			while ( $result->next_token != null );
			
			return $total;
			
		}
		
		/**
		 * Creates or replaces attributes in an item.
		 * 
		 * Examples:
		 * 		<code>
		 * 			// appends attr1 with a single value ('value1') and attr2 with multiple values ('value2a' and 'value2b') to any existing values for Item1
		 * 			$sdb->put_attributes( 'TestDomain', 'Item1', array( 'attr1' => 'value1', 'attr2' => array( 'value2a', 'value2b' ) ) );
		 *		</code>
		 * 
		 * @todo how can we handle per-attribute replaces?
		 * @todo we need to figure out how to handle 'exists' for expected attributes
		 * 
		 * @param $domain string The name of the domain
		 * @param $name string The name of the item
		 * @param $attributes array Associative array of name => value pairs or name => array( value, value, ... ) pairs to add
		 * @param $expected array Associative array of name => value pairs or name => array( value, value, ... ) pairs to check for before saving
		 * @param $options array Any additional options to pass in request
		 * @return AWS_Response
		 * @throws AWS_Exception
		 */
		public function put_attributes ( $domain, $name, $attributes = array(), $replace_all = false, $expected = array(), $options = array() ) {
			
			$options['DomainName'] = $domain;
			$options['ItemName'] = $name;
			
			$i = 1;
			foreach ( $attributes as $k => $v ) {
				
				if ( !is_array( $v ) ) {
					$v = array( $v );
				}
				
				foreach ( $v as $vv ) {
					$options['Attribute.' . $i . '.Name'] = $k;
					$options['Attribute.' . $i . '.Value'] = $vv;
					
					if ( $replace_all == true ) {
						$options['Attribute.' . $i . '.Replace'] = 'true';
					}
					
					$i++;
				}
				
			}
			
			$i = 1;
			foreach ( $expected as $k => $v ) {
				
				if ( !is_array( $v ) ) {
					$v = array( $v );
				}
				
				foreach ( $v as $vv ) {
					$options['Expected.' . $i . '.Name'] = $k;
					$options['Expected.' . $i . '.Value'] = $vv;
				}
				
			}
			
			$result = $this->request( 'PutAttributes', $options );

			return $result;

		}
		
		public function batch_put_attributes ( $domain, $items = array(), $replace_all = false, $options = array() ) {
			
			$options['DomainName'] = $domain;
			
			$i = 1;
			foreach ( $items as $item_name => $attributes ) {
				
				$options['Item.' . $i . '.ItemName'] = $item_name;
				
				$j = 1;
				foreach ( $attributes as $attribute_name => $attribute_values ) {
					
					// if $attribute_values is not an array, make it one so we can treat single values and multiple values the same
					if ( !is_array( $attribute_values ) ) {
						$attribute_values = array( $attribute_values );
					}
					
					foreach ( $attribute_values as $value ) {
						$options['Item.' . $i . '.Attribute.' . $j . '.Name'] = $attribute_name;
						$options['Item.' . $i . '.Attribute.' . $j . '.Value'] = $value;
						
						if ( $replace_all == true ) {
							$options['Item.' . $i . '.Attribute.' . $j . '.Replace'] = 'true';
						}
						
						$j++;
					}
					
				}
				
				$i++;
				
			}
			
			$result = $this->request( 'BatchPutAttributes', $options );
			
			return $result;
			
		}
		
		public function get_attributes ( $domain, $item_name, $attributes = array(), $options = array() ) {
			
			$options['DomainName'] = $domain;
			$options['ItemName'] = $item_name;
			
			// if $attributes isn't an array, turn it into one - lets us pass in a single string as well as an array
			if ( !is_array( $attributes ) ) {
				$attributes = array( $attributes );
			}
			
			$i = 1;
			foreach ( $attributes as $attribute ) {
				$options['AttributeName.' . $i] = $attribute;
			}
			
			$result = $this->request( 'GetAttributes', $options, '//aws:Attribute' );
			
			// the results for this call are particularly cumbersome, so make them better
			$a = array();
			foreach ( $result->response as $attribute ) {
				$name = (string)$attribute->Name;
				$value = (string)$attribute->Value;
				
				// if the attribute name is already set, make it an array and append the new result
				if ( isset( $a[ $name ] ) ) {
					
					// if it's not already an array, make it one
					if ( !is_array( $a[ $name ] ) ) {
						$a[ $name ] = array( $a[ $name ] );
					}
					
					// and either way, append the new value
					$a[ $name ][] = $value;
				}
				else {
					$a[ $name ] = $value;
				}
			}
			
			// replace the response
			$result->response = $a;
			
			return $result;
			
		}
		
	}

?>