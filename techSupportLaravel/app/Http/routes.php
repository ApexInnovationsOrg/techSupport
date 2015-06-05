<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');
Route::get('home', 'HomeController@index');
Route::get('stats', 'HomeController@stats');
Route::get('payOut','HomeController@payOut');
Route::post('pay','HomeController@pay');

Route::get('parse', 'WebAPI@parse');
Route::get('testParse', 'WebAPI@testParse');
Route::get('iamnotspam', 'WebAPI@iamnotspam');

Route::get('startTicket', 'TechSupportController@startTicket');
Route::get('showTicket', 'TechSupportController@showTicket');
Route::post('updateNotes','TechSupportController@updateNotes');
Route::any('completedTicket','TechSupportController@completedTicket');
Route::post('claimBounty','TechSupportController@claimBounty');
Route::post('archiveTicket','TechSupportController@archiveTicket');
Route::post('transferTicket','TechSupportController@transferTicket');
Route::post('unclaimTicket','TechSupportController@unclaimTicket');
Route::post('taunt','TechSupportController@taunt');
Route::get('userJSON','TechSupportController@userJSON');


Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
