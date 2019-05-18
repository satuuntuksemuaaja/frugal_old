<?php

class PayoutItem extends Eloquent
{
    protected $guarded = [];

    /**
     * A payout item has a payout parent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    /**
     * What contractor does this relate to?
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}