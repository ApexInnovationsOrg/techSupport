<?php namespace App\Mail\Transport;

use Swift_Transport;
use Swift_Mime_Message;
use GuzzleHttp\Post\PostFile;
use Swift_Events_EventListener;
use GuzzleHttp\ClientInterface;

class MailgunTransport extends \Illuminate\Mail\Transport\MailgunTransport {

	/**
	 * {@inheritdoc}
	 */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $options = ['auth' => ['api', $this->key]];

        if (version_compare(ClientInterface::VERSION, '6') === 1) {
            $options['multipart'] = [
                ['name' => 'to', 'contents' => $this->getTo($message)],
                ['name' => 'message', 'contents' => (string) $message, 'filename' => 'message.mime'],
            ];
        } else {
            $options['body'] = [
                'to' => $this->getTo($message),
                'message' => new PostFile('message', (string) $message),
				'o:native-send' => 'true'
            ];
        }

        return $this->client->post($this->url, $options);
    }
}