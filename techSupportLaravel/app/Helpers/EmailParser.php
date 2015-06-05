<?php namespace App\Helpers;
/**
 * 
 * User: EM
 * Date: 4/27/15
 * Time: 16:10
 */

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\SupportTicket;
use App\Employee;
use App\Animal;
use App\Adjective;
use App\Users;
use Mail;
use Auth;
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;


class EmailParser extends BasicObject {

    protected $info = null;
    protected $EventID = null;
    protected $UserID = null;
    private static $techSupport = 'techSupportList@apexinnovations.com';
    private static $techSupportCell = 'techSupportCell@apexinnovations.com';


    public function __construct($info,$EventID,$UserID)
    {
        $this->info = $info;
        $this->EventID = $EventID;
        $this->UserID = $UserID;
        // if(App::environment('local'))
        // {
        //     dd('hello!');
        //     private static $techSupport = 'eddie@apexinnovations.com';
        //     private static $techSupportCell = '2819140203@vzwpix.com';
        // }
    }
    static public function init()
    {
        if(App::environment() === 'local')
        {
            self::$techSupport = 'eddie@apexinnovations.com';
            self::$techSupportCell = '2819140203@vzwpix.com';
        }
    }
    public function SaveLog()
    {
        $log = new UserLogs;
        $log->info    = $this->info;
        $log->EventID = $this->EventID;
        $log->UserID  = $this->UserID;
        $log->save();
    }
    static public function testParse()
    {
        $codeName = 'test';
        $techSupport = ['2819140203@vzwpix.com','eddie@apexinnovations.com'];
        $techSupportCell = self::$techSupportCell;
        $key = 'test';
               
        // Mail::send('emails.ticketCreated', ['key' => 'test', 'codeName' => 'test'], function($message) use ($codeName, $techSupport) 
        // {
        //     $message->bcc($techSupport, 'Tech Support')->subject('TST: "' . $codeName . '" created');
        // }); 
        // Mail::raw("$codeName created.\n Type: test.\n Claim at " . url('admin/techSupport/startTicket/?key='.$key), function($message) use ($techSupport) 
        // {
        //     $message->bcc($techSupport, 'Tech Support')->subject('Ticket Created');
        // });

        Mail::send('emails.spamCheck',['url'=>'https://devbox.apexinnovations.com/admin/techSupport/iamnotspam'], function($message)
        {
            $message->to('mike@apexinnovations.com')->subject('Read me please!');
        });

    }   
    static public function parse()
    {
        $mailbox = new ImapMailbox('{mail.apexinnovations.com:993/imap/ssl}INBOX', 'apex\techSupport', '2dZSUGqc1tPCXYXP4yzk','../voicemails');

        $mails = array();
        $mailsIds = $mailbox->searchMailBox('ALL');
        //dd($mailsIds);
        if(!$mailsIds) {
            die('Mailbox is empty');
        }
        $mailsInfo = $mailbox->getMailsInfo($mailsIds);
        
        foreach($mailsInfo as $message)
        {
            $isSpam = true;
            if(property_exists($message,'subject'))
            {
                $isSpam = EmailParser::isSpam($message->subject);
            } 

            $mail = $mailbox->getMail($message->uid);
            $supportTicket = EmailParser::createTicket($mail,$isSpam,$message,$mailbox);
            
            if(!$isSpam)
            {
                EmailParser::emailTechSupport($supportTicket->CodeName, $supportTicket->Key, $supportTicket->PhoneNumber, EmailParser::ticketType($message->subject));
                if(strpos($message->subject,'From:') === false)
                {
                    EmailParser::automatedReply($mail,$isSpam,$supportTicket->Key);
                }
            }
            else 
            {
                EmailParser::automatedReply($mail,$isSpam,$supportTicket->Key);
            }
        
           $mailbox->deleteMail($message->uid);

        }  
        return 'parsed';
    }

    static public function isSpam($subject = '')
    {
        $status = true;

        if(strpos($subject,'From:') !== false || strpos($subject,'ATTN:') !== false)
        {
            $status = false;
        } 

        return $status;
    }
    static public function createTicket($mail,$isSpam,$message,$mailbox)
    {
                $supportTicket = new SupportTicket;
                $supportTicket->CodeName = EmailParser::nameGenerator();
                $supportTicket->PhoneNumber = preg_replace("/[^0-9]/","",$mail->subject);
                $supportTicket->From = $mail->fromName;
                $supportTicket->EmailAddress = $mail->fromAddress;
                if($supportTicket->PhoneNumber === '' && $mail->fromAddress)
                {
                    $user = Users::where('Login','=',$mail->fromAddress)->first();
                    if(!empty($user))
                    {
                        $supportTicket->UserID = $user->ID;
                    }
                }
                $fileNameParts = $mail->getAttachments();
                $fileNameParts = reset($fileNameParts);
                $fileName = null;
                if(gettype($fileNameParts) === 'object')
                {
                    $fileName = $message->uid . '_' . $fileNameParts->id . '_' . $fileNameParts->name;
                }


                if($mailbox->getMail($message->uid)->textHtml == null)
                {
                    $supportTicket->EmailMessage = $mailbox->getMail($message->uid)->textPlain;
                }
                else
                {
                    $supportTicket->EmailMessage = $mailbox->getMail($message->uid)->textHtml;
                }
                $supportTicket->Key = str_random(40);
                $supportTicket->VoicemailFileName = $fileName;
                if($isSpam) $supportTicket->Validated = 'N';
                $supportTicket->save();
                return $supportTicket;
    }

    static public function ticketType($subject)
    {
        if(strpos($subject,'From:') !== false)
        {
            return 'Voicemail Ticket';
        } 
        else
        {
            return 'Email Ticket';
        }
    }

    static public function automatedReply($mail,$spam,$key)
    {
        if($spam)
        {
          
            $url = url('/admin/techSupport/iamnotspam/?key=' . $key);
            Mail::send('emails.spamCheck',['url'=>$url], function($message) use ($mail)
            {
                $message->to($mail->fromAddress, $mail->fromName)->subject('Read me please!');
            });
        }
        else
        {

            Mail::queue('emails.thanks',[], function($message) use ($mail)
            {
                 $message->to($mail->fromAddress, $mail->fromName)->subject('Thanks!');
            });
        }
    }   
    static public function emailTechSupport($codeName, $key, $phone, $type)
    {

        $techSupport = self::$techSupport;   
        $url = 'https://apexinnovations.com/admin/techSupport/admin/techSupport/startTicket/?key=' . $key;
        // dd($url);
        Mail::queue('emails.ticketCreated', ['codeName' => $codeName, 'url' => $url], function($message) use ($codeName, $techSupport,$phone) 
        {
            $message->bcc($techSupport, 'Tech Support')->subject('TST: "' . $codeName . '" created');
        });

          $techSupportCell = self::$techSupportCell;

        
        $textMessage = "$codeName created. Type: $type. Claim at " . $url;
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($techSupportCell) 
        {
            $message->bcc($techSupportCell, 'Tech Support')->subject('Ticket Created');
        });
    }

    static public function emailTicketStarted($codeName,$name,$startTime,$validated = 'Y')
    {
        $techSupport = self::$techSupport; 
        $techSupportCell = self::$techSupportCell;

        Mail::queue('emails.ticketStarted', ['employeeName' => $name, 'codeName'=>$codeName, 'started' => $startTime], function($message) use ($techSupport, $name, $codeName, $validated) 
        {
            $message->bcc($techSupport, 'Tech Support')->subject($validated === 'N' ? 'TST: (Possible spam) ' . $name . ' has claimed "' . $codeName . '"' : 'TST: ' . $name . ' has claimed "' . $codeName . '"');
        });


        $textMessage = "Too slow! $codeName already started by $name";
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($techSupportCell,$validated) 
        {
            $message->bcc($techSupportCell, 'Tech Support')->subject($validated === 'N' ? 'Spam ticket claimed' : 'Ticket claimed');
        });
    }

    static public function emailTicketUnclaimed($codeName,$name,$time,$reason,$key)
    {
        $techSupport = self::$techSupport;      
        Mail::queue('emails.ticketUnclaimed', ['employeeName' => $name, 'codeName'=>$codeName, 'time' => $time, 'reason' => $reason, 'key'=>$key], function($message) use ($techSupport, $name, $codeName) 
        {
            $message->bcc($techSupport, 'Tech Support')->subject('TST: ' . $name . ' has UNclaimed "' . $codeName . '"');
        });

        $techSupportCell = self::$techSupportCell;
        $textMessage = "$name unclaimed $codeName. What a slacker. Reason: $reason";
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($techSupportCell) 
        {
            $message->bcc($techSupportCell, 'Tech Support')->subject('Ticket UNclaimed');
        });
    }

    static public function emailTicketTransferred($employeeTransferredTo,$codeName,$name,$time,$reason,$key)
    {
        $employeeEmail = $employeeTransferredTo->Email;
        $employeeCellPhone = $employeeTransferredTo->CellPhone;
        $employeeName = $employeeTransferredTo->FirstName . ' ' . $employeeTransferredTo->LastName;

        Mail::queue('emails.ticketTransferred', ['transferTo'=> $employeeTransferredTo, 'transferredFrom' => $name, 'codeName'=>$codeName, 'time' => $time, 'reason' => $reason, 'key'=>$key], function($message) use ($employeeEmail, $employeeName, $name, $codeName) 
        {
            $message->bcc($employeeEmail, $employeeName)->subject('TST: ' . $name . ' has transferred "' . $codeName . '" to you');
        });

         $textMessage = "$name transferred $codeName to you. Reason: $reason :: https://apexinnovations.com/admin/techSupport/showTicket?key=" . $key; 
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($employeeCellPhone) 
        {
            $message->bcc($employeeCellPhone, 'Tech Support')->subject('Ticket Transferred');
        });
    }



    static public function ticketCompleted($codeName,$name,$completed)
    {

        $techSupport = self::$techSupport;
        Mail::queue('emails.ticketCompleted', ['employeeName' => $name, 'codeName'=>$codeName, 'completed' => $completed], function($message) use ($techSupport, $name, $codeName) 
        {
            $message->bcc($techSupport, 'Tech Support')->subject('TST: ' . $name . ' has resolved "' . $codeName . '!"');
        });

        $techSupportCell = self::$techSupportCell;
        $textMessage = "Bam! $name resolved $codeName!";
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($techSupportCell) 
        {
            $message->bcc($techSupportCell, 'Tech Support')->subject('Ticket completed');
        });
    }

    static public function nameGenerator()
    {
        $adjective = Adjective::orderByRaw("RAND()")->first();
        $animal = Animal::orderByRaw("RAND()")->first();
        return ucwords($adjective->Adjective . ' ' . $animal->Animal);
    }

    static public function taunt($owner,$taunt)
    {
        $taunter = Auth::user();
        $textMessage = "Dear $owner->FirstName, \n " . $taunt . " \n Love, \n " . $taunter->FirstName;
        Mail::queue('emails.blank',['textMessage' => $textMessage], function($message) use ($owner) 
        {
            $message->to($owner->CellPhone, $owner->FirstName . ' ' . $owner->LastName)->subject('Taunt Received');
        });
    }
}
EmailParser::init();//making a quasi constructor to set where the emails get sent based on the env