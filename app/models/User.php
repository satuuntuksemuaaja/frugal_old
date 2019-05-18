<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	protected $dates = ['deleted_at'];
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

	public function level()
	{
		return $this->belongsTo('Level');
	}

	public function smses()
	{
		return $this->hasMany('SMS');
	}
	public function designation()
	{
		return $this->belongsTo('Designation');
	}
	public function getRememberToken()
	{
	    return $this->remember_token;
	}
	public function leads()
	{
		return $this->hasMany('Lead');
	}

	public function quotes()
	{
		return $this->hasManyThrough('Quote', 'Lead');
	}

	public function setRememberToken($value)
	{
	    $this->remember_token = $value;
	}

	public function getRememberTokenName()
	{
	    return 'remember_token';
	}


}
