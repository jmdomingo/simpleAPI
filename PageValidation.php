<?php
		
	function checkCompleteHousehold($HouseholdData){
		
		
		/*Check the completeness and validity of data required per household using 
			if condition
			pregmatch
		eg.
		if( $Time_Start > $Time_End ) {
			$HouseholdData["error"] = 301;
			array_push( $HouseholdData["errorList"], "Time started is greater than time ended" );
		}
		*/
		
		return $HouseholdData;
	}

	//==============================================================================================================================	
	
	function checkCompleteRoster($HouseholdData){
	
		/*Check the completeness and validity of data required per member of household roster such as 
			if age, sex, status, work, etch
			
		eg.
		if( !preg_match("/^[a-zA-Z ñÑ.-]{1,40}$/", $HouseholdData["assessmentForm"]["roster"][$i]) ) {
				$HouseholdData["error"] = 301;
				array_push( $HouseholdData["errorList"], $MemberError . "Invalid Last Name" );
			
		}
		*/
		
		return $HouseholdData;
	}
	
	//==============================================================================================================================	

	/*User checking if exist*/
	function checkUser($HouseholdData){
		include("dbcon.php");
		
		$username = $HouseholdData["username"];
		$password = $HouseholdData["password"];
		
		$Query = sqlsrv_query($Conn,
			"SELECT uid
			FROM [sys_staff]
			WHERE username=?
			AND password=?
			AND [status] = 1",
			array( &$username, &$password ) );
		if( sqlsrv_has_rows($Query) ) {
			$row = sqlsrv_fetch_array( $Query, SQLSRV_FETCH_ASSOC);
			$HouseholdData["enumerator_id"] = $row['uid'];
		} else{
			$HouseholdData["error"] = 400;
		}
		return $HouseholdData;
	}
	
	//==============================================================================================================================	
	
	/*Check if activity is active in the region	*/
	function checkActivity($HouseholdData){
		include("dbcon.php");
		
		$af_q2_region = $HouseholdData["assessmentForm"]["region"];
		$activity = $HouseholdData["activity"];
		
		$Query = sqlsrv_query($Conn,
			"SELECT 
				1
			FROM tbl_assessment_region tar
			WHERE tar.assessment_encoding_status = 1 
					AND tar.assessment_encoding_status_region = 0 
					AND tar.region = ?
					AND tar.assessment = ?
					AND tar.archive = 0",
			array( &$af_q2_region, &$activity) );
		
		sqlsrv_fetch( $Query );	
		
		if( !sqlsrv_has_rows($Query) ) {
			$HouseholdData["error"] = 401;
		}
		
		return $HouseholdData;
	}
	
	//==============================================================================================================================	
	
	/*Check if activity is active in the region	*/
	function checkEnum($HouseholdData){
		include("dbcon.php");
		
		$enumerator_id = $HouseholdData["enumerator_id"];
		$af_q2_city_municipality = $HouseholdData["assessmentForm"]["af_q2_city_municipality"];
		$activity = $HouseholdData["activity"];
		
		$Query = sqlsrv_query($Conn,
			"SELECT 
				staff.area_supervisor_id asID
			FROM tbl_staff_assignment staff
			WHERE 
				staff.enumerator_id = ?
					AND staff.city = ?
					AND staff.archive = 0
					AND staff.assessment = ?",
			array( &$enumerator_id, &$af_q2_city_municipality, &$activity ) );

		if( sqlsrv_has_rows($Query) ) {
			$row = sqlsrv_fetch_array( $Query, SQLSRV_FETCH_ASSOC);
			$HouseholdData["area_supervisor_id"] = $row['asID'];
		} else{
			$HouseholdData["error"] = 402;
		}
		return $HouseholdData;
	}
	
	//==============================================================================================================================	
	
	/*Check if Household ID already exists*/
	function checkHHID($HouseholdData){
		include("dbcon.php");
		// $Num = "%" . $HouseholdData["assessmentForm"]["af_house_num"];
		$HHID = $HouseholdData["assessmentForm"]["hhid"];
		
		$MobileQuery = sqlsrv_query($Conn,
			"SELECT hh_id
			FROM tbl_household_mobile
			WHERE hh_id LIKE ?",
			array( &$HHID ) );
		
		$WebQuery = sqlsrv_query($Conn,
			"SELECT hh_id
			FROM tbl_household
			WHERE hh_id LIKE ?",
			array( &$HHID ) );
		
		
		if( sqlsrv_has_rows($MobileQuery) || sqlsrv_has_rows($WebQuery)  ) {
			$HouseholdData["error"] = 301;
			array_push( $HouseholdData["errorList"], "Household ID already exists" );
		} 
		
		return $HouseholdData;
	}
	
?>