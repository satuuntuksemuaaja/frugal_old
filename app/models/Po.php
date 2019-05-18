<?php
class Po extends Eloquent
{
  public function dates()
  {
    return ['created_at', 'updated_at', 'submitted'];
  }


  public function customer()
  {
    return $this->belongsTo('Customer');
  }

  public function vendor()
  {
    return $this->belongsTo('Vendor');
  }

  public function user()
  {
    return $this->belongsTo('User');
  }

  public function items()
  {
    return $this->hasMany('PoItem');
  }
  public function job()
  {
    return $this->belongsTo('Job');
  }

  public function children()
  {
      return $this->hasMany('Po', 'parent_id');
  }
}