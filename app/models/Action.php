<?php
class Action extends Eloquent
{
  public $table = "status_expiration_actions";

  public function designation()
  {
    return $this->belongsTo('Designation');
  }

}