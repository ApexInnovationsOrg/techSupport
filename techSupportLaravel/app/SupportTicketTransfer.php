<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicketTransfer extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportTicketTransfers';
    protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'SupportTicketID', 
		'TransferFromEmployeeID', 
		'TransferToEmployeeID', 
		'TransferReason'
	];

}
