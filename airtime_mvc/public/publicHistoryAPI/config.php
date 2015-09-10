<?php

	$airtimeConfig = parse_ini_file('/etc/airtime/airtime.conf');
	
	$dbName = $airtimeConfig['dbname'];
	
	$dbUser = $airtimeConfig['dbuser'];
	
	$dbPassword = $airtimeConfig['dbpass'];
	
?>