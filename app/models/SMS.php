<?php
class SMS extends Eloquent
{
  public $table = "smses";

  public function user()
  {
    return $this->belongsTo('User');
  }

}