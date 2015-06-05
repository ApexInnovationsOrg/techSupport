<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Employees';
	public $timestamps = false;
	protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['FirstName', 'LastName', 'Email','TechSupport','PhoneNumber'];

}
