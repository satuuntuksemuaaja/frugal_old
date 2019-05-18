<?php
class Notification extends Eloquent
{
  public function status()
  {
    return $this->belongsTo('Status');
  }

  public function expiration()
  {
    return $this->belongsTo('Expiration', 'status_expiration_id');
  }



}