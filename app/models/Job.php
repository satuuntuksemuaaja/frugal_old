<?php

/**
 * @property mixed id
 * @property static closed_on
 * @property mixed contract_date
 * @property int locked
 * @property mixed quote_id
 * @property int schedules_sent
 * @property mixed quote
 */
class Job extends Eloquent
{
    public function getDates()
    {
        return ['created_at', 'updated_at', 'schedule_sent_on'];
    }

    public function pos()
    {
        return $this->hasMany('Po');
    }

    public function buildnotes()
    {
        return $this->hasMany('BuildupNote', 'job_id');
    }

    public function orders()
    {
        return $this->hasMany('ChangeOrder');
    }

    public function schedules()
    {
        return $this->hasMany('JobSchedule');
    }

    public function items()
    {
        return $this->hasMany('JobItem');
    }

    public function fft()
    {
        return $this->hasOne('FFT');
    }

    public function punches()
    {
        return $this->hasMany('Punch');
    }

    public function quote()
    {
        return $this->belongsTo('Quote');
    }

    public function tasks()
    {
        return $this->hasMany('Task');
    }

    public function authorization()
    {
        return $this->hasOne('Authorization');
    }

    /**
     * A job has many payouts.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * A job has many notes
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(JobNote::class);
    }
}