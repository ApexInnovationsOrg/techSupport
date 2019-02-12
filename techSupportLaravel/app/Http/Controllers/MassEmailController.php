<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\SupportTicket;

use App\Helpers\EmailParser;
use App\Helpers\SessionHelper;

use DB;
use App\Employee;
use App\Users;
use App\SupportTypes;
use App\SupportTicketTransfer;

use Illuminate\Http\Request as HttpRequest;

use Request;
use Auth;
use Validator;
use Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;


// use PhpImap\Mailbox as ImapMailbox;
// use PhpImap\IncomingMail;
// use PhpImap\IncomingMailAttachment;


class MassEmailController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		ini_set('max_execution_time', 99999999);
		ini_set('memory_limit', -1);
		set_time_limit(99999999);
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('emails');
	}

	public function sendEmail($emailType = null)
	{
		switch ($emailType)
		{
			case 'test':
				// $users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login LIKE 'eddie@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.* FROM Users AS U WHERE Login LIKE 'katie@apexinnovations.com'");


				// dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					// Mail::send('emails.sepsis.default',['name'=>$user->Name], function($message) use ($user)
			  //       {
			  //           $message->to($user->Login, $user->Name);
			  //           $message->subject('Now Available - Sepsis-A Systemic Response Online Education');
			  //           $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			  //           $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'impulsetq');

			  //       });


					if (filter_var($user->Login, FILTER_VALIDATE_EMAIL)) {
						Mail::send('emails.sepsis.defaultSepsis',['name'=>$user->Name], function($message) use ($user)
				        {
				            $message->to($user->Login, $user->Name);
				            $message->subject('Now Available - Sepsis-A Systemic Response Online Education');
				            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
				            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'sepsisRelease');

				        });

				    }
				}
				return redirect('/email')->with('message','test email successfully sent'); 


			case 'sepsis':

				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users U WHERE FirstName IS NOT NULL AND LastName IS NOT NULL AND Login NOT LIKE '%|%'AND FirstName <> ''AND LastName <> ''AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'");
				// dd($users);
				foreach($users as $user)
				{	
					if (filter_var($user->Login, FILTER_VALIDATE_EMAIL)) {
						Mail::send('emails.sepsis.defaultSepsis',['name'=>$user->Name], function($message) use ($user)
				        {
				            $message->to($user->Login, $user->Name);
				            $message->subject('Now Available - Sepsis-A Systemic Response Online Education');
				            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
				            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'sepsisRelease');

				        });

				    }
				}
				return redirect('/email')->with('message','test email successfully sent to '. count($users) .' users'); 
				
			case 'imp2user':
				$users = DB::select("
									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM Users U
									INNER JOIN LicenseSeats LS ON LS.UserID = U.ID
									INNER JOIN LicensePeriods LP ON LP.ID = LS.LicensePeriodID
									INNER JOIN Licenses L ON L.ID = LP.LicenseID
									INNER JOIN Products P ON P.ID = L.ProductID
									WHERE Login NOT LIKE '%apex%'
									AND LMS = 'N'
									AND P.ID = 9
									AND (LS.ExpirationDate > NOW() OR LP.EndDate > NOW())
									AND U.Login NOT IN('dkrueger@mchhs.org')

									UNION

									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM OrganizationAdmins OA
									INNER JOIN Users U ON U.ID = OA.UserID
									INNER JOIN Licenses L ON L.OrganizationID = OA.OrganizationID
									INNER JOIN LicensePeriods LP ON LP.LicenseID = L.ID
									WHERE L.ProductID = 9
									AND EndDate > NOW()
									AND Login NOT LIKE '%apex%'
									AND LMS = 'N'
									
									UNION 
									
									SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login
									FROM (
										SELECT U.ID, U.FirstName, U.LastName, U.Login, U.DepartmentID, U.LMS
										FROM Users U INNER JOIN DepartmentAdmins DA ON DA.UserID = U.ID
									) AS U
									INNER JOIN Departments D ON D.ID = U.DepartmentID
									INNER JOIN Organizations O ON O.ID = D.OrganizationID
									INNER JOIN Licenses L ON L.OrganizationID = O.ID
									INNER JOIN LicensePeriods LP ON LP.LicenseID = L.ID
									WHERE L.ProductID = 9
									AND EndDate > NOW()
									AND Login NOT LIKE '%apex%'
									AND LMS = 'N'
									");


				// dd($users);
					
				foreach($users as $user)
				{	
					error_log(print_r($user->Login,1));
					if (filter_var($user->Login, FILTER_VALIDATE_EMAIL)) {
						Mail::queue('emails.impulse2TQ.careminderEmail',['name'=>$user->Name], function($message) use ($user)
				        {
				            $message->to($user->Login, $user->Name);
				            $message->subject('imPULSE 2.0 Test Question Analysis and Adjustments');
				            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
				            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'impulsetq');

				        });

				    }
				}

				return redirect('/email')->with('message','test email successfully sent'); 
				break;















			case 'CAhemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue('emails.hemi2.base','emails.hemi2.Current_Hemi_Admins_PlainTextEmail', ['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Canadian Hemispheres 2.0 coming soon!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
				}
				return redirect('/email')->with('message','hemiAdmins email successfully sent'); 
			case 'hemiAdminsReminder':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue('emails.hemi2.careminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Canadian Hemispheres 2.0 is here!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
				}
				return redirect('/email')->with('message','hemiAdmins email successfully sent'); 
			case 'hemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, CONCAT(FirstName, ' ', LastName) AS Name, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				dd($users);
				foreach($users as $user)
				{					
					Mail::queue(['emails.hemi2.base','emails.hemi2.Current_Hemi_Store_PlainTextEmail'], ['image' => 'Current_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Hemispheres 2.0 coming soon!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','hemiStore email successfully sent'); 
			case 'expCAHemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N')
				// dd($users);
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Expired_Hemi_Admins_PlainTextEmail'], ['image' => 'Expired_CAHemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Canadian Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'cahemi2');

			        });
			    }
				return redirect('/email')->with('message','expHemiAdmins email successfully sent'); 
			case 'expHemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, CONCAT(FirstName, ' ', LastName) AS Name, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Expired_Hemi_Store_PlainTextEmail'], ['image' => 'Expired_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','expHemiStore email successfully sent'); 
			case 'nonHemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N')
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Non_Hemi_Admins_PlainTextEmail'], ['image' => 'Non_Hemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Stroke Education from Apex Innovations, Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
			    }
				return redirect('/email')->with('message','nonHemiAdmins email successfully sent'); 
			case 'nonHemiStore':
				$users = DB::select("SELECT DISTINCT(U.Login), U.Name FROM (SELECT ID, Login, CONCAT(FirstName, ' ', LastName) AS Name FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				foreach($users as $user)
				{	
				// SELECT DISTINCT(U.Login) FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT U.ID FROM (SELECT ID, Login FROM Users WHERE DepartmentID = 765 ) AS U INNER JOIN LicenseSeats AS LS ON LS.UserID = U.ID INNER JOIN LicensePeriods AS LP ON LP.ID = LS.LicensePeriodID INNER JOIN Licenses AS L ON L.ID = LP.LicenseID WHERE ProductID = 2 ) AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
					Mail::queue(['emails.hemi2.base','emails.hemi2.Non_Hemi_Store_PlainTextEmail'], ['image' => 'Non_Hemi_Store'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Stroke Education from Apex Innovations, Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
					error_log('win');
			    }
				return redirect('/email')->with('message','nonHemiStore email successfully sent'); 
			case 'canadianHemi':
				$users = DB::select("SELECT U.Login, CONCAT(U.Firstname, ' ' , U.LastName) AS Name FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 6 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				 // dd($users);
				foreach($users as $user)
				{	
						 
					Mail::queue('emails.hemi2.canadianHemi',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Canadian Stroke Education from Apex Innovations, Canadian Hemispheres 2.0 COMING SOON!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
				}
				return redirect('/email')->with('message','canadianHemi email successfully sent'); 
			default:
				return redirect('/email')->with('errors','No email or invalid email specified');
		} 
	}	
}
