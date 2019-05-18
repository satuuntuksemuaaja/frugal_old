<?php
echo BS::title("LED and Tile", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

// LED
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'How many 12" LED Strip Lights are needed?:', 'var' => 'quote_led_12',
  'span' => 3, 'val' => (isset($meta['quote_led_12'])) ? $meta['quote_led_12'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many 60" LED Strip Lights are needed?:', 'var' => 'quote_led_60',
  'span' => 3, 'val' => (isset($meta['quote_led_60'])) ? $meta['quote_led_60'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many transformers are needed (if cabinets are not connected together
  and there is no attic above or unfinished basement below there is 1 transformer per separate location).',
'var' => 'quote_led_transformers', 'span' => 3,
'val' => (isset($meta['quote_led_transformers'])) ? $meta['quote_led_transformers'] : null];

$fields[] = ['type' => 'input', 'text' => 'How many connections are needed for LED strip lights?:',
'var' => 'quote_led_connections', 'span' => 3,
'val' => (isset($meta['quote_led_connections'])) ? $meta['quote_led_connections'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many couplers are needed?', 'var' => 'quote_led_couplers', 'span' => 3,
'val' => (isset($meta['quote_led_couplers'])) ? $meta['quote_led_couplers'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many switches need to be added?', 'var' => 'quote_led_switches',
'span' => 3, 'val' => (isset($meta['quote_led_switches'])) ? $meta['quote_led_switches'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many feet of LED strip light is being installed?',
'var' => 'quote_led_feet', 'span' => 3, 'val' => (isset($meta['quote_led_feet'])) ? $meta['quote_led_feet'] : null];
$fields[] = ['type' => 'input', 'text' => 'How many Puck Lights?',
             'var' => 'quote_puck_lights', 'span' => 3, 'val' => (isset($meta['quote_puck_lights'])) ? $meta['quote_puck_lights'] : null];

$fields[] = ['type' => 'hidden', 'var' => 'led', 'val' => 'Y'];
$form = Forms::init()->id('ledForm')->labelSpan(6)->url("/quote/$quote->id/led")->elements($fields)->render();
$save = "<center>".
    Button::init()->text("Save LED Information")->color('danger post pulse-red')->formid('ledForm')->icon('save')->render() .
    "</center>";
$panel = Panel::init('primary')->header("LED Requirements")->content($form)->footer($save)->render();
$left = BS::span(6, $panel);

// --------- Tile Manager
$request = App::make('request');
// Start with a table.
$headers = ['Description', 'Counter', 'BS', 'Pattern', 'Sealed'];
$rows = [];
foreach ($quote->tiles as $tile)
{
    $delete = "<span class='pull-right'><a class='get' href='/quote/$quote->id/tile/$tile->id/delete'><i class='fa fa-trash-o'></i></a></span>";
    $rows[] = [
      "<a href='/quote/$quote->id/led?tile=$tile->id'>$tile->description</a> $delete",
      $tile->linear_feet_counter,
      $tile->backsplash_height,
      $tile->pattern,
      $tile->sealed
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
if ($request->has('tile'))
    $tile = QuoteTile::find($request->get('tile'));
else $tile = new QuoteTile;


$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Description:', 'var' => 'description', 'span' => 6,
             'val' => $tile->description];

$fields[] = ['type' => 'input', 'text' => 'Linear Feet of Counter?:', 'var' => 'linear_feet_counter', 'span' => 3,
  'val' => $tile->linear_feet_counter];
$fields[] = ['type' => 'input', 'text' => 'How tall is backsplash?:', 'var' => 'backsplash_height', 'span' => 3,
'val' => $tile->backsplash_height];
$opts = [];
if ($tile->pattern)
$opts[] = ['val' => $tile->pattern, 'text' => $tile->pattern];
$opts[] = ['val' => 'Straight', 'text' => 'Straight'];
$opts[] = ['val' => 'Pattern', 'text' => 'Pattern'];
$fields[] = ['type' => 'select', 'text' => 'Straight or Pattern?:', 'var' => 'pattern', 'span' => 3, 'opts' => $opts];

$opts = [];
if ($tile->sealed)
  $opts[] = ['val' => $tile->sealed, 'text' => $tile->sealed];
$opts[] = ['val' => 'Yes', 'text' => 'Yes'];
$opts[] = ['val' => 'No', 'text' => 'No'];
$fields[] = ['type' => 'select', 'text' => 'Will tile be sealed?:', 'var' => 'sealed', 'span' => 3, 'opts' => $opts];
$fields[] = ['type' => 'hidden', 'var' => 'tile', 'val' => 'Y'];
$fields[] = ['type' => 'hidden', 'var' => 'tile_id', 'val' => $tile->id];
$form = Forms::init()->id('tileForm')->labelSpan(6)->url("/quote/$quote->id/led")->elements($fields)->render();
$save = "<center>".
    Button::init()->text("Save Tile Information")->color('danger post pulse-red')->formid('tileForm')->icon('save')->render() .
    Button::init()->text("Clear Tile Form")->color('info')->url("/quote/$quote->id/led")->icon('reload')->render().
    "</center>";
$panel = Panel::init('info')->header("Tile Requirements")->content($form)->footer($save)->render();
$right = BS::span(6, $table .$panel);



echo BS::row($left.$right);


$pass = true;
if (!isset($meta['progress_led']) || !isset($meta['progress_tile'])) $pass = false;
$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review Questions")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
  ->url("/quote/$quote->id/questionaire")->render();
$options .= Button::init()->text("Add Addons")->color('primary btn-lg')->withoutGroup()->icon('arrow-right')
    ->url("/quote/$quote->id/addons")->render();

if ($pass)
$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
  ->url("/quote/$quote->id/view")->render();
$options .= "</div>";


echo BS::row(BS::span(9, $options, 3));
