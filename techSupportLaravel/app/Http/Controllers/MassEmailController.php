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
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login LIKE 'eddie@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
						 
					Mail::send('emails.hemi2.reminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Hemispheres 2.0 is here!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
				}
				return redirect('/email')->with('message','Test email successfully sent'); 
			case 'hemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Current_Hemi_Admins_PlainTextEmail'], ['image' => 'Current_Hemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Hemispheres 2.0 coming soon!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

			        });
				}
				return redirect('/email')->with('message','hemiAdmins email successfully sent'); 
			case 'hemiAdminsReminder':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// dd($users);
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N') 
				foreach($users as $user)
				{	
					Mail::queue('emails.hemi2.reminderEmail',['name'=>$user->Name], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('Hemispheres 2.0 is here!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

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
			case 'expHemiAdmins':
				$users = DB::select("SELECT CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE SendNewsletter <> 'N')");
				// SELECT U.Login FROM Users AS U WHERE U.ID IN (SELECT OA.UserID FROM OrganizationAdmins AS OA WHERE OrganizationID IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.EndDate < NOW() AND L.OrganizationID <> 2 AND L.OrganizationID NOT IN (SELECT DISTINCT(L.OrganizationID) FROM Licenses AS L INNER JOIN LicensePeriods AS LP ON LP.LicenseID = L.ID WHERE L.ProductID = 2 AND LP.StartDate < NOW() AND LP.EndDate > NOW() AND L.OrganizationID <> 2 ) ) ) AND U.Login REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'AND U.Login NOT LIKE '%@apex.com'AND U.Login NOT LIKE '%@apexinnovations.com'AND U.ID NOT IN (SELECT UserID FROM UserWebsitePreferences WHERE queueNewsletter <> 'N')
				dd($users);
				foreach($users as $user)
				{	
					Mail::queue(['emails.hemi2.base','emails.hemi2.Expired_Hemi_Admins_PlainTextEmail'], ['image' => 'Expired_Hemi_Admins'], function($message) use ($user)
			        {
			            $message->to($user->Login, $user->Name);
			            $message->subject('NEW Hemispheres 2.0, Just Released!');
			            $message->from('info@apexinnovations.com', 'Your friends at Apex Innovations');
			            $message->getHeaders()->addTextHeader('X-Mailgun-Campaign-Id', 'fnyhc');

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
