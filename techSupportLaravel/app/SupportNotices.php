<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportNotices extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportNotices';
	protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
					'Notice',
					'EmployeeID',
					'StartDate',
					'EndDate',
					'Active',
					'created_at',
					'updated_at'];

}
