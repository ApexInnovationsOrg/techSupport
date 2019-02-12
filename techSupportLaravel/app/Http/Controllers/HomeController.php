<?php namespace App\Http\Controllers;


use Auth;
use DB;
use Session;
use App\Users;
use App\Employee;
use App\SupportTicket;
use App\SupportTicketPayout;
use App\SupportNotices;
use Illuminate\Http\Request as HttpRequest;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

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
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$User = Auth::user();
		$Tickets = SupportTicket::where('EmployeeID', '=', $User->ID)
		->where(function($query)
		{
			$query->where('Completed', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 MONTH)'))
				  ->orWhere('Completed','=',null);
		})
		->where('Paid','N')
		->orderBy('Completed', 'ASC')
		->get();


		$UnclaimedTickets = SupportTicket::where('EmployeeID','=',null)->get();
		$bounty = $this->calculateBounty();
		$a_json = array();
		$a_json_row = array();
		foreach($UnclaimedTickets as $ut)
		{
			$buttonColor = $ut->Validated === 'Y' ? 'btn-primary' : 'btn-warning';
			$a_json_row["codeName"] = (string)$ut->CodeName;
			$a_json_row["creationDate"] = (string)$ut->created_at;
			$a_json_row["claimTicket"] = "<a href=" . url("startTicket?key=$ut->Key") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn $buttonColor\">Claim Ticket</button></a>";
			array_push($a_json, $a_json_row);
		}
		$UnclaimedTicketsFormatted = json_encode($a_json);
		
		$response = [];
		foreach($Tickets as $Ticket)
		{

			if($Ticket->Completed && $Ticket->BountyClaimed == "N")
			{
				$formattedShowTicket = "<span style='display:none'>1</span><a href=" . url("showTicket?key=$Ticket->Key") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn btn-success\">Claim Bounty</button></a>";
			}
			else if($Ticket->Completed && $Ticket->BountyClaimed == "Y")
			{
				$formattedShowTicket = "<span style='display:none'>2</span><a href=" . url("showTicket?key=$Ticket->Key") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn btn-default\">Completed</button></a>";
			}
			else
			{
				$formattedShowTicket = "<span style='display:none'>3</span><a href=" . url("showTicket?key=$Ticket->Key") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn btn-primary\">Show Ticket</button></a>";
			}
			$response[] = [
				'codeName'=>"<span style='display:none'>" . (string)$Ticket->CodeName . "</span><a href=" . url("showTicket?key=$Ticket->Key&details=true") .">" . (string)$Ticket->CodeName . "</a>",
				'claimDate'=>(string)$Ticket->Started,
				'completed'=>(string)$Ticket->Completed,
				'bountyClaimed'=> $Ticket->BountyClaimed == "Y" ? "<span class='glyphicon glyphicon-ok' style='text-align:center;display:block'></span>" : '',
				'ticket'=>$formattedShowTicket
			];

		}
		$formattedTickets = json_encode($response);

		return view('home',['tickets'=>$formattedTickets,'unclaimedTickets'=>$UnclaimedTicketsFormatted,'bounty'=>$bounty]);
	}

	public function calculateBounty($key = null, $forTicket = false)
	{

		if(!$forTicket)
		{
			$User = Auth::user();
			$Bounties = DB::table('SupportTickets')
			->where('EmployeeID', '=', $User->ID)
			->where('BountyClaimed', '=', 'Y')
			->where('Paid','N')
			->join('SupportTypes', 'SupportTypes.ID', '=', 'SupportTickets.SupportTypeID')
			->get();
			$totalBounty = 0;
			foreach($Bounties as $Bounty)
			{
				$totalBounty += $this->calculation($Bounty);
			}
			return ['TotalBounty'=>$totalBounty,'BountiesClaimed'=>count($Bounties)];
		} 
		else
		{
			$Bounty = DB::table('SupportTickets')
			->where('Key', '=', $key)
			->join('SupportTypes', 'SupportTypes.ID', '=', 'SupportTickets.SupportTypeID')
			->first();
			return $this->calculation($Bounty);
		}

		
	}
	private function calculation($Bounty)
	{
		$extraTime = 0;
		if($Bounty->LengthOfCall > 10)
		{
			$extraTime = ($Bounty->LengthOfCall - 10)/10 * $Bounty->TimeMultiplier;
		}
		return $Bounty->BaseRate + $extraTime;
	}

	public function stats()
	{
		$LoggedInUser = Auth::user();
		$admin = false;

		if($LoggedInUser->AdminTechSupport == 'Y')
		{
			$admin = true;
			$Bounties = DB::table('SupportTickets')
			->where('BountyClaimed', '=', 'Y')
			->join('SupportTypes', 'SupportTypes.ID', '=', 'SupportTickets.SupportTypeID')
			->join('Employees','SupportTickets.EmployeeID', '=', 'Employees.ID')
			->get();


			$response = [];
			foreach($Bounties as $bounty)
			{
				$response[] = [
					'codeName'=>"<span style='display:none'>" . (string)$bounty->CodeName . "</span><a href=" . url("showTicket?key=$bounty->Key&details=true") .">" . (string)$bounty->CodeName . "</a>",
					'completed'=>(string)$bounty->Completed,
					'bountyClaimed'=> '$' . $this->calculateBounty($bounty->Key,true),
					'bountyClaimedInteger'=> (int)$this->calculateBounty($bounty->Key,true),
					'employee' => $bounty->LastName . ', ' . $bounty->FirstName,
					'showTicket'=>"<a href=" . url("showTicket?key=$bounty->Key") ."><button name=\"singlebutton\" class=\"btn btn-primary\">Show Ticket</button></a>"
				];

			}
			$formattedBounties = json_encode($response);


			$response2 = [];
			$Tickets = DB::table('SupportTickets')
			->join('Employees','SupportTickets.EmployeeID', '=', 'Employees.ID')
			->where('Archived','=','N')
			->get();
			foreach($Tickets as $ticket)
			{
				$userName = '';
				if(!empty($ticket->UserID))
				{
					$User = Users::where('ID', '=', $ticket->UserID)->first();
					
					$userName = "<span style='display:none'>" . $User->LastName . ', ' . $User->FirstName . "</span><a href=" . url("../EditUsers.php?ID=$ticket->UserID&details=true") . ">" . $User->LastName . ', ' . $User->FirstName . "</a>";
				}

				$response2[] = [
					'codeName'=>"<span style='display:none'>" . (string)$ticket->CodeName . "</span><a href=" . url("showTicket?key=$ticket->Key") .">" . (string)$ticket->CodeName . "</a>",
					'completed'=>(string)$ticket->Completed,
					'user' => $userName,
					'employee' => $ticket->LastName . ', ' . $ticket->FirstName,
					'showTicket'=>"<a href=" . url("showTicket?key=$ticket->Key") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn btn-primary\">Show Ticket</button></a>",
					'archiveTicket'=>"<button data-key=\"$ticket->Key\" name=\"singlebutton\" class=\"archiveTicket btn btn-danger\">Archive Ticket</button>"
				];
			}
			$formattedTickets = json_encode($response2);

		  	$response3 = [];
			$unclaimedTickets = SupportTicket::where('EmployeeID', '=', null)->get();
			foreach($unclaimedTickets as $ut)
			{
				$response3[] = [
						'codeName'=>"<span style='display:none'>" . (string)$ut->CodeName . "</span><a href=" . url("showTicket?key=$ut->Key") .">" . (string)$ut->CodeName . "</a>",
						'created'=>(string)$ut->created_at,
						'claimTicket'=>"<a href=" . url("startTicket?key=$ut->Key") ."><button name=\"singlebutton\" class=\"btn btn-primary\">Claim Ticket</button></a>"
					];
			}
			$UnclaimedTicketsFormatted = json_encode($response3);
		}	
		else
		/*********** for non admins ***********/
		{
			$Bounties = DB::table('SupportTickets')
			->where('BountyClaimed', '=', 'Y')
			->where('EmployeeID', '=', $LoggedInUser->ID)
			->join('SupportTypes', 'SupportTypes.ID', '=', 'SupportTickets.SupportTypeID')
			->get();


			$response = [];
			foreach($Bounties as $bounty)
			{
				$userName = '';
				if(!empty($bounty->UserID))
				{
					$User = Users::where('ID', '=', $bounty->UserID)->first();
					
					$userName = "<a href=" . url("../EditUsers.php?ID=$bounty->UserID") . ">" . $User->LastName . ', ' . $User->FirstName . "</a>";
				}

				$response[] = [
					'codeName'=>"<span style='display:none'>" . (string)$bounty->CodeName . "</span><a href=" . url("showTicket?key=$bounty->Key&details=true") .">" . (string)$bounty->CodeName . "</a>",
					'completed'=>(string)$bounty->Completed,
					'bountyClaimed'=> '$' . $this->calculateBounty($bounty->Key,true),
					'user' => $userName,
					'showTicket'=>"<a href=" . url("showTicket?key=$bounty->Key") ."><button name=\"singlebutton\" class=\"btn btn-primary\">Show Ticket</button></a>"
				];

			}
			$formattedBounties = json_encode($response);


			$response2 = [];
			$Tickets = DB::table('SupportTickets')
			->join('Employees','SupportTickets.EmployeeID', '=', 'Employees.ID')
			->where('Archived','=','N')
			->where('EmployeeID','=',$LoggedInUser->ID)
			->get();
			$response2 = [];
			foreach($Tickets as $ticket)
			{
				$userName = '';
				if(!empty($ticket->UserID))
				{
					$User = Users::where('ID', '=', $ticket->UserID)->first();
					
					$userName = "<a href=" . url("../EditUsers.php?ID=$ticket->UserID") . ">" . $User->LastName . ', ' . $User->FirstName . "</a>";
				}

				$response2[] = [
					'codeName'=>"<span style='display:none'>" . (string)$ticket->CodeName . "</span><a href=" . url("showTicket?key=$ticket->Key") .">" . (string)$ticket->CodeName . "</a>",
					'completed'=>(string)$ticket->Completed,
					'user' => $userName,
					'showTicket'=>"<a href=" . url("showTicket?key=$ticket->Key&details=true") ."><button id=\"singlebutton\" name=\"singlebutton\" class=\"btn btn-primary\">Show Ticket</button></a>",
				];
			}
			$formattedTickets = json_encode($response2);


			$unclaimedTickets = SupportTicket::where('EmployeeID', '=', null)->get();
			$response3 = [];
			foreach($unclaimedTickets as $ut)
			{
				$response3[] = [
						'codeName'=>"<span style='display:none'>" . (string)$ut->CodeName . "</span><a href=" . url("showTicket?key=$ut->Key") .">" . (string)$ut->CodeName . "</a>",
						'created'=>(string)$ut->created_at,
						'claimTicket'=>"<a href=" . url("startTicket?key=$ut->Key") ."><button name=\"singlebutton\" class=\"btn btn-primary\">Claim Ticket</button></a>"
					];
			}
			$UnclaimedTicketsFormatted = json_encode($response3);
		}

		return view('stats',['admin'=>$admin,'tickets'=>$formattedTickets,'unclaimedTickets'=>$UnclaimedTicketsFormatted,'bounty'=>$formattedBounties]);
	}	
	public function payOut()
	{
		$payOut = DB::select(DB::raw("SELECT CONCAT(E.FirstName, ' ', E.LastName) AS Person, E.ID AS 'EmployeeID', COUNT(*) AS 'TotalTickets', AVG(LengthOfCall) AS 'AverageTime', SUM(LengthOfCall) AS 'TotalTime', SUM(IF(LengthOfCall > 10, BaseRate + ((LengthOfCall - 10)/10 * TimeMultiplier), BaseRate) ) AS PayOut FROM SupportTickets AS ST JOIN Employees AS E on ST.EmployeeID = E.ID JOIN SupportTypes AS STT ON STT.ID = ST.SupportTypeID WHERE BountyClaimed = 'Y' AND Paid = 'N' GROUP BY E.ID"),array());

		$payOutArr = [];
			foreach($payOut as $pay)
			{
				$payOutArr[] = [
						'person'=>$pay->Person,
						'totalTickets'=>$pay->TotalTickets,
						'averageTime'=>$pay->AverageTime,
						'totalTime'=>$pay->TotalTime,
						'payout'=>'$' . $pay->PayOut,
						'pay'=>"<form action=" . url("/pay") ." method=\"POST\"><input type=\"hidden\" name=\"EmployeeID\" value=\"$pay->EmployeeID\"/><input type=\"hidden\" name=\"_token\" value=" . csrf_token() . "><input type=\"hidden\" name=\"amount\" value=\"$pay->PayOut\"/><input type=\"submit\" name=\"singlebutton\" value=\"Pay\" class=\"btn btn-success\"></input></form>"
					];
			}
			$payOutJSON = json_encode($payOutArr);
		return view('payOut',['payOut'=>$payOutJSON]);
	}

	public function pay(HttpRequest $request)
	{
		$employee = Employee::find($request->EmployeeID);
		$user = Auth::user();
		$SupportTicketPayout = SupportTicketPayout::create(array('EmployeeTriggerID'=>$user->ID));
		$tickets = SupportTicket::where('EmployeeID',$request->EmployeeID)->whereNotNull('Completed')->update(['Paid'=>'Y','SupportTicketPayoutID'=>$SupportTicketPayout->ID]);
		
		return redirect('/payOut')->with('message',"Successfully paid $employee->FirstName: \$$request->amount");
	}
	
	public function noticeEditor()
	{
		$supportNotices = SupportNotices::get();
		$notices = array();
		
		foreach($supportNotices AS $supportNotice){
			$notices[] = array('id'=>$supportNotice->ID,'notice'=>$supportNotice->Notice,'employeeID'=>$supportNotice->EmployeeID,'startDate'=>$supportNotice->StartDate,'endDate'=>$supportNotice->EndDate,'active'=>$supportNotice->Active);
		}		
		
		return view('noticeEditor',['notices'=>json_encode($notices)]);
	}
	
	public function noticeEditorSaveAndDelete(HttpRequest $request)
	{				
		$formType = $request->formAction;
		
		if($formType == 'save'){
			$noticeID = $this->noticeEditorSave($request);
			
			if($noticeID > 0){
				return redirect('/noticeEditor')->with('message',"Successfully saved notice")->with('selection',$noticeID);
			}
		}elseif($formType == 'delete'){
			$deleted = $this->noticeEditorDelete($request);
			
			if($deleted){			
				return redirect('/noticeEditor')->with('message',"Notice successfully deleted");
			}
		}	
		
		return redirect('/noticeEditor')->with('message',"Error modifying notice!");
	}
	
	private function noticeEditorSave($request)
	{
		$employee = Auth::user();
		$active = ($request->noticeActive == "on" ? "Y" : "N");
		$startDate = ($request->noticeStartDate != '' ? $request->noticeStartDate : NULL);
		$endDate = ($request->noticeEndDate != '' ? $request->noticeEndDate : NULL);			
		
		$supportNotice = SupportNotices::updateOrCreate(['ID'=>$request->noticeID],['Notice'=>$request->formValue,'StartDate'=>$startDate,'EndDate'=>$endDate,'EmployeeID'=>$employee->ID,'Active'=>$active]);
		
		return $supportNotice->ID;
	}
	
	private function noticeEditorDelete($request)
	{
		$supportNotice = SupportNotices::find($request->noticeID);
		
		return $supportNotice->delete();
	}

}
