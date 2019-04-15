<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Notifications\SlackNotifier;
use App\Http\Controllers\ScheduledReports as ScheduledReports;


use Log;
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
		
		$schedule->call('App\Http\Controllers\ScheduledReports@runScheduledReports')->everyMinute();
		
		$times = ['09:00','10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
		foreach($times as $time)
		{
	        $schedule->call(function()
			{
			
				$json = file_get_contents('https://api.giphy.com/v1/gifs/trending?api_key=dc6zaTOxFJmzC');
		        $obj = json_decode($json);
		        $gifURL = $obj->data[array_rand($obj->data)]->images->original->url;
		        // SlackNotifier::message('Gif of the hour',env('SLACK_WEBHOOK_CAGE'));

	       		SlackNotifier::message('Gif of the hour' . $gifURL,env('SLACK_WEBHOOK_CAGE'));

			})->weekdays()->at($time);
      	}
	}

}
