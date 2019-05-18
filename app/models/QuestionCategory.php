<?php
class QuestionCategory extends Eloquent
{
  public $table = "question_categories";

  public function questions()
  {
    return $this->hasMany('Question');
  }
}