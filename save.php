<?php

	function saveHousehold($HouseholdData) {

		include("dbcon.php");
		
		/*
		
			INSERTION OF DATA TO DATABASE
			
		*/
		
		//verification of saved
		$VerificationQuery = sqlsrv_query($Conn, "SELECT
													COUNT(1)
													FROM tbl_household_mobile hh 
														INNER JOIN tbl_family_roster_mobile fam ON hh.hh_id=fam.hh_id
													WHERE hh.hh_id=?
											", array(&$af_household_id) );
		sqlsrv_fetch( $VerificationQuery );
		
		$TotalDatabaseRecords = sqlsrv_get_field($VerificationQuery, 0);
		
		if( $TotalDatabaseRecords != count( $HouseholdData["assessmentForm"]["roster"] ) ) {
			$HouseholdData["error"] = 501;
		}
		
		return $HouseholdData;
	}
?>