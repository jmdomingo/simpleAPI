<?php
	$Server = "#";
	$connectionInfo = array( "Database"=>"db_sql", "UID"=>"user", "PWD"=>"password");
	$Conn = sqlsrv_connect( $Server, $connectionInfo);
	
	if( !$Conn ) {
		$HouseholdData["error"] = 500;
	} else {
		//echo "Connected";
	} 
?>