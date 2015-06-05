<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicketPayout extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportTicketPayouts';
    protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array('EmployeeTriggerID');
}
