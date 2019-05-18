<?php

/**
 * @property mixed job_id
 */
class FFT extends Eloquent
{
  public $table = "ffts";

  public function getDates()
  {
    return ['created_at', 'updated_at', 'schedule_start', 'pre_schedule_start', 'signed'];
  }
  public function user()
  {
    return $this->belongsTo('User');
  }

  public function thread_notes()
  {
      return $this->hasMany(FFTNote::class, 'fft_id');
  }
  public function assigned()
  {
    return $this->belongsTo('User', 'user_id');
  }
  public function preassigned()
  {
    return $this->belongsTo('User', 'pre_assigned');
  }

  public function job()
  {
    return $this->belongsTo('Job');
  }
  public function customer()
  {
    return $this->belongsTo('Customer');
  }

}