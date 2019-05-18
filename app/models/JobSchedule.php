<?php

/**
 * @property mixed job
 * @property mixed user
 * @property string notes
 * @property mixed  default_email
 * @property mixed  designation_id
 * @property mixed  end
 * @property mixed  start
 * @property mixed  aux
 * @property mixed  id
 */
class JobSchedule extends Eloquent
{

  public function getDates()
  {
    return ['created_at', 'updated_at', 'start', 'end'];
  }

  public function job()
  {
    return $this->belongsTo('Job');
  }

  public function designation()
  {
    return $this->belongsTo('Designation');
  }

  public function user()
  {
    return $this->belongsTo('User');
  }


}