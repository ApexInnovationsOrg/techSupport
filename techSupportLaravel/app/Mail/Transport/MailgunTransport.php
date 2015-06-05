<?php namespace App\Mail\Transport;

use Swift_Transport;
use Swift_Mime_Message;
use GuzzleHttp\Post\PostFile;
use Swift_Events_EventListener;

class MailgunTransport extends \Illuminate\Mail\Transport\MailgunTransport {

	/**
	 * {@inheritdoc}
	 */

	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$client = $this->getHttpClient();
		return $client->post($this->url, ['auth' => ['api', $this->key],
			'body' => [
				'to' => $this->getTo($message),
				'message' => new PostFile('message', (string) $message),
				'o:native-send' => 'true'
			]
		]);
	}
}