<?php namespace App\Http\Controllers;
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    // Ignores notices and reports all other kinds... and warnings
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    // error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
}
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\EmailParser;
use App\SupportTicket;
use App\Employee;


use Illuminate\Http\Request as HttpRequest;

class WebAPI extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function parse()
	{
		return EmailParser::parse();
	}

	public function testParse()
	{	
		return EmailParser::testParse();
	}

	public function iamnotspam(HttpRequest $request)
	{
		$supportTicket = SupportTicket::where('Key',$request->key)->first();
		if($supportTicket !== null && $supportTicket->Validated !== 'Y' && $supportTicket->EmployeeID === null)
		{
			$supportTicket->Validated = 'Y';
			EmailParser::emailTechSupport($supportTicket->CodeName, $supportTicket->Key, $supportTicket->PhoneNumber, 'Email Ticket');
			$supportTicket->save();
		}
		return view('validated');
	}

}
