<?php namespace App\Http\Controllers;
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    // Ignores notices and reports all other kinds... and warnings
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    // error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
}
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
use App\SupportReplyEmail;

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


class TechSupportController extends Controller {
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
		//
	}

	public function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
	    $datetime1 = date_create($date_1);
	    $datetime2 = date_create($date_2);
	    
	    $interval = date_diff($datetime1, $datetime2);
	    if($differenceFormat === '%i Minute(s) %s Seconds')
	    {
	    	$differenceFormat = ($interval->i === 0 ? '' : $interval->i > 1 ? '%i Minutes' : '%i Minute') . ' %s Seconds';
	    }
	    return $interval->format($differenceFormat);
	    
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function startTicket()
	{

		$user = Auth::user();
		$key = Input::get('key');
		$userName = $user->FirstName . ' ' . $user->LastName;
		
		$startedTicket = SupportTicket::where('Key','=',$key)->first();
		if(!empty($startedTicket))
		{
			if($startedTicket->EmployeeID !== $user->ID && $startedTicket->EmployeeID !== null)
			{
				$owner = Employee::where('ID','=',$startedTicket->EmployeeID)->first();
				$ownerName = $owner->FirstName . ' ' . $owner->LastName;
				return $this->showTicket()->withErrors(["<strong>Hogwash!</strong> $owner->FirstName beat you by: " . $this->dateDifference(date("Y-m-d H:i:s"),$startedTicket->Started,'%i Minute(s) %s Seconds'),"<a href='#' data-toggle='modal' data-target='#tauntModal'>Click here to send $owner->FirstName a taunt</a>"]);
			}
			else
			{
				if($startedTicket->EmployeeID == null)
				{
					$startedTicket->EmployeeID = $user->ID;
					$startedTicket->Started = date("Y-m-d H:i:s");

					EmailParser::emailTicketStarted($startedTicket->CodeName,$userName,$startedTicket->Started,$startedTicket->Validated);
					
					$startedTicket->Validated = 'Y';
					$startedTicket->save();
				}

				return $this->showTicket();
			}

		}
		else 
		{
			return redirect('/')->withErrors('Ticket doesn\'t exist');	
		}

	}
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showTicket()
	{
		$loggedInUser = Auth::user();
		$key = old('key') !== null ? old('key') : Input::get('key');
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
		$ownerName = "Not Claimed";
		if($owner)
		{
			$ownerName = $owner->FirstName . ' ' . $owner->LastName;
		}
		$notOwner = true;
		if($ticket->EmployeeID === $loggedInUser->ID)
		{
			$notOwner = null;
		}
		$supportTypes = SupportTypes::all();
		$user = Users::where('ID','=',$ticket->UserID)->first();
		$techSupportEmployees = Employee::where('TechSupport','=','Y')->orderBy('FirstName')->get();
		$transferHistory = DB::select(DB::raw("SELECT S1.ID, S1.created_at, S1.SupportTicketID, S1.TransferFrom, S1.TransferFromEmployeeID, CONCAT(E2.FirstName, ' ', E2.LastName) AS TransferTo, S1.TransferToEmployeeID, S1.TransferReason FROM (SELECT STT.ID, STT.created_at, STT.SupportTicketID, CONCAT(E.FirstName, ' ', E.LastName) AS TransferFrom, STT.TransferFromEmployeeID, STT.TransferToEmployeeID, STT.TransferReason FROM SupportTicketTransfers AS STT LEFT JOIN Employees AS E ON E.ID = STT.TransferFromEmployeeID ) AS S1 LEFT JOIN Employees AS E2 ON E2.ID = S1.TransferToEmployeeID WHERE SupportTicketID = :ticketID ORDER BY S1.created_at"),array('ticketID'=>$ticket->ID));

		if(!empty($user))
		{
			$ticket->formattedUserName = $user->LastName . ', ' . $user->FirstName . ' :: ' . $user->Login;
		} 
		$supportReplyEmail = SupportReplyEmail::where('SupportTicketID','=',$ticket->ID)->first();
		$replyEmail = 0;
		if($supportReplyEmail)
		{
			$replyEmail = $supportReplyEmail->ID;
		}		
		
		return view('ticket',[
			'ticket'=>$ticket,
			'notOwner'=>$notOwner,
			'ownerName'=>$ownerName, 
			'supportTypes'=>$supportTypes,
			'techSupportEmployees'=>$techSupportEmployees,
			'transferHistory'=>$transferHistory,
			'supportReplyEmail'=>$replyEmail
		]);
	}
	
	/**
	 * Display the specified resource.
	 *
	 */
	public function replyToTicket(HttpRequest $request)
	{
		$key = old('key') !== null ? old('key') : Input::get('key');
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
		$ownerName = "Not Claimed";
		if($owner)
		{
			$ownerName = $owner->FirstName . ' ' . $owner->LastName;
		}
				
		return view('replyToTicket',[
			'ticket'=>$ticket,
			'ownerName'=>$ownerName
		]);
	}
	
	/**
	 * Display the specified resource.
	 *
	 */
	public function sendReplyToTicket(HttpRequest $request)
	{
		$formType = $request->formAction;		
		
		if($formType == 'send'){			
			
			$emailReply = $request->formValue;
			$key = old('key') !== null ? old('key') : Input::get('key');
			$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
			$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
			
			$supportReplyEmail = new SupportReplyEmail;
			$supportReplyEmail->SupportTicketID = $ticket->ID;
			$supportReplyEmail->ReplyEmail = $emailReply;
			$supportReplyEmail->save();
			
			Mail::queue('emails.ticketReply', ['ticketEmail' => $ticket->EmailMessage,'replyEmail' => $emailReply,'replyAdminName' => $owner->FirstName . " " . $owner->LastName], function($message) use ($ticket,$owner)  
			// Mail::send('emails.ticketReply', ['ticketEmail' => $ticket->EmailMessage,'replyEmail' => $emailReply,'replyAdminName' => $owner->FirstName . " " . $owner->LastName], function($message) use ($ticket,$owner)  
			{
				$message->from($owner->Email,'Tech Support')->to($ticket->EmailAddress,'User')->bcc($owner->Email)->subject('ATTN: Tech Support');
			});			
			
			return $this->showTicket()->withMessages(["Email sent!"]);			
		}elseif($formType == 'cancel'){			
			return $this->showTicket();
		}else{			
			return $this->showTicket()->withErrors(["Issue when sending reply email."]);
		}
		
	}

	public function updateNotes()
	{
		$ticket = SupportTicket::where('Key','=',Input::get('key'))->firstOrFail();
		$updatedNote =  Input::get('note');
		$ticket->Notes = $updatedNote;
		$ticket->save();
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function completedTicket(HttpRequest $request)
	{
		$loggedInUser = Auth::user();

		// $key = old('key') !== null ? old('key') : Input::get('key');
		$key = $request->input('key',old('key'));

		// dd(Session::all());
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		// dd($ticket,$key,$request);

		if($ticket->Completed !== null)
		{
			return redirect()->back()->withInput()->withErrors(['Already Completed'=>'The ticket has already been completed']);
		}
		if($ticket->EmployeeID !== $loggedInUser->ID)
		{
			return redirect()->back()->withInput()->withErrors(['Not Yours'=>'You cannot complete a ticket that is not yours.']);	
		}

		$ticket->Completed = date("Y-m-d H:i:s");
		$ticket->save();
		$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
		$userName = $owner->FirstName . ' ' . $owner->LastName;
		EmailParser::ticketCompleted($ticket->CodeName,$userName,$ticket->Started);
		return $this->showTicket();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function userJSON()
	{
		$searchTerm = urldecode(Input::get('search'));
		if (($pos = strpos($searchTerm, ", ")) !== false) {
			// Form of "LastName, FirstName"
			$users = Users::where('FirstName', 'LIKE', '%' . substr($searchTerm, $pos + 2) . '%')->where('LastName', 'LIKE', '%' . substr($searchTerm, 0, $pos) . '%')->orderBy('LastName','ASC')->get();
		} else if (($pos = strpos($searchTerm, " ")) !== false) {
			// Form of "FirstName LastName"
			$users = Users::where('FirstName', 'LIKE', '%' . substr($searchTerm, 0, $pos) . '%')->where('LastName', 'LIKE', '%' . substr($searchTerm, $pos + 1) . '%')->orderBy('LastName','ASC')->get();
		} else {
			// If it's not of that form, just submit it in bulk
			$users = Users::where('FirstName', 'LIKE', '%' . $searchTerm . '%')->orWhere('LastName', 'LIKE', '%' . $searchTerm . '%')->orWhere('Login', 'LIKE', '%' . $searchTerm . '%')->orderBy('LastName','ASC')->get();
		}


		$response = [];
		foreach($users as $user)
		{
			$response[] = [
				'ID'=>$user->ID,
				'value'=>$user->LastName . ', ' . $user->FirstName . ' :: ' .$user->Login
			];
		}
		return response()->json($response);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function archiveTicket()
	{
		$ticket = SupportTicket::where('Key','=',Input::get('key'))->firstOrFail();
		$ticket->Archived = 'Y';
		$ticket->save();
		return json_encode(['success'=>true]);
	}
	public function unclaimTicket(HttpRequest $request)
	{
				
		$user = Auth::user();
		$v = Validator::make($request->all(), [
			'unclaimReason' => 'required|min:4',
			'key' => 'required'
		]);

		$key = Input::get('key');
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		if($ticket->Completed !== null)
		{
			return redirect()->back()->withInput()->withErrors(['Already completed'=>'Completed Tickets cannot be unclaimed']);
		}
		if($ticket->EmployeeID !== $user->ID)
		{
			return redirect()->back()->withInput()->withErrors(['Not yours'=>'You cannot unclaim someone else\'s ticket.']);
		}

		if($v->fails())
		{

			return redirect()->back()->withInput()->withErrors($v->errors());
		}
		else
		{
			
			$ticket->EmployeeID = null;
			$ticket->Started = null;
			$ticket->save();

			$transfer = new SupportTicketTransfer;
			$transfer->SupportTicketID = $ticket->ID;
			$transfer->TransferFromEmployeeID = $user->ID;
			$transfer->TransferToEmployeeID = null;
			$transfer->TransferReason = Input::get('unclaimReason');
			$transfer->save();
			EmailParser::emailTicketUnclaimed($ticket->CodeName,$user->FirstName . ' ' . $user->LastName,date("Y-m-d H:i:s"),$transfer->TransferReason,$key);
			return redirect('/')->with('message','Successfully unclaimed ticket, slacker.');
		}	
	}

	public function transferTicket(HttpRequest $request)
	{
		
		$user = Auth::user();
		$v = Validator::make($request->all(), [
			'transferID' => 'required|numeric', 
			'transferReason' => 'required|min:4',
			'key' => 'required'
		]);

		$key = Input::get('key');
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		if($ticket->Completed !== null)
		{
			return redirect()->back()->withInput()->withErrors(['Already completed'=>'Completed Tickets cannot be transferred']);
		}
		if($ticket->EmployeeID !== $user->ID)
		{
			return redirect()->back()->withInput()->withErrors(['Not yours'=>'You cannot transfer someone else\'s ticket.']);
		}

		if($v->fails())
		{
			return redirect()->back()->withInput()->withErrors($v->errors());
		}
		else
		{
			$ticket->EmployeeID = Input::get('transferID');
			$ticket->save();

			$transfer = new SupportTicketTransfer;
			$transfer->SupportTicketID = $ticket->ID;
			$transfer->TransferFromEmployeeID = $user->ID;
			$transfer->TransferToEmployeeID = Input::get('transferID');
			$transfer->TransferReason = Input::get('transferReason');
			$transfer->save();

			$employee = Employee::where('ID', '=',Input::get('transferID'))->first();

			EmailParser::emailTicketTransferred($employee,$ticket->CodeName,$user->FirstName . ' ' . $user->LastName,date("Y-m-d H:i:s"),$transfer->TransferReason,$key);
			return redirect('/')->with('message','Transferred Ticket to ' . $employee->FirstName);
		}	
	}
	public function taunt(HttpRequest $request)
	{
		$key = Input::get('key');
		$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
		$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
		EmailParser::taunt($owner,$request->taunt);
		return redirect('/')->with('message','Taunt sent to ' . $owner->FirstName);
	}
	public function claimBounty(HttpRequest $request)
	{


		$v = Validator::make($request->all(), [
			'resolutionSelect' => 'required', 
			'solvedRadio' => 'required',
			'timeInput' => 'required|numeric'
		]);

		if($v->fails())
		{
			return redirect('/showTicket?key=' . $request->input('key'))->withInput()->withErrors($v->errors());
		}
		else
		{
			// dd($request);
			$key = $request->input('key',old('key'));
			$ticket = SupportTicket::where('Key','=',$key)->firstOrFail();
			$owner = Employee::where('ID', '=',$ticket->EmployeeID)->first();
			$ticket->UserID = Input::get('userID');
			$ticket->ResolutionNotes = Input::get('resolutionNotes');
			$ticket->SupportTypeID = Input::get('resolutionSelect');
			$ticket->LengthOfCall = Input::get('timeInput');
			$ticket->Solved = Input::get('solvedRadio');
			$ticket->BountyClaimed = 'Y';
			$ticket->save();
			return redirect('/');
		}	
	}
}
