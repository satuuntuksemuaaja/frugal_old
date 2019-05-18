<?php
$headers = ['Status', 'Actions'];
$rows = [];
$statuses = Status::orderBy('name', 'ASC')->get();
foreach ($statuses AS $status)
{
    $rows[] = [
        "<a href='/admin/statuses/$status->id'>{$status->name}</a>",
        $status->expirations()->count()
    ];
}

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(6, $table);


$fields = [];
$status = (isset($id)) ? Status::find($id) : new Status;
$fields[] = ['type' => 'textarea', 'text' => 'Name:', 'var' => 'name', 'val' => $status->name, 'span' => 7];
$fields[] = [
    'type' => 'checkbox',
    'text' => '',
    'var'  => 'followup_status',
    'opts' => [
        [
            'val'     => 'Y',
            'text'    => 'This is a follow-up status',
            'checked' => $status->followup_status
        ]
    ],
    'span' => 7
];
$fields[] = [
    'type' => 'checkbox',
    'text' => '',
    'var'  => 'followup_lock',
    'opts' => [
        [
            'val'     => 'Y',
            'text'    => 'Lock this followup status once set?',
            'checked' => $status->followup_lock
        ]
    ],
    'span' => 7
];


$title = ($status->id) ? "Edit $status->name" : "New status";
$url = ($status->id) ? "/admin/statuses/$status->id" : "/admin/statuses";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$panel = Panel::init('primary')->header($title)->content($form)->footer($save)->render();

// Show conditions.
if (!$status->expirations)
{
    exit;
}

// Show all expirations in a table
$headers = ['Name', 'Expires', "Delete"];
$rows = [];
foreach ($status->expirations AS $expiration)
{
    $rows[] = [
        "<a href='/admin/status/$status->id/expiration/$expiration->id'>$expiration->name</a>",
        $expiration->expires / 60 / 60,
        "<a class='get' href='/admin/status/$status->id/expiration/$expiration->id/delete'><i class='fa fa-trash-o'></i></a>"
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$fields = [];
$expiration = (isset($eid)) ? Expiration::find($eid) : new Expiration;
$fields[] = ['type' => 'input', 'text' => 'Name:', 'var' => 'name', 'val' => $expiration->name, 'span' => 7];
$expires = $expiration->expires ? $expiration->expires / 60 / 60 : 0;
$fields[] = ['type' => 'input', 'text' => 'Expires in Hours:', 'var' => 'expires', 'val' => $expires, 'span' => 7];
$expires_before = $expiration->expires_before ? $expiration->expires_before / 60 / 60 : 0;
$fields[] = [
    'type' => 'input',
    'text' => 'Expires in Hours Before Appointment:',
    'var'  => 'expires_before',
    'val'  => $expires_before,
    'span' => 7
];
$expires_after = $expiration->expires_after ? $expiration->expires_after / 60 / 60 : 0;
$fields[] = [
    'type' => 'input',
    'text' => 'Expires in Hours AFTER Appointment:',
    'var'  => 'expires_after',
    'val'  => $expires_after,
    'span' => 7
];

$opts = [];
$opts[] = ['val' => 'Status Change', 'text' => 'Time since last status change'];
$opts[] = ['val' => 'Last Note', 'text' => 'Time since last note was made'];
if ($expiration->type)
{
    array_unshift($opts, ['val' => $expiration->type, 'text' => $expiration->type]);
}

$fields[] = [
    'type'    => 'select',
    'var'     => 'type',
    'text'    => 'This expiration is based on:',
    'opts'    => $opts,
    'span'    => 7,
    'comment' => 'Expirations are based on time since status has been changed or when a follow-up happened.'
];

$opts = [];
$opts[] = ['val' => 'N', 'text' => 'No Warning'];
$opts[] = ['val' => 'Y', 'text' => 'Yellow'];
$opts[] = ['val' => 'R', 'text' => 'Red'];
if ($expiration->warning)
{
    array_unshift($opts, ['val' => $expiration->warning, 'text' => $expiration->warning]);
}

$fields[] = [
    'type'    => 'select',
    'var'     => 'warning',
    'text'    => 'Color of Lead/Quote Warning:',
    'opts'    => $opts,
    'span'    => 7,
    'comment' => 'If this expiration happens, what color should the lead/quote warning notification be?'
];


$title = ($expiration->id) ? "Edit $expiration->name" : "New Expiration";
$url = ($expiration->id) ? "/admin/status/$status->id/expiration/$expiration->id" : "/admin/status/$status->id/expiration";
$form = Forms::init()->id('editEForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editEForm')->render();
$panel .= Panel::init('info')->header("Expirations")->content($table . $form . $save)->render();

// Now All the actions if we set an action
if ($expiration->id)
{
    $headers = ['Description', 'To', 'SMS', 'Email', 'Attachment', 'Delete'];
    $rows = [];
    foreach ($expiration->actions as $action)
    {
        $rows[] = [
            "<a href='/admin/status/$status->id/expiration/$expiration->id/action/$action->id'>$action->description</a>",
            $action->designation->name,
            $action->sms ? 'Yes' : 'No',
            $action->email ? 'Yes' : 'No',
            $action->attachment ?
                "<a class='mjax' data-target='#workModal' href='/admin/action/$action->id/attachment'>$action->attachment</a>" :
                "<a class='mjax' data-target='#workModal' href='/admin/action/$action->id/attachment'>None</a>",
            "<a class='get' href='/admin/status/action/$action->id/delete'><i class='fa fa-trash-o'></i></a>"
        ];
    }
    $table = Table::init()->headers($headers)->rows($rows)->render();
    $action = (isset($aid) && $aid) ? Action::find($aid) : new Action;
    $fields = [];
    $fields[] = [
        'type' => 'textarea',
        'var'  => 'description',
        'val'  => $action->description,
        'span' => 7,
        'text' => 'Description:'
    ];
    $opts = [];
    foreach (Designation::all() as $designation)
    {
        $opts[] = ['val' => $designation->id, 'text' => $designation->name];
    }
    if ($action->designation_id)
    {
        array_unshift($opts, ['val' => $action->designation_id, 'text' => $action->designation->name]);
    }
    $fields[] = ['type' => 'select', 'var' => 'designation_id', 'opts' => $opts, 'span' => 7, 'text' => 'Send To:'];
    $opts = [];
    $opts[] = ['val' => 'Y', 'text' => 'Send SMS', 'checked' => $action->email];
    $fields[] = ['type' => 'checkbox', 'var' => 'sms', 'opts' => $opts, 'span' => 7, 'class' => 'sms-selector'];
    $fields[] = [
        'type'  => 'textarea',
        'var'   => 'sms_content',
        'val'   => $action->sms_content,
        'span'  => 7,
        'class' => 'sms',
        'text'  => 'SMS Content:'
    ];
    $opts = [];
    $opts[] = ['val' => 'Y', 'text' => 'Send Email', 'checked' => $action->email, 'class' => 'email-selector'];
    $fields[] = ['type' => 'checkbox', 'var' => 'email', 'opts' => $opts, 'span' => 7, 'class' => 'email'];
    $fields[] = [
        'type'  => 'input',
        'var'   => 'email_subject',
        'val'   => $action->email_subject,
        'span'  => 7,
        'class' => 'email',
        'text'  => 'E-mail Subject:'
    ];
    $fields[] = [
        'type'  => 'textarea',
        'var'   => 'email_content',
        'val'   => $action->email_content,
        'span'  => 7,
        'class' => 'email',
        'text'  => 'E-mail Content:'
    ];
    $url = ($action->id) ? "/admin/status/$status->id/expiration/$expiration->id/action/$action->id" :
        "/admin/status/$status->id/expiration/$expiration->id/action";
    $form = Forms::init()->id('editAForm')->url($url)->labelSpan(4)->elements($fields)->render();
    $save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editAForm')->render();
    $panel .= Panel::init('info')->header("Action")->content($table . $form . $save)->render();

} // if there is an expiration set.


$span .= BS::span(6, $panel);
echo Modal::init()->id('workModal')->onlyConstruct()->render();


echo BS::row($span);