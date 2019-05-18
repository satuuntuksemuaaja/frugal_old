<?php

/**
 * @property  price
 * @property  inches
 * @property  name
 * @property  cabinet_id
 * @property  color
 * @property  measure
 * @property  location
 * @property  override
 * @property  data
 * @property mixed quote_id
 */
class QuoteCabinet extends Eloquent
{
  public function cabinet()
  {
    return $this->belongsTo('Cabinet');
  }

  public function quote()
  {
    return $this->belongsTo('Quote');
  }


}