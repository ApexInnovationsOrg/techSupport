<?php namespace App\Providers;

use Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Queue::failing(function ($connection, $job, $data) {
	        HipchatNotifier::message('Queue failed.',['queue'=>false,'room'=>'the cage','color'=>'red']);
	        HipchatNotifier::message('Job: ' . $job,['queue'=>false,'room'=>'the cage','color'=>'red']);
	        HipchatNotifier::message('Data: ' . $data,['queue'=>false,'room'=>'the cage','color'=>'red']);
        });
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
	}

}
