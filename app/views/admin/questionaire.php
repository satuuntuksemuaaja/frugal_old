<?php
$headers = ['Question', 'Gives Money To', 'Stage', 'Category'];
$rows = [];
foreach (Question::whereActive(true)->get() AS $question)
{
    if ($question->stage == 'B')
    {
        $stage = "Both";
    }
    else
    {
        if ($question->stage == 'F')
        {
            $stage = 'Final';
        }
        else
        {
            $stage = 'Initial';
        }
    }

    $color = ($question->contract) ? 'color-info ' : null;
    $rows[] = ["{$color}<a href='/admin/questionaire/$question->id'>$question->question</a>",
        ($question->designation) ? $question->designation->name : "None",
        $stage,
        ($question->category) ? $question->category->name : "Unassigned",
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);

$question = (isset($id)) ? Question::find($id) : new Question;
$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'question', 'text' => 'Question:', 'val' => $question->question, 'span' => 7];
$opts = [];
foreach (Designation::all() as $designation)
{
    $opts[] = ['val' => $designation->id, 'text' => $designation->name];
}

$opts[] = ['val' => 0, 'text' => 'None (Frugal)'];
if ($question->designation_id)
{
    array_unshift($opts, ['val' => $question->designation_id, 'text' => $question->designation->name]);
}
else
{
    array_unshift($opts, ['val' => 0, 'text' => 'None (Frugal)']);
}

$fields[] = ['type' => 'select', 'text' => 'Money goes To', 'opts' => $opts, 'span' => 7, 'var' => 'designation_id'];

$opts = [];
$opts[] = ['val' => 'numbers', 'text' => 'numbers'];
$opts[] = ['val' => 'yes/no', 'text' => 'yes/no'];
if ($question->response_type)
{
    array_unshift($opts, ['val' => $question->response_type, 'text' => $question->response_type]);
}
else
{
    array_unshift($opts, ['val' => $question->response_type, 'text' => $question->response_type]);
}

$fields[] = ['type' => 'select', 'text' => 'Response Type?', 'opts' => $opts, 'span' => 7, 'var' => 'response_type'];

$opts = [];
$opts[] = ['val' => 'B', 'text' => 'Both'];
$opts[] = ['val' => 'I', 'text' => 'Initial'];
$opts[] = ['val' => 'F', 'text' => 'Final'];
if ($question->stage)
{
    array_unshift($opts, ['val' => $question->stage, 'text' => $question->stage]);
}
else
{
    array_unshift($opts, ['val' => null, 'text' => null]);
}

$fields[] = ['type' => 'select', 'text' => 'Ask in Stage', 'opts' => $opts, 'span' => 7, 'var' => 'stage'];

$opts = [];
foreach (QuestionCategory::all() as $cat)
{
    $opts[] = ['val' => $cat->id, 'text' => $cat->name];
}

if ($question->question_category_id)
{
    array_unshift($opts, ['val' => $question->question_category_id, 'text' => $question->category->name]);
}
else
{
    array_unshift($opts, ['val' => 0, 'text' => '-- Select Category -- ']);
}

$fields[] = ['type' => 'select', 'var' => 'category_id', 'opts' => $opts, 'text' => 'Category:', 'span' => 7];

// #119 - Add Vendor Specific Questions
$opts = [];
$opts[] = ['val' => 0, 'text' => 'No Vendor Association (All)'];
foreach (Vendor::orderBy('name', 'ASC')->get() as $vendor)
{
    $opts[] = ['val' => $vendor->id, 'text' => $vendor->name];
}

if ($question->vendor_id)
{
    if ($question->vendor_id == 999)
    {
        $v = "All Vendors";
    }
    else
    {
        $v = Vendor::find($question->vendor_id)->name;
    }

    array_unshift($opts, ['val' => $question->vendor_id, 'text' => $v]);
}
$fields[] = ['type' => 'select', 'var' => 'vendor_id', 'opts' => $opts, 'text' => 'Vendor Association:', 'span' => 7];

$opts = [];
$opts[] = ['val' => 'Y', 'text' => 'Ask on Contract?', 'checked' => ($question->contract) ? true : false];
$fields[] = ['type' => 'checkbox', 'var' => 'contract', 'opts' => $opts, 'span' => 7];
$fields[] = [
    'type' => 'textarea',
    'var'  => 'contract_format',
    'text' => 'Contract Format:',
    'val'  => $question->contract_format, 'span' => 7];

$opts = [['val' => 'Y', 'text' => 'Only for Small Job?', 'checked' => ($question->small_job) ? true : false]];
$fields[] = ['type' => 'checkbox', 'var' => 'small_job', 'opts' => $opts, 'span' => 7];

$opts = [['val' => 'Y', 'text' => 'Include on Build-up Checklist?', 'checked' => ($question->on_checklist) ? true : false]];
$fields[] = ['type' => 'checkbox', 'var' => 'on_checklist', 'opts' => $opts, 'span' => 7];

$opts = [['val' => 'Y', 'text' => 'Include on Item List in Job Board?', 'checked' => ($question->on_job_board) ? true : false]];
$fields[] = ['type' => 'checkbox', 'var' => 'on_job_board', 'opts' => $opts, 'span' => 7];

$title = ($question->id) ? "Edit Question $question->id" : "New Question";
$url = ($question->id) ? "/admin/questionaire/$question->id" : "/admin/questionaire";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $question->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/questionaire/$question->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();

// Now for a condition to add.
$fields = [];
if (!$question->condition)
{
    $panel .= Panel::init('danger')->header("Condition")->content("Question has not been created yet.")->footer(null)->render();
}
else
{
    $fields[] = ['type' => 'input', 'var' => 'answer', 'text' => 'Answer?', 'val' => $question->condition->answer, 'span' => 7];
    $opts = [];
    $opts[] = ['val' => $question->condition->operand, 'text' => $question->condition->operand];
    $opts[] = ['val' => 'Add', 'text' => 'Add'];
    $opts[] = ['val' => 'Subtract', 'text' => 'Subtract'];
    $fields[] = ['type' => 'select', 'var' => 'operand', 'text' => 'Add or Sub?', 'opts' => $opts, 'span' => 7];
    $fields[] = ['type' => 'input', 'var' => 'amount', 'text' => 'Amount:', 'val' => $question->condition->amount, 'span' => 7];
    $opts = [];
    $opts[] = ['val' => 'Y', 'text' => 'Apply amount once?', 'checked' => ($question->condition->once) ? true : false];
    $fields[] = ['type' => 'checkbox', 'var' => 'once', 'opts' => $opts, 'span' => 7];

    $title = "Update Condition";
    $url = "/admin/questionaire/$question->id/condition/update";
    $form = Forms::init()->id('editCForm')->url($url)->labelSpan(4)->elements($fields)->render();
    $save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editCForm')->render();
    $panel .= Panel::init('primary')->header($title)->content($form)->footer($save)->render();

} // has condition

$span .= BS::span(4, $panel);
echo BS::row($span);