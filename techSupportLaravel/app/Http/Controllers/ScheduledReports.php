<?php 
/**
 * Class for handling the running of scheduled reports
 *
 * Created: JK April 2019
 */	

namespace App\Http\Controllers;

use DB;
use \App\Helpers\AES;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Mail;

class ScheduledReports extends Controller
{
	
	/**
	 * Checks if scheduled reports are ready to be ran.
	 * Called from the Kernel by the scheduler every minute.
	 *
	 * @return void
	 */	
	public function runScheduledReports()
    {
		$reports = DB::select("SELECT R.Controller, RS.UserID, RS.ScheduleDate, R.Name AS ReportName, RO.Type, RS.Parameters, U.FirstName, U.LastName, U.Login AS EmailAddress
							FROM ReportSchedules RS 
							INNER JOIN Reports R ON R.ID = RS.ReportID 
							INNER JOIN ReportOccurrences RO ON RO.ID = RS.ReportOccurrenceID 
							INNER JOIN Users U ON U.ID = RS.UserID");
		
		$this->determineReportsToRun($reports);
	}	

	/**
	 * Determines if a report needs to be ran, and if it does it the request is sent out to the RunReportAPI.
	 *
	 * @return void
	 */	
	private function determineReportsToRun($reports)
	{
		$runReports = array();
		foreach($reports as $report)
		{
			$runReport = false;
						
			switch($report->Type)
			{
				case 'Do Not Repeat':
					$runReport = $this->scheduledDoNotRepeat($report);
					break;
				case 'Daily':
					$runReport = $this->scheduledDaily($report);
					break;
				case 'Weekly':
					$runReport = $this->scheduledWeekly($report);
					break;
				case 'Monthly':
					$runReport = $this->scheduledMonthly($report);
					break;
				case 'Quarterly':
					$runReport = $this->scheduledQuarterly($report);
					break;
				case 'Yearly':
					$runReport = $this->scheduledYearly($report);
					break;				
				
			}
			
			// $runReport = true; //Remove this when not testing
			if($runReport)
			{
				//Get vars
				$reportVariables = $this->getReportVariables($report);
				$reportVarString = $this->createReportString($reportVariables);
				
				$runReports[] = array("Report"=>$report,"ReportVarString"=>$reportVarString,"ReportVariables"=>$reportVariables);				
			}
			
		}
		
		foreach($runReports as $report)
		{
			$this->callReport($report['Report'],$report['ReportVarString'],$report['ReportVariables']);
		}
		
	}

	/**
	 * Run report with the parameters
	 *
	 * @return void
	 */		
	private function callReport($report, $reportVarString, $reportVariables)
	{			
	
		//Send out report request to reportAPI
		// $url = 'https://devbox2.apexinnovations.com/ajax/reportAPI/index.php?' . $reportVarString;
		$url = 'https://www.apexinnovations.com/ajax/reportAPI/index.php?' . $reportVarString;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_URL,$url);		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
		
		$response = curl_exec($curl);
		curl_close($curl);		
		
		
		//Response is a time for when the report was created
		$response = json_decode($response,1);
		$fileTime = $response['data'];
		$fileLocation = "/tmp/" . $report->Controller . "-" . $fileTime . ".xlsx";
		
		//Create vars needed for email
		$adminName = $report->FirstName . " " . $report->LastName;
		$reportParams = array("Report"=>$report->ReportName,
							"Frequency"=>$report->Type,
							"Initial Scheduled Date"=>$report->ScheduleDate . " CST");
		
		//Look over report parameters and grab the names of information that is set.
		//Also create a StartDate and EndDate variable with a space in the name, used for email.
		foreach($reportVariables as $var => $val)
		{
			if((strpos($var, 'ID') === false) && !is_null($val) && ctype_upper($var{0}))
			{
				if($var == "StartDate" || $var == "EndDate")
				{
					if(!isset($reportParams["Date Range"]))
					{
						$reportParams["Date Range"] = $val;
					}else{
						$reportParams["Date Range"] .= " - " . $val;
					}
				}else{
					//Put System, Org, and Dept names at the beginning of array for stylization of email.
					$keyName = rtrim($var," Name");
					if($keyName == "System" || $keyName == "Organization" || $keyName == "Department")
					{
						$reportParams = array_merge(array($keyName=>$val),$reportParams);
					}else{
						$reportParams[$keyName] = $val;
					}
				}
			}
		}
		
		//Email off
		$email = $report->EmailAddress;
		Mail::send('emails.reportGenerated',['adminName' => $adminName, 'reportParams'=>$reportParams], function($message) use ($fileLocation,$email)
		{
			$message->to($email, 'Admin')->subject('Scheduled Report')->attach($fileLocation);
		});
		
	}
	
	
	/**
	 * Gets the report variables that will need to be sent to the given report controller.
	 *
	 * @return String
	 */	
	private function createReportString($params)
	{
		$reportString = "";				
		foreach($params as $key => $value)
		{
			if(!is_null($value) && strpos($key, 'Name') === false){
				$reportString .= "$key=" . URLEncode($value) . "&";
			}
		}	
		
		$reportString .= "authorizeRunReport=" . $this->createAuthorize();

		return $reportString;		
	}
	
	/**
	 * Gets the report variables that will need to be sent to the given report controller.
	 *
	 * @return Array
	 */	
	private function getReportVariables($report)
	{
		$reportParams = json_decode($report->Parameters);
		
		$params = array(
					"controller"=>$report->Controller,
					"report"=>$report->Controller,
					"dataType"=>"xlsx",
					"SystemID"=>((isset($reportParams->SystemID) && $reportParams->SystemID > 0) ? $reportParams->SystemID : null),
					"System Name"=>((isset($reportParams->SystemID) && isset($reportParams->SystemName) && $reportParams->SystemID > 0) ? $reportParams->SystemName : null),
					"OrganizationID"=>((isset($reportParams->OrganizationID) && $reportParams->OrganizationID > 0) ? $reportParams->OrganizationID : null),
					"Organization Name"=>((isset($reportParams->OrganizationID) && isset($reportParams->OrganizationName) && $reportParams->OrganizationID > 0) ? $reportParams->OrganizationName : null),
					"DepartmentID"=>((isset($reportParams->DepartmentID) && $reportParams->DepartmentID > 0) ? $reportParams->DepartmentID : null),
					"Department Name"=>((isset($reportParams->DepartmentID) && isset($reportParams->DepartmentName) && $reportParams->DepartmentID > 0) ? $reportParams->DepartmentName : null),
					"ProductID"=>((isset($reportParams->ProductID) && $reportParams->ProductID > 0) ? $reportParams->ProductID : null),
					"Product Name"=>(isset($reportParams->ProductID) && isset($reportParams->ProductName) && $reportParams->ProductID > 0 ? $reportParams->ProductName : null),
					"CourseID"=>((isset($reportParams->CourseID) && $reportParams->CourseID > 0) ? $reportParams->CourseID : null),
					"Course Name"=>((isset($reportParams->CourseID) && isset($reportParams->CourseName) && $reportParams->CourseID > 0) ? $reportParams->CourseName : null),
					"LicenseID"=>((isset($reportParams->LicenseID) && $reportParams->LicenseID > 0) ? $reportParams->LicenseID : null),
					"License Name"=>((isset($reportParams->LicenseID) && isset($reportParams->LicenseName) && $reportParams->LicenseID > 0) ? $reportParams->LicenseName : null),
					"UserID"=>((isset($reportParams->UserID) && $reportParams->UserID > 0) ? $reportParams->UserID : null),
					"User Name"=>((isset($reportParams->UserID) && isset($reportParams->UserName) && $reportParams->UserID > 0) ? $reportParams->UserName : null),
					"StartDate"=>(isset($reportParams->StartDate) ? $reportParams->StartDate : null),
					"EndDate"=>(isset($reportParams->EndDate) ? $reportParams->EndDate : null)
				);
		
		return $params;
	}	

	/**
	 * Creates our authorization string to verify that this is a legitimate call to the report API.
	 *
	 * @return String
	 */	
	private function createAuthorize()
	{
		defined('ENGINE_DATA_ENCRYPTION_KEY') or define('ENGINE_DATA_ENCRYPTION_KEY', env('ENGINE_DATA_ENCRYPTION_KEY'));	
		defined('ENGINE_DATA_DELIMITER') or define('ENGINE_DATA_DELIMITER', env('ENGINE_DATA_DELIMITER'));																		
		defined('ENGINE_DATA_ENCRYPTION_MODE') or define('ENGINE_DATA_ENCRYPTION_MODE', false);
		
		$cipher = new AES(ENGINE_DATA_ENCRYPTION_MODE, $this->hex3bin(ENGINE_DATA_ENCRYPTION_KEY));	
		$dataArray = array("authorizeRunReport=" . env('AUTHORIZE_VALUE'),'timestamp='.time());
		shuffle($dataArray);
		
		return bin2hex($cipher->encrypt(implode(ENGINE_DATA_DELIMITER, $dataArray)));
	}

	/**
	 * Used for hex encoding our binary encrypted string
	 *
	 * @return String
	 */		
	private function hex3bin($hexString) 
	{
		if (strlen($hexString) % 2 != 0 || preg_match("/[^\da-fA-F]/",$hexString)) {
			throw new Exception("Invalid hexadecimal number ($hexString) in hex3bin()." . (strlen($hexString) % 2 != 0 ? " Must have even number of characters." : ""));
		}
		return pack("H*", $hexString);
	}

	/**
	 * Determine if scheduled report does not repeat.
	 *
	 * @return Boolean
	 */		
	private function scheduledDoNotRepeat($report)
	{
		$retval = false;		
		$now = date('Y-m-d H:i');
		$scheduledDay = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		
		if($now == $scheduledDay)
		{
			$retval = true;
		}
		
		return $retval;
	}

	/**
	 * Determine if scheduled report runs daily.
	 *
	 * @return Boolean
	 */		
	private function scheduledDaily($report)
	{
		$retval = false;
		$now = date('Y-m-d H:i');
		$schedule = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		
		if($now >= $schedule)
		{
			$nowDay = date('H:i');
			$scheduleNowDay = date('H:i', strtotime($report->ScheduleDate));
			
			if($nowDay == $scheduleNowDay)
			{
				$retval = true;
			}
		}
		
		return $retval;
	}

	/**
	 * Determine if scheduled report runs weekly.
	 *
	 * @return Boolean
	 */			
	private function scheduledWeekly($report)
	{
		$retval = false;
		$scheduleDate = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		$now = date('Y-m-d H:i');

		$dateArray = array();
		for($i = 0; $i <= 520; $i++) //Roughly 10 years worth of weeks
		{
			$dateArray[] = date('Y-m-d H:i',strtotime("+" . $i . " week",strtotime($scheduleDate)));
		}

		if(in_array($now,$dateArray)){
			$retval = true;	
		}
		
		return $retval;		
	}
	
	/**
	 * Determine if scheduled report runs monthly.
	 *
	 * @return Boolean
	 */		
	private function scheduledMonthly($report)
	{
		$retval = false;
		$scheduleDate = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		$now = date('Y-m-d H:i');

		$dateArray = array();
		for($i = 0; $i <= 120; $i++)//10 years worth of months
		{
			$dateArray[] = date('Y-m-d H:i',strtotime("+" . $i . " month",strtotime($scheduleDate)));
		}

		if(in_array($now,$dateArray)){
			$retval = true;	
		}
		
		return $retval;	
	}
	
	/**
	 * Determine if scheduled report runs quarterly.
	 *
	 * @return Boolean
	 */		
	private function scheduledQuarterly($report)
	{
		$retval = false;
		$scheduleDate = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		$now = date('Y-m-d H:i');

		$dateArray = array();
		for($i = 0; $i <= 25; $i++)//25 years worth of years
		{
			$dateArray[] = date('Y-m-d H:i',strtotime("+" . $i . " year",strtotime($scheduleDate)));
		}

		if(in_array($now,$dateArray)){
			$retval = true;	
		}
		
		return $retval;		
	}
	
	/**
	 * Determine if scheduled report runs yearly.
	 *
	 * @return Boolean
	 */		
	private function scheduledYearly($report)
	{
		$retval = false;
		$scheduleDate = date('Y-m-d H:i', strtotime($report->ScheduleDate));
		$now = date('Y-m-d H:i');

		$dateArray = array();
		for($i = 0; $i <= 40; $i++)//10 years worth of quarters
		{
			$dateArray[] = date('Y-m-d H:i',strtotime("+" . ($i * 3) . " year",strtotime($scheduleDate)));
		}

		if(in_array($now,$dateArray)){
			$retval = true;	
		}
		
		return $retval;	
	}

}