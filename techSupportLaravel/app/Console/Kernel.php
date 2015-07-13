<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use HipchatNotifier;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// $schedule->command('inspire')
		// 		 ->hourly();


        $schedule->call(function()
		{
		
			$json = file_get_contents('http://api.giphy.com/v1/gifs/trending?api_key=dc6zaTOxFJmzC');
	        $obj = json_decode($json);
	        HipchatNotifier::message('Gif of the hour',['room'=>'the cage','color'=>'gray','from'=>"GOTH"]);
       		HipchatNotifier::message($obj->data[array_rand($obj->data)]->images->original->url,['room'=>'the cage','color'=>'gray','from'=>"GOTH"]);

		})
		->everyMinute();

      
	}

}
