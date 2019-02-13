<?php

namespace App\Http\Controllers;

/**
 * 
 * User: JK
 * Date: 1/17/19
 * Handles Contact Us user ticket creation
 */

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Products;
use App\FrequentlyAskedQuestions;
use App\SupportTicket;
use App\SupportNotices;
use App\Employee;
use App\Animal;
use App\Adjective;
use App\Users;
use Mail;
use Auth;

use App\Notifications\SlackNotifier;

class WebsiteSubmissionController extends Controller
{

	protected $errors = array();
    private static $techSupport = 'techSupportList@apexinnovations.com';
    private static $techSupportCell = 'techSupportCell@apexinnovations.com';
    
	private static $debug = false;
	// private static $debug = true;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitTicket(Request $request)
    {
		$data = $request->json()->all();
        		
		$validatorRequired = Validator::make($request->json()->all(),[
			'contactPreference'	=>	'required',
			'problemOverview'	=>	'required',
			'userName'			=>	'required',
			'description'		=>	'required',
			'contactInfo'		=>	'required',
			'browserInfo'		=>	'required'
		]);
		
		if($validatorRequired->fails()){
			
			foreach($validatorRequired->errors()->all() AS $error){
				$this->errors[] = $error;
			}
			
		}else{
			
			$contactPreference = $data['contactPreference'];
			$problemOverview = $data['problemOverview'];
			$userName = $data['userName'];
			$description= $data['description'];
			$contactInfo = $data['contactInfo'];
			$browserInfo = $data['browserInfo'];
			
			
			switch($contactPreference){
				case 'phone':
					$contactInfo = preg_replace("/[^0-9]/","",$contactInfo);
					$validateAttr = 'numeric';
					break;
				case 'email':
					$validateAttr = 'email';			
					break;
			}
			
			$validator = Validator::make($request->json()->all(),[
				'contactPreference'	=>	'string',
				'problemOverview'	=>	'string',
				'userName'			=>	'string',
				'description'		=>	'string',
				'browserInfo'		=>	'string',
				'contactInfo'		=>	$validateAttr
			]);
			
			if($validator->fails()){
			
				foreach($validator->errors()->all() AS $error){
					$this->errors[] = $error;
				}
			
			}else{
			
				$supportTicket = WebsiteSubmissionController::createTicket($contactInfo,$userName,$description,$problemOverview,$contactPreference,$browserInfo);		
				
				if(!self::$debug){
					WebsiteSubmissionController::emailTechSupport($supportTicket->CodeName, $supportTicket->Key);
					
					if($supportTicket->EmailAddress != NULL){
						WebsiteSubmissionController::emailUserReceipt($supportTicket->EmailAddress,$contactInfo,$userName,$description,$problemOverview);
					}
				}
			}
		}
		
		//if no validation errors
		if(empty($this->errors)){			
			$retval = array('success'=>true);			
		}else{			
			$retval = array('success'=>false,'errors'=>$this->errors);
		}
		
        return json_encode($retval);
    }
	
	/**
     * Return current notices
     */
    public function notices()
    {
		$supportNotices = SupportNotices::where('Active','=','Y')->get();
		$notices = array();
		$currentDate = date('Y-m-d H:i:s');
		
		foreach($supportNotices AS $supportNotice){
			$showNotice = true;
			if(!is_null($supportNotice->EndDate) && $supportNotice->EndDate < $currentDate){
				$showNotice = false;
			}	
			if(!is_null($supportNotice->StartDate) && $supportNotice->StartDate > $currentDate){
				$showNotice = false;
			}
			
			if($showNotice){
				$notices[] = $supportNotice->Notice;
			}
		}		
		
		return json_encode($notices);
	}
	
	/**
     * Return common questions
     */
    public function commonQuestions()
    {
		$commonQuestions = array();
		
		$faqs = FrequentlyAskedQuestions::where('Active','=','Y')->whereIn('ID',array(3,16,17,58))->get();
		foreach($faqs as $faq){
			$commonQuestions[] = array('title'=>htmlentities($faq->Title),'content'=>htmlentities($faq->Content));
		}
		
		return json_encode($commonQuestions);
	}
	
	/**
     * Return product names
     */
    public function products()
    {
		$prodNames = array();
		
		$products = Products::where('Active','=','Y')->whereNotNull('UnitPrice')->select('Name')->orderBy('Name','asc')->get();
		foreach($products as $product){
			$prodNames[] = $product->Name;
		}
		
		return json_encode($prodNames);
	}
	
	static public function emailTechSupport($codeName, $key)
	{
		$techSupport = self::$techSupport;  
		
		$url = 'https://apexinnovations.com/admin/techSupport/admin/techSupport/startTicket/?key=' . $key;
		
		Mail::queue('emails.ticketCreated', ['codeName' => $codeName, 'url' => $url, 'ticketType' => 'Contact Us'], function($message) use ($codeName, $techSupport)  
		// Mail::send('emails.ticketCreated', ['codeName' => $codeName, 'url' => $url, 'ticketType' => 'Contact Us'], function($message) use ($codeName, $techSupport)  
        {
            $message->bcc($techSupport, 'Tech Support')->subject('TST: "' . $codeName . '" created');
        });
		
		$techSupportCell = self::$techSupportCell;

        $type = 'Contact Us';
        $textMessage = "$codeName created. Type: $type. Claim at " . $url;
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($techSupportCell) 
        {
            $message->bcc($techSupportCell, 'Tech Support')->subject('Ticket Created');
            $message->getHeaders()->addTextHeader('X-Mailgun-Native-Send', 'true');
        });
        SlackNotifier::message($textMessage,env('SLACK_WEBHOOK_IT'));		
	}
	
	static public function emailUserReceipt($emailAddress,$contactInfo,$userName,$description,$problemOverview)
	{			
		Mail::queue('emails.ticketReceipt', ['contact'=>$contactInfo,'userName'=>$userName,'description'=>$description,'overview'=>$problemOverview], function($message) use ($emailAddress)  
		// Mail::send('emails.ticketReceipt', ['contact'=>$contactInfo,'userName'=>$userName,'description'=>$description,'overview'=>$problemOverview], function($message) use ($emailAddress)  
        {
            $message->bcc($emailAddress, 'Tech Support')->subject('Receipt: ATTN Tech Support');
        });		
	}
	
	static public function createTicket($contactInfo,$userName,$description,$problemOverview,$contactPreference,$browserInfo)
	{
		$supportTicket = new SupportTicket;
		$supportTicket->CodeName = WebsiteSubmissionController::nameGenerator();
		$supportTicket->Key = str_random(40);
		$supportTicket->From = 'Contact Us';
		
		switch($contactPreference){
			case 'phone':
				$supportTicket->PhoneNumber = $contactInfo;
				break;
			case 'email':
				$supportTicket->EmailAddress = $contactInfo;			
				break;
			default:
				$this->error[] = 'Invalid ticket type';
				break;
		}
		
		$supportTicket->EmailMessage = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> <meta name="Generator" content="Microsoft Word 12 (filtered medium)"> <style><!-- /* Font Definitions */ @font-face{font-family: "Cambria Math"; panose-1: 2 4 5 3 5 4 6 3 2 4;}@font-face{font-family: Calibri; panose-1: 2 15 5 2 2 2 4 3 2 4;}/* Style Definitions */ p.MsoNormal, li.MsoNormal, div.MsoNormal{margin: 0in; margin-bottom: .0001pt; font-size: 12.0pt; font-family: "Times New Roman", "serif";}a:link, span.MsoHyperlink{mso-style-priority: 99; color: blue; text-decoration: underline;}a:visited, span.MsoHyperlinkFollowed{mso-style-priority: 99; color: purple; text-decoration: underline;}p{mso-style-priority: 99; mso-margin-top-alt: auto; margin-right: 0in; mso-margin-bottom-alt: auto; margin-left: 0in; font-size: 12.0pt; font-family: "Times New Roman", "serif";}span.EmailStyle18{mso-style-type: personal-compose; font-family: "Calibri", "sans-serif";}.MsoChpDefault{mso-style-type: export-only; font-size: 10.0pt;}@page WordSection1{size: 8.5in 11.0in; margin: 1.0in 1.0in 1.0in 1.0in;}div.WordSection1{page: WordSection1;}--> </style> </head><body lang="EN-US" link="blue" vlink="purple"> <div class="WordSection1"> <p><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Contact Information:</span>' . $contactInfo . ' <o:p></o:p> </p><p><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Overview:</span>' . $problemOverview .' <o:p></o:p> </p><p><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">User Name:</span>' . $userName . ' <o:p></o:p> </p><p><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Description:</span>' . $description . ' <o:p></o:p> </p><p><span style="font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;">Browser Info:</span>' . $browserInfo . '<o:p></o:p></p></div></body></html>';
		
		if(!self::$debug){
			$supportTicket->save();
		}
		return $supportTicket;
	}
  
    static public function nameGenerator()
    {
        $adjective = Adjective::orderByRaw("RAND()")->first();
        $animal = Animal::orderByRaw("RAND()")->first();
        return ucwords($adjective->Adjective . ' ' . $animal->Animal);
    }
}