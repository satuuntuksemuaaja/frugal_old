<?php
// Check bypass
foreach (User::all() as $user)
{
  if (!$user->bypass)
    {
      $user->bypass = md5('4hk1jdn' . rand(0,59212));
      $user->save();
    }
}
$headers = ['Name', 'E-mail', 'Access Level', 'Mobile Link', 'Google'];
$users = User::orderBy('name', 'ASC')->whereActive(true)->get();
$rows = [];
    foreach ($users AS $user)
    {
      $smses = "<a class='mjax' data-target='#workModal' href='/admin/users/$user->id/sms'><i class='fa fa-phone'></i></a>";
      $google = "<a href='/admin/user/$user->id/google/authorize'><i class='fa fa-cloud'></i></a> ";
      $designation = ($user->designation) ? $user->designation->name : "No Designation Defined";
      $level = ($user->level) ? $user->level->name : "No Access Level Defined";
      $rows[] = ["$google $smses <a href='/admin/users/$user->id'><span style='color: #$user->color'>$user->name</span></a>",
      $user->email,
       $level . " ($designation)",
      "http://www.frugalk.com/login/$user->bypass",
      ($user->google) ? "Yes " : "No"
    ];
    }
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);

//Add Edit
$user = (isset($id)) ? User::find($id) : null;
$title = ($user) ? "Edit $user->name" : "Create new User";
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Name:', 'var' => 'name', 'val' => ($user) ? $user->name : null, 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'E-mail:', 'var' => 'email', 'val' => ($user) ? $user->email : null, 'span' => 6];
$fields[] = ['type' => 'password', 'text' => 'Password:', 'var' => 'password', 'span' => 6];
$levels = [];
$accessLevels = Level::all();
if ($user && $user->level)
  $levels[] = ['val' => $user->level->id, 'text' => $user->level->name];
else
  $levels[] = ['val' => null, 'text' => '-- No Level Assigned --'];
foreach ($accessLevels AS $accessLevel)
  $levels[] = ['val' => $accessLevel->id, 'text' => $accessLevel->name];

$fields[] = ['type' => 'select', 'var' => 'level_id', 'opts' => $levels, 'span' => 6, 'text' => 'Level:'];
$designations = Designation::all();
$opts = [];
if ($user && $user->designation)
  $opts[] = ['val' => $user->designation->id, 'text' => $user->designation->name];
else
  $opts[] = ['val' => null, 'text' => '-- No Designation Found --'];
foreach ($designations AS $designation)
  $opts[] = ['val' => $designation->id, 'text' => $designation->name];
$fields[] = ['type' => 'select', 'var' => 'designation_id', 'opts' => $opts, 'span' => 6, 'text' => 'Designation:'];
$fields[] = ['type' => 'input', 'var' => 'color', 'val' => ($user) ? $user->color : null, 'text' => 'Calendar Color:'];
$fields[] = ['type' => 'input', 'var' => 'mobile', 'val' => ($user) ? $user->mobile : null, 'text' => 'Mobile Number:',
'mask' => '999.999.9999', 'span' => 6];
$fields[] = ['type' => 'input', 'var' => 'frugal_number', 'val' => ($user) ? $user->frugal_number : null, 'text' => 'Frugal Number:',
'mask' => '999.999.9999','span' => 6];

$url = ($user) ? "/admin/users/$user->id" : "/admin/users";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
if ($user)
  $delete = Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/users/$user->id/delete")->render();
else $delete = null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);
echo Modal::init()->id('workModal')->onlyConstruct();
echo BS::row($span);
