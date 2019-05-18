<?php
class Task extends Eloquent
{
  public function user()
  {
    return $this->belongsTo('User');
  }

  public function assigned()
  {
    return $this->belongsTo('User', 'assigned_id');
  }

  public function customer()
  {
    return $this->belongsTo('Customer');
  }

  public function job()
  {
    return $this->belongsTo('Job');
  }

  public function notes()
  {
    return $this->hasMany('TaskNote');
  }
}