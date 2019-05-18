<?php
use Carbon\Carbon;
use vl\core\Signature;
$auth = $job->authorization;

if (!$auth)
{
    $auth = new Authorization();
    $auth->job_id = $job->id;
    $auth->save();
}
echo BS::title("Customer Job Authorizations", $job->quote->lead->customer->name);
// Items on left, signature on right.
$headers = ['Item', 'Remove'];
$rows = [];

foreach ($auth->items AS $item)
{
        $rows[] = [
            $item->item,
            "<a href='/job/$job->id/authdelete/$item->id'>delete</a>"
        ];
}


$table = Table::init()->headers($headers)->rows($rows)->render();
$add = $auth->signed ? null : Button::init()->text("Add New Item")->color('primary')->modal('newItem')->icon('plus')->render() . "<br/><br/>";
$alerts = null;

if (!$auth->signature)
{
    $buttons[] = Button::init()->text("Send Authorization Request To Customer")->color('success get')->url("/job/$job->id/authsend")
        ->icon('arrow-right')->render();
    $buttons[] = Button::init()->text("Have Customer Sign Now")->color('primary')->url("/job/$job->id/authsign")
        ->icon('edit')->render();
    $alerts .= BS::alert('danger', "Job Authorizations have not been signed", "This authorization has either not yet been sent to the customer
    for approval or signed. If you are done with all items that should be signed off on, please click Send to Customer below.", $buttons);
}
else
{
    if ($auth->signature)
    {
        $button = [Button::init()->text("Remove Signature")->url("/job/$job->id/authremove")->color('danger')->icon('times')->render()];
        $alerts .= BS::alert('success', "Authorizations Signed", "All items have been signed off on by the customer.", $button);
    }
}
$span = BS::span(10, $add . $table . $alerts);
if ($auth->signature)
{
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/job/{$job->id}/authpdf'>pdf here</a>" : null;
    $pre = BS::callout('info', "<b>Signature Found</b> A signature was found for the authorizations requested, and was
    signed by {$job->quote->lead->customer->name} on " . Carbon::parse($auth->signed_on)->format('m/d/y h:i a') . ". ");
    $img = Signature::sigJsonToImage($auth->signature, ['imageSize' => [$w, $h], 'bgColour' => 'transparent']);
    ob_start();
    imagepng($img);
    imagedestroy($img);
    $img = base64_encode(ob_get_clean());
    $pre .= '<div style="width:475;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="data:image/png;base64,' . $img . '" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">' . $job->quote->lead->customer->name . '</p>
            </div>';
    $span .= BS::span(10, $pre);
}

echo BS::row($span);

// Item Modal
$fields = [];
$opts = [];
$opts[] = ['val' => null, 'text' => '-- Select an Authorization Item --'];
foreach (AuthorizationList::whereActive(true)->get() as $auth)
    $opts[] = ['val' => $auth->item, 'text' => $auth->item];

$fields[] = ['type' => 'select2', 'var' => 'auth_id', 'text' => 'Select Authorization:', 'opts' => $opts, 'span' => 6];
$fields[] = ['type' => 'textarea', 'var' => 'description', 'text' => 'Or Enter Manual Item:', 'span' => 6];

$form = Forms::init()->id('newItemForm')->labelSpan(4)->elements($fields)->url("/job/$job->id/auth/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newItemForm')->render();
echo Modal::init()->id('newItem')->header("New Item")->content($form)->footer($save)->render();

