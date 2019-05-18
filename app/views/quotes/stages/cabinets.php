<?php
$pass = true;
echo BS::title("Cabinets", $quote->lead->customer->name."'s ".$quote->type);

$headers = ['Description', 'Cabinets', 'List Price', 'Color', 'In. off Floor',  'Remove'];
$rows = [];
$cabinets = Cabinet::orderBy('name', 'ASC')->get();
foreach ($cabinets AS $cabinet)
{
	$allCabinets[] = ['value' => $cabinet->id, 'text' => $cabinet->frugal_name];
}

foreach ($quote->cabinets AS $cabinet)
{

	$cab = ($cabinet->cabinet) ? $cabinet->cabinet->frugal_name : "Select Cabinet";
	if ($cab == 'Select Cabinet')
	{
		$pass = false;
	}

	$color = ($cabinet->color) ? $cabinet->color : "No Color";
	$colorPulse = ($cabinet->cabinet && $cabinet->cabinet->vendor->colors &&  ! $cabinet->color) ? "class='pulse-red'" : null;
	if ($colorPulse)
	{
		$pass = false;
	}

	$inches = ($cabinet->inches) ? $cabinet->inches : "On floor";
	$price  = $cabinet->price;
	if ( ! $price)
	{
		$pass = false;
	}

	// now make them all x-editable
    if ($cabinet->customer_removed) $cabinet->description .= "<br/><small>** Cabinet being removed by customer! ** </small>";
	$pulse = ( ! $cabinet->cabinet) ? "class='pulse-red'" : null;
	$rows[] = [
		$cabinet->description,
		"<a {$pulse} href='/quote/$quote->id/cabinet/$cabinet->id/edit'>$cab</a>",
		$price,
		"<span {$colorPulse}>$color</span>",
		$inches,
		"<a class='get' href='/quote/$quote->id/cabinet/$cabinet->id/remove'><i class='fa fa-trash-o'></i></a>"];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$add   = Button::init()->text("Add Cabinet")->color('primary')->modal('cabModal')->icon('plus')->render();
if ($quote->cabinets()->count() > 0)
{
	if ($quote->type == 'Cabinet Only')
	{
		$add .= null;
	}
	else if ($quote->type == 'Cabinet and Install' || $quote->type == 'Builder')
	{
		$add .= Button::init()->text("Next")->color('info')->icon('arrow-right')->url("/quote/$quote->id/accessories")->render();
	}
	else
	if ($pass)
	{

		$add .= Button::init()->text("Next")->color('info')->icon('arrow-right')->url("/quote/$quote->id/granite")->render();
	}

}
$add .= Button::init()->text("Quote Overview")->color('info')->icon('share')
                      ->url("/quote/$quote->id/view")->render();
$message = BS::callout('info', "Need to redo a cabinet or XML? Simply click the trash can and delete the appropriate cabinet and
  click Add Cabinet to redo the cabinet entry.");
$span = BS::span(6, $message.$table.$add);

// Are we editing a cabinet?
if (isset($selectedCabinet))
{
	$fields   = [];
	$opts     = [];
	$cabinets = Cabinet::orderBy('frugal_name', 'ASC')->get();
	foreach ($cabinets AS $cabinet)
	{
		$opts[] = ['val' => $cabinet->id, 'text' => $cabinet->frugal_name];
	}

	if ($selectedCabinet->cabinet_id)
	{
		array_unshift($opts, ['val' => $selectedCabinet->cabinet_id, 'text' => Cabinet::find($selectedCabinet->cabinet_id)->frugal_name]);
	}
	else
	{
		array_unshift($opts, ['val' => null, 'text' => '-- Select Cabinet --']);
	}

	$fields[] = ['type' => 'select2', 'var' => 'cabinet_id', 'text' => 'Select Cabinet:', 'opts' => $opts];
	$fields[] = ['type' => 'input', 'var' => 'price', 'val' => $selectedCabinet->price, 'text' => 'List Price:'];
	$fields[] = ['type' => 'input', 'var' => 'inches', 'val' => $selectedCabinet->inches, 'text' => 'In. off Floor:'];
	$fields[] = ['type' => 'input', 'var' => 'description', 'val' => $selectedCabinet->description, 'text' => 'Description:', 'span' => 7];
    $fields[] = ['type' => 'select2', 'var' => 'customer_removed', 'text' => 'Customer Removing Cabinets?', 'opts' => [['val' => 0, 'text' => 'No'], ['val' => '1', 'text' => 'Yes']]];

	// If we don't know what kind of cabinet we can't determine color yet.
	if ($selectedCabinet->cabinet_id && $selectedCabinet->cabinet->vendor->colors)
	{
		$colors = explode("\n", $selectedCabinet->cabinet->vendor->colors);
		$opts   = [];
		foreach ($colors AS $color)
		{
			$opts[] = ['val' => $color, 'text' => $color];
		}

		if ($selectedCabinet->color)
		{
			array_unshift($opts, ['val' => $selectedCabinet->color, 'text' => $selectedCabinet->color]);
		}
		else
		{
			array_unshift($opts, ['val' => null, 'text' => '-- Select Color --']);
		}

		$fields[] = ['type' => 'select2', 'var' => 'color', 'text' => 'Select Color:', 'opts' => $opts];
	}
	if ($quote->type == 'Cabinet Only')
	{
		$opts = [];
		if ($selectedCabinet->location)
		{
			$opts[] = ['val' => $selectedCabinet->location, 'text' => $selectedCabinet->location];
		}

		$opts[] = ['val' => 'North', 'text' => 'North'];
		$opts[] = ['val' => 'South', 'text' => 'South'];
		$fields[] = ['type' => 'select', 'var' => 'location', 'opts' => $opts, 'text' => 'Delivery Location:'];
		$checked = ($selectedCabinet->measure) ? true : false;
		$opts    = [];
		$opts[] = ['val' => 'Y', 'text' => 'Did you do a field measure?', 'checked' => $checked];
		$fields[] = ['type' => 'checkbox', 'var' => 'measure', 'opts' => $opts, 'span' => 6];
		$opts = [];
		$opts[] = ['val' => 'Curbside Delivery', 'text' => 'Curbside Delivery'];
		$opts[] = ['val' => 'Custom Delivery', 'text' => 'Custom Delivery'];
		if ($selectedCabinet->delivery)
		{
			array_unshift($opts, ['val' => $selectedCabinet->delivery, 'text' => $selectedCabinet->delivery]);
		}

		$fields[] = ['type' => 'select', 'text' => 'Delivery Option:', 'opts' => $opts, 'span' => 6, 'var' => 'delivery'];

	}
	$wood = isset($selectedCabinet) && $selectedCabinet->cabinet && $selectedCabinet->cabinet->vendor && $selectedCabinet->cabinet->vendor->wood_products
	? Button::init()->text("Upload Wood Products XML")->color('primary')->modal('woodModal')->icon('exclamation')->render()
	: null;
	if ($selectedCabinet->wood_xml)
	{
		$wood .= "<h4>Wood Products XML Found!</h4>";
	}

	$save = Button::init()->text("Save")->color('danger post btn-block')->formid('editCabinetForm')
	                      ->icon('save')->withoutGroup()->render();
	$form = Forms::init()->id('editCabinetForm')->labelSpan(4)->url("/quote/$quote->id/cabinet/$selectedCabinet->id/edit")
	                     ->elements($fields)->render();

	$panel = Panel::init('primary')->header("Edit Cabinet")->content($form.$wood.$save)->render();
	$span .= BS::span(6, $panel);
}

echo BS::row($span);

// Add Cabinet Modal.
$pre = BS::callout('info', "You are adding a cabinet to this quote. There are no longer primary and secondary cabinets.
  This system now supports unlimited cabinet orders.");
$fields = [];
$fields[] = ['type' => 'file', 'var' => 'xml', 'text' => 'Pro Kitchens XML:', 'span' => 6];
$fields[] = ['type' => 'input', 'var' => 'name', 'text' => 'Cabinet Name:', 'comment' => 'Only if there is no XML file.',
	'span' => 6];
$fields[] = ['type' => 'textarea', 'var' => 'list', 'text' => 'Cabinet List:', 'comment' => 'Only if there is no XML file.',
	'span' => 6];
$fields[] = ['type' => 'submit', 'var' => 'submit', 'val' => 'Upload', 'class' => 'btn-primary'];
$form = Forms::init()->id('cabinetForm')->elements($fields)->url("/quote/$quote->id/cabinets/new")->render();
echo Modal::init()->id('cabModal')->header("Add Cabinet to Quote")->content($pre.$form)->render();

// Upload Wood Products XML
if (isset($selectedCabinet) && $selectedCabinet->cabinet && $selectedCabinet->cabinet->vendor && $selectedCabinet->cabinet->vendor->wood_products)
{
	$pre = "<h5>You are adding a secondary XML that is going to be processed as wood products. The XML you upload here should
  not be cabinets. If you are looking to replace the XML for this cabinet, then delete the cabinet and start over.</h5>";
	$fields = [];
	$fields[] = ['type' => 'file', 'var' => 'wood_xml', 'text' => 'Wood Products XML', 'span' => 6];
	$fields[] = ['type' => 'submit', 'var' => 'submit', 'val' => 'Upload', 'class' => 'btn-primary'];
	$form = Forms::init()->id('woodForm')->elements($fields)->url("/quote/$quote->id/cabinet/$selectedCabinet->id/wood")->render();
	echo Modal::init()->id('woodModal')->header("Add Wood Products")->content($pre.$form)->render();
}