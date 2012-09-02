<?php

	class AWS_Exception extends Exception {
		public $retry = null;
	}

	class AWS_Client_Exception extends AWS_Exception {
		// something with the request needs to change, so we should not retry
		public $retry = false;
	}

	class AWS_Server_Exception extends AWS_Exception {
		// the issue is with amazon, so we should probably just retry
		public $retry = true;
	}

	class AWS_Exception_AccessDenied extends AWS_Client_Exception {}
	class AWS_Exception_ConditionalCheckFailed extends AWS_Client_Exception {}
	class AWS_Exception_IncompleteSignature extends AWS_Client_Exception {}
	class AWS_Exception_LimitExceeded extends AWS_Client_Exception {}
	class AWS_Exception_MissingAuthenticationToken extends AWS_Client_Exception {}
	class AWS_Exception_ProvisionedThroughputExceeded extends AWS_Client_Exception {
		public $retry = true;
	}
	class AWS_Exception_ResourceInUse extends AWS_Client_Exception {}
	class AWS_Exception_ResourceNotFound extends AWS_Client_Exception {}
	class AWS_Exception_Throttling extends AWS_Client_Exception {
		public $retry = true;
	}
	class AWS_Exception_Validation extends AWS_Client_Exception {}
	class AWS_Exception_Serialization extends AWS_Client_Exception {}

	class AWS_Exception_InternalFailure extends AWS_Server_Exception {}
	class AWS_Exception_InternalServerError extends AWS_Server_Exception {}
	class AWS_Exception_ServiceUnavailable extends AWS_Server_Exception {}

?>