<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class FrequentlyAskedQuestions extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'FrequentlyAskedQuestions';
	protected $primaryKey = 'ID';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['Title','Content','ProductID','Type','Active'];

}