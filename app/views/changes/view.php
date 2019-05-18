<?php
use Carbon\Carbon;
use vl\core\Signature;

echo BS::title("Change Order", $order->job->quote->lead->customer->name);
// Items on left, signature on right.
$headers = ['Item', 'Price', 'Added By', 'Added On', 'Orderable?', 'Ordered On', 'Ordered By'];
$rows = [];
$total = 0;
foreach ($order->items AS $item)
{
    $delete = "<span class='pull-right'><a class='get' href='/orderitem/$item->id/delete'>delete</a></span>";
    $total += $item->price;
    if (!$order->signed)
    {
        if ($item->ordered_on->timestamp > 0)
        {
            $orderedOn = $item->ordered_on->format("m/d/y h:ia");
        }
        else
        {
            if (!$item->orderable)
                $orderedOn = "N/A";
            else $orderedOn = "<a class='btn btn-primary btn-xs get' href='/change/$order->id/orderItem/$item->id'>Click to Order</a>";
        }
        $rows[] = [
            Editable::init()->id("idLo_$item->id")->placement('right')->pk(0)->type('textarea')->title("Item")
                    ->linkText($item->description)->url("/change/$order->id/item/$item->id/description")->render() . $delete,
            Editable::init()->id("idLo_$item->id")->placement('right')->pk(0)->type('text')->title("Price")
                    ->linkText($item->price)->url("/change/$order->id/item/$item->id/price")->render(),
            $item->user->name,
            $item->created_at->format("m/d/y h:i a"),
            Editable::init()->id("idLo_$item->id")->placement('left')->pk(0)->type('select')->title("Part is orderable?")
                    ->linkText($item->orderable ? "Yes" : "No")->url("/change/$order->id/item/$item->id/orderable")->source([
                    ['value' => 1, 'text' => 'Yes'],
                    ['value' => 0, 'text' => 'No']
                ])->render(),
           $orderedOn,
            $item->by ? $item->by->name : "Nobody"
        ];
    }
    else
    {
        $rows[] = [$item->description, $item->price, $item->user->name, $item->created_at->format("m/d/y h:i a")];
    }
}
$rows[] = ["<span class='pull-right'><b>Total:</b>", "$" . number_format($total, 2), null, null, null, null, null];

$table = Table::init()->headers($headers)->rows($rows)->render();
$add = $order->signed ? null : Button::init()->text("Add New Item")->color('primary')->modal('newItem')->icon('plus')->render() . "<br/><br/>";
$alerts = null;

if (!$order->sent || !$order->signed)
{
    $buttons[] = Button::init()->text("Send Change Order To Customer")->color('success get')->url("/change/$order->id/send")
                       ->icon('arrow-right')->render();
    $buttons[] = Button::init()->text("Have Customer Sign Now")->color('primary')->url("/change/$order->id/signature")
                       ->icon('edit')->render();
    $alerts .= BS::alert('danger', "Change order has not been signed", "This change order has either not yet been sent to the customer
    for approval or signed. If you are done with this change order, please click Send to Customer below.", $buttons);
}
else
{
    if ($order->signed)
    {
        $button = [Button::init()->text("Remove Signature")->url("/change/$order->id/signature/remove")->color('danger')->icon('times')->render()];
        $alerts .= BS::alert('success', "Change Order Approved", "This change order has been approved by the customer. If you wish
      to unlock the change order, the signature must be removed to be signed again.", $button);
    }
}
$span = BS::span(10, $add . $table . $alerts);
if ($order->signature)
{
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/change/{$order->id}/signature/pdf'>pdf here</a>" : null;
    $pre = BS::callout('info', "<b>Signature Found</b> A signature was found for this change order, and was
    signed by {$order->job->quote->lead->customer->name} on " . Carbon::parse($order->signed_on)->format('m/d/y h:i a') . ". If
    additional items need to be added, please create a new change order request.");
    $img = Signature::sigJsonToImage($order->signature, ['imageSize' => [$w, $h], 'bgColour' => 'transparent']);
    ob_start();
    try
    {
        imagepng($img);
        imagedestroy($img);
        $img = base64_encode(ob_get_clean());
    } catch (Exception $e)
    {
        $img = null;
    }
    $pre .= '<div style="width:475;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="data:image/png;base64,' . $img . '" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">' . $order->job->quote->lead->customer->name . '</p>
            </div>';
    $span .= BS::span(10, $pre);
}

echo BS::row($span);

// Item Modal
$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'description', 'text' => 'Description:', 'span' => 6];
$fields[] = ['type' => 'input', 'var' => 'price', 'text' => 'Price: (enter in dollars and cents)', 'pre' => "$"];
$fields[] = ['type' => 'select', 'var' => 'orderable', 'text' => 'Needs to be ordered?', 'opts' => [0 => 'No', 1 => 'Yes']];

$form = Forms::init()->id('newItemForm')->labelSpan(4)->elements($fields)->url("/change/$order->id/item/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newItemForm')->render();
echo Modal::init()->id('newItem')->header("New Item")->content($form)->footer($save)->render();

