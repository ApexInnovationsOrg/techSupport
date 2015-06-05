<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Users';
	public $timestamps = false;
	protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['ID', 'Login', 'FirstName', 'LastName'];

}
