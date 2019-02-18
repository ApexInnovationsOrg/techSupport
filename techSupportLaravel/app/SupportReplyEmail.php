<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportReplyEmail extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportReplyEmails';
	protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
					'SupportTicketID',
					'ReplyEmail',
					'created_at',
					'updated_at'];

}
