<?php

	include('aws.php');
	include('simpledb.php');
	
	$sdb = new SimpleDB( 'a', 'b' );
	$sdb->list_domains();

?>