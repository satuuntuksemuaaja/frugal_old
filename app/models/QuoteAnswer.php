<?php

/**
 * @property mixed quote_id
 */
class QuoteAnswer extends Eloquent
{
  public $table = 'quote_questions';

  public function quote()
  {
    return $this->belongsTo('Quote');
  }

  public function question()
  {
    return $this->belongsTo('Question');
  }

}