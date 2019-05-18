<?php
class LeadSource extends Eloquent
{
  public $table = "sources";

  public function leads()
  {
    return $this->hasMany('Lead', 'source_id');
  }
}