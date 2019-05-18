<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Accessory extends Eloquent
{
  use SoftDeletingTrait;
  protected $dates = ['deleted_at'];

  public function vendor()
  {
    return $this->belongsTo('Vendor');
  }


}