<?php
	
	function computeAssessmentAge( $EnumerationDate, $Birthday ) {
		$d1 = new DateTime($EnumerationDate);
		$d2 = new DateTime($Birthday);

		$diff = $d2->diff($d1);
		return $diff->y;
	}
	
	
	function getAreaData($HouseholdData) {
		include("dbcon.php");
		
		$BarangayCode = $HouseholdData["assessmentForm"]["baranggay"];
		
		$AreaDataQuery = sqlsrv_query($Conn, 
									"SELECT 
										brgy.brgy_name,
										brgy.urb_rur,
										city.city_name,
										reg.region_nick
										FROM lib_brgy brgy 
											INNER JOIN lib_cities city ON city.city_code=brgy.city_code
											INNER JOIN lib_regions reg ON LEFT(reg.region_code,2)=LEFT(brgy.brgy_code,2)
										WHERE brgy_code=?", array(&$BarangayCode) );
		sqlsrv_fetch( $AreaDataQuery );
		
		
		$HouseholdData["assessmentForm"]["barangayName"] = sqlsrv_get_field($AreaDataQuery, 0);
		$HouseholdData["assessmentForm"]["urbRur"] = sqlsrv_get_field($AreaDataQuery, 1);
		$HouseholdData["assessmentForm"]["cityName"] = trim( sqlsrv_get_field($AreaDataQuery, 2) );
		$HouseholdData["assessmentForm"]["regionName"] = trim( sqlsrv_get_field($AreaDataQuery, 3) );
		
		return $HouseholdData;
	}
	
	function getActivityName($HouseholdData) {
		include("dbcon.php");
		
		$ActivityID = $HouseholdData["activity"];
		
		$ActivityQuery = sqlsrv_query($Conn, 
									"SELECT 
										assessment
										FROM tbl_assessments
										WHERE assessment_id=?", array(&$ActivityID) );
		sqlsrv_fetch( $ActivityQuery );
		
		
		$HouseholdData["activityName"] = sqlsrv_get_field($ActivityQuery, 0);
		
		return $HouseholdData;
	}

?>
