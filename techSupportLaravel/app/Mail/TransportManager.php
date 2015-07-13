<?php namespace App\Mail;


use App\Mail\Transport\MailgunTransport;
use GuzzleHttp\Client as HttpClient;

class TransportManager extends \Illuminate\Mail\TransportManager {


	/**
	 * Create an instance of the Mailgun Swift Transport driver.
	 *
	 * @return \Illuminate\Mail\Transport\MailgunTransport
	 */
	protected function createMailgunDriver()
	{
		$client = new HttpClient;
		$config = $this->app['config']->get('services.mailgun', []);
		return new MailgunTransport($client, $config['secret'], $config['domain']);
	}

}
