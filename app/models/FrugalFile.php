<?php
class FrugalFile extends Eloquent
{
  public $table = "files";
  public function user()
  {
    return $this->belongsTo('User');
  }
}