<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTypes extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'SupportTypes';
    protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'Name'
	];

}
