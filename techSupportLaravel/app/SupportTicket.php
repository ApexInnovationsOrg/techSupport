<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportTickets';
    protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'EmailText', 
		'PhoneNumber', 
		'UserID', 
		'EmployeeID', 
		'Key', 
		'Notes', 
		'From', 
		'SupportTypeID', 
		'created_at', 
		'updated_at',
		'Started',
		'Completed',
		'LengthOfCall',
		'ResolutionNotes',
		'Solved',
		'BountyClaimed',
		'Completed',
		'Validated',
		'Paid'
	];

}
