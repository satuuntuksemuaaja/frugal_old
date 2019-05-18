<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 9/26/15
 * Time: 12:04 PM
 */
echo BS::title("Followups", $lead->customer->name);
$back = Button::init()->url("/leads")->text("Back to Leads")->color('primary')->icon('arrow-left')->render();
$headers = ['#', 'Timestamp', 'Status', 'By', 'Comments'];
$rows = [];
foreach ($lead->followups()->whereClosed(false)->get() AS $f)
{
    $close =  "<span class='pull-right'><a href='/lead/$lead->id/followups/$f->id/close'><i class='fa fa-times-circle-o'></i></a></span>";
    $rows[] = [
      "<a href='/lead/$lead->id/followups/$f->id'>$f->stage</a>",
      $f->updated_at->format("m/d/y h:i a"),
      $f->status ? $f->status->name . $close : "No Status Set",
      $f->user ? $f->user->name : "No Person Assigned",
      $f->comments ?: "None"
    ];
}


$left = Table::init()->headers($headers)->rows($rows)->render();
$left = BS::span(6, $back. $left);

if ($followup && $followup->id)
{
    // Edit Form Info
    $fields = [];
    $opts = [];
    if ($followup->status_id)
        $opts[] = ['val' => $followup->status_id, 'text' => $followup->status->name];
    else
        $opts[] = ['val' => 0, 'text' => '-- Select Status --'];
    foreach (Status::whereFollowupStatus(true)->orderBy('name')->get() AS $s)
        $opts[] = ['val' => $s->id, 'text' => $s->name];

    $fields[] = ['type' => 'textarea', 'text' => 'Comments:', 'var' => 'comments', 'val' => $followup->comments, 'span' => 7];
    if ($followup->status_id)
    {
        $status = Status::find($followup->status_id);
        if (!$status->followup_lock)
            $fields[] = ['type' => 'select', 'var' => 'status_id', 'text' => 'Select Status', 'opts' => $opts, 'span' => 7];
    }
    else
        $fields[] = ['type' => 'select', 'var' => 'status_id', 'text' => 'Select Status', 'opts' => $opts, 'span' => 7];


    $form = Forms::init()->id('editForm')->url("/lead/$lead->id/followups/$followup->id")->labelSpan(4)->elements($fields)->render();
    $save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
    $right = $form.$save;
    $right = BS::span(6, $right);

}
else $right = null;


echo BS::row($left.$right);