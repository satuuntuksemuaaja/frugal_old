<?php
class Expiration extends Eloquent
{
  public $table = "status_expirations";

  public function actions()
  {
    return $this->hasMany('Action', 'status_expiration_id');
  }

}