<?php namespace App\Mail;


use App\Mail\Transport\MailgunTransport;

class TransportManager extends \Illuminate\Mail\TransportManager {


	/**
	 * Create an instance of the Mailgun Swift Transport driver.
	 *
	 * @return \Illuminate\Mail\Transport\MailgunTransport
	 */
	protected function createMailgunDriver()
	{
		$config = $this->app['config']->get('services.mailgun', array());
		return new MailgunTransport($config['secret'], $config['domain']);
	}

}
