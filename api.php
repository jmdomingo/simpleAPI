<?php
	// allow cross-origin policy
	header("Access-Control-Allow-Origin: *");

	// raw
	$formData = var_export($_POST,true);
	
	
	// log 
	$logFIle = "logs.txt";
	$fh = fopen($logFIle, 'a') or die("can't open file");
	// append
	$now = "\n\n". 'Time: '. date('Y-m-d H:i:s') ."\n";
	$stringData = $now . "RAW POST DATA: \n" . print_r($formData,true) . "\n\n";
	fwrite($fh, $stringData);
	
	
	//==========================================================
	// PROCESS THE JSON DATA.
	$json = $_POST["json"];
	$HouseholdData = json_decode($json, true);
	$HouseholdData["error"] = 0;
	$HouseholdData["assessmentForm"]["hhid"] = "";	
	
	$stringData = "DATA FROM JSON: \n" . print_r($HouseholdData, true) . "\n";
	fwrite($fh, $stringData);
	fclose($fh);
	
	
	
	//==========================================================
	// Check errors on JSON decode
	$jsonDecodeSuccess = true;
	$jsonDecodeErrorMsg = "";
	switch(json_last_error()) {
		case JSON_ERROR_DEPTH:
			$jsonDecodeErrorMsg = "Maximum stack depth exceeded";
		break;
		case JSON_ERROR_CTRL_CHAR:
			$jsonDecodeErrorMsg = "Unexpected control character found";
		break;
		case JSON_ERROR_SYNTAX:
			$jsonDecodeErrorMsg = "Syntax error, malformed JSON";
		break;
		case JSON_ERROR_NONE:
			$jsonDecodeSuccess = true;
		break;
	}
	
	include("dbcon.php");
	
	if($HouseholdData["error"] != 500) {
	
		if($jsonDecodeSuccess == true) {
			
			$HouseholdData["dev"] = "";
			
			//==========================================================
			// Default activity
			$Activity = 1;
			
			
			//==========================================================
			// User authentication and data checking
			include("PageValidation.php");
			
			$HouseholdData["activity"] = $Activity;
			
			$HouseholdData["errorList"] = array();
			
			$HouseholdData = checkUser($HouseholdData);
			
			
			if($HouseholdData["error"] != 400){

				//Check if activity is active in the region
				$HouseholdData = checkActivity($HouseholdData);
				
				include("functions.php");
				
				$HouseholdData = getAreaData($HouseholdData);
				$HouseholdData = getActivityName($HouseholdData);
				
				if($HouseholdData["error"] != 401){
					
					// Check if enumerator is valid to enumerate household within the city
					$HouseholdData = checkEnum($HouseholdData);
				
					
					if($HouseholdData["error"] != 402){
					
						// construct HH ID						
						$HouseholdData["assessmentForm"]["hhid"] = $household_id;
						
						
						// Check if HH ID already exist in database
						$HouseholdData = checkHHID($HouseholdData);
						
						if($HouseholdData["error"] == 0) {
							
							// Check if data of households is not empty
							$HouseholdData = checkCompleteHousehold($HouseholdData);
							
							// put errors in $HouseholdData["errorlist"] using array_push
							$HouseholdData = checkCompleteRoster( $HouseholdData );
							
						}
					}
				}
			}
			
			
			//==========================================================
			// Saving module
			if($HouseholdData["error"] == 0) {
				include("save.php");
				$HouseholdData = saveHousehold($HouseholdData);
				
				if($HouseholdData["error"] == 0) {
					$HouseholdData["error"] = 100;
				}
			}
			
		} else {
			$HouseholdData["error"] = 300;
		}
	}
	
	$Response = Array('status' => '0','message' => "Unknown error occurred. Please contact your system administrator.");
	
	//==========================================================
	// Respond via JSON.	
	$Response = array();
	switch($HouseholdData["error"]) {
		case 500:
			$Response = Array('status' => '500','message' => "Database or application error, please contact your system administrator.");
			break;
		case 501:
			$Response = Array('status' => '501','message' => "Database error, please contact your system administrator.");
			break;
		case 400:
			$Response = Array('status' => '400','message' => "Invalid username or password.");
			break;
		case 401:
			$Message = $HouseholdData["assessmentForm"]["regionName"] . " is not allowed to encode for activity " . $HouseholdData["activityName"] . ".";
			$Response = Array('status' => '401','message' => $Message );
			break;
		case 402:
			$Response = Array('status' => '402','message' => "User is not allowed to encode to activity " . $HouseholdData["activityName"] . " in city " . $HouseholdData["assessmentForm"]["cityName"]);
			break;
		case 300:
			$Response = Array('status' => '300','message' => "Error in JSON parsing due to: " . $jsonDecodeErrorMsg);
			break;
		case 301:
			$FinalErrorList = "Errors of household " . $HouseholdData["assessmentForm"]["hhid"] . ":\n";
			foreach($HouseholdData["errorList"] as $Error) {
				$FinalErrorList .= "- " . $Error . "\n";
			}
			$FinalErrorList = preg_replace('/\n$/', '', $FinalErrorList);
			$Response = Array('status' => '301','message' => $FinalErrorList);
			break;
		//for dev usage
		case 101:
			$Response = Array('status' => '101','message' => "Successful synchronization of household: " . $HouseholdData["assessmentForm"]["hhid"] . ".");
			break;
		case 100:
			$Response = Array('status' => '100','message' => "API received household: " . $HouseholdData["assessmentForm"]["hhid"] . ".");
			break;
		default:
			$Response = Array('status' => '0','message' => "Unknown error occurred. Please contact your system administrator.");
	}
	
	// output response
	echo json_encode($Response);
	//==========================================================
?>