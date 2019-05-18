<?php
class Extra extends Eloquent
{
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }


}