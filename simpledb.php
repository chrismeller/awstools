<?php

	class SimpleDB extends AWS_Service {
		
		const REGION_US_E_1 = 'sdb.amazonaws.com';
		const REGION_US_W_1 = 'sdb.us-west-1.amazonaws.com';
		const REGION_EU_W_1 = 'sdb.eu-west-1.amazonaws.com';
		const REGION_APAC_SE_1 = 'sdb.ap-southeast-1.amazonaws.com';
		
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
		
		/**
		 * @todo how can we handle per-attribute replaces?
		 * @todo we need to figure out how to handle 'exists' for expected attributes
		 */
		public function put_attributes ( $domain, $name, $attributes = array(), $replace_all = false, $expected = array(), $options = array() ) {
			
			$options['DomainName'] = $domain;
			$options['ItemName'] = $name;
			
			$i = 1;
			foreach ( $attribute as $k => $v ) {
				
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
			
		}
		
	}

?>