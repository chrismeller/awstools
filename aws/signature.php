<?php

	class AWS_Signature {
	
		public static function factory ( $version = 2 ) {
			
			$class_name = 'AWS_Signature_v' . $version;
			
			return new $class_name();
			
		}
		
	}

?>