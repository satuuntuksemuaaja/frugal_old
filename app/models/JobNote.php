<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/5/17
 * Time: 2:31 PM
 */
class JobNote extends Eloquent
{
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * A note belongs to a user.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}