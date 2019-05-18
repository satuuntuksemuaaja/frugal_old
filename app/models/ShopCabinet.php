<?php
class ShopCabinet extends Eloquent
{

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function cabinet()
    {
        return $this->belongsTo(QuoteCabinet::class, 'quote_cabinet_id');
    }
}
