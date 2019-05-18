<?php
class Question extends Eloquent
{
  public function condition()
  {
    return $this->hasOne('Condition');
  }

  public function designation()
  {
    return $this->belongsTo('Designation');
  }

  public function category()
  {
    return $this->belongsTo('QuestionCategory', 'question_category_id');
  }

}