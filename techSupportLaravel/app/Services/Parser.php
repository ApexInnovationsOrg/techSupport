<?php namespace App\Services;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\SupportTicket;
use App\Users;
use App\Employee;


use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

public function parse()
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

			$isSpam = $this->isSpam($message->subject);
			if(!$isSpam)
			{

				$mail = $mailbox->getMail($message->uid);

				
				$supportTicket = new SupportTicket;
				$supportTicket->CodeName = EmailParser::nameGenerator();
				$supportTicket->PhoneNumber = preg_replace("/[^0-9]/","",$mail->subject);
				$supportTicket->From = $mail->fromName;
				$supportTicket->EmailAddress = $mail->fromAddress;
				if($supportTicket->PhoneNumber === '' && $mail->fromAddress)
				{
					$user = Users::where('Login','=',$mail->fromAddress)->first();
					$supportTicket->UserID = $user->ID;
				}
				$fileNameParts = $mail->getAttachments();
				$fileNameParts = reset($fileNameParts);
				$fileName = null;
				if(gettype($fileNameParts) === 'object')
				{
					$fileName = $message->uid . '_' . $fileNameParts->id . '_' . $fileNameParts->name;
				}

				//dd($fileName);
				
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
				$supportTicket->save();
				//EmailParser::emailTechSupport($supportTicket->CodeName, $supportTicket->Key, $supportTicket->PhoneNumber);
			}
			//$mailbox->deleteMail($message->uid);

	    }  
		return 'parsed';
	}