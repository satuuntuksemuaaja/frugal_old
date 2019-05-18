<?php
class TaskNote extends Eloquent
{
  public function task()
  {
    return $this->belongsTo('Task');
  }

  public function user()
  {
    return $this->belongsTo('User');
  }

}