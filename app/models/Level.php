<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;
class Level extends Eloquent
{
  use SoftDeletingTrait;
  protected $dates = ['deleted_at'];

  public function newQuery()
  {
    return parent::newQuery()->whereActive(true);
  }
  public function users()
  {
    return $this->hasMany('User');
  }

}

