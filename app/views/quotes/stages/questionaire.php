<?php
function hasThisVendor($quote, $id)
{
	foreach ($quote->cabinets AS $cabinet)
	{
		if ($cabinet->cabinet->vendor_id == $id)
		{
			return true;
		}

	}
	return false;
}
echo BS::title("Questionaire", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

$pass = true;

$headers = ['Question', 'Answer'];
foreach (QuestionCategory::all() AS $category)
{
	$rows[] = ["<b>$category->name</b>", null];
	foreach ($category->questions AS $question)
	{
	    if (!$question->active) continue;
		if ($quote->type == 'Cabinet Small Job' &&  ! $question->small_job)
		{
			continue;
		}

		if ($quote->final && $question->stage == 'I')
		{
			continue;
		}

		if ( ! $quote->final && $question->stage == 'F')
		{
			continue;
		}

		if ($question->vendor_id > 0 &&  ! hasThisVendor($quote, $question->vendor_id))
		{
			continue;
		}
		// don't ask. literally.
		$answer = QuoteAnswer::whereQuoteId($quote->id)->whereQuestionId($question->id)->first();
		$answer = ($answer) ? $answer->answer : null;
		if ( ! $answer && $question->response_type != 'yes/no')
		{
			$pass = false;
		}

		if ($question->response_type == 'yes/no')
		{
			$selected = ($answer == 'Y') ? "<option value='Y'>Yes</option>" : "<option value='N'>No</option>";
			if (!$answer)
			    $selected = "<option value=''>--</option>";
			$rows[] = [$question->question, "
        <select name='question_$question->id'>
        {$selected}
        <option value='Y'>Yes</option>
        <option value='N'>No</option>",
			];
		}
		else
		{
			$rows[] = [$question->question, "<input type='text' name='question_$question->id' value='$answer'>"];
		}

	} // fe question
} // fe cat
$table = "<form id='questionaireForm' method='post' action='/quote/$quote->id/questionaire'><fieldset>
               <div class='questionaireForm_msg'></div>";
$table .= Table::init()->headers($headers)->rows($rows)->render();
$save  = Button::init()->text("Save Questionaire")->color('danger post pulse-red')->formid('questionaireForm')->icon('save')->render();
$panel = Panel::init('info')->header("Questionaire")->content($table)->footer("</fieldset><center>$save</center></form>")->render();

$left = BS::span(10, $panel, 1);
echo BS::row($left);

$pass = true;
if ( ! isset($meta['progress_questionaire']))
{
	$pass = false;
}

$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review Additional Requirements")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
                          ->url("/quote/$quote->id/additional")->render();

if ($pass && $quote->type != 'Cabinet Small Job')
{

	$options .= Button::init()->text("Next")->color('success btn-lg ')->withoutGroup()->icon('arrow-right')
	                          ->url("/quote/$quote->id/led")->render();
}

$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
                          ->url("/quote/$quote->id/view")->render();
$options .= "</div>";

echo BS::row(BS::span(9, $options, 3));
