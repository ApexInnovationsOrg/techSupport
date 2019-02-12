<?php 

namespace App\Notifications;

class SlackNotifier{
	
	public static function message($message = 'Someone didn\'t set up a message...',$hook){

		  define('SLACK_WEBHOOK', $hook);
// 		  // Make your message
		  $message = json_encode(array('text' => $message));
// 		  // Use curl to send your message
		  $c = curl_init(SLACK_WEBHOOK);
// 		  // curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($c, CURLOPT_POST, true);
		  curl_setopt($c, CURLOPT_POSTFIELDS, $message);
		  curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		  curl_exec($c);

	}
}