<?php
echo BS::title("Admin", "Frugal Kitchens Administrator");

$buttons[] = ['icon' => 'money', 'color' => 'success', 'text' => 'Payments', 'url' => '/admin/payments'];
$buttons[] = ['icon' => 'lock', 'color' => 'default', 'text' => 'Users', 'url' => '/admin/users'];
$buttons[] = ['icon' => 'lock', 'color' => 'default', 'text' => 'Access Levels', 'url' => '/admin/levels'];
$buttons[] = ['icon' => 'lock', 'color' => 'default', 'text' => 'Designations', 'url' => '/admin/designations'];
$buttons[] = ['icon' => 'wrench', 'color' => 'primary', 'text' => 'Lead Sources', 'url' => '/admin/sources'];
$buttons[] = ['icon' => 'question', 'color' => 'primary', 'text' => 'Questionaires', 'url' => '/admin/questionaire'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Vendors', 'url' => '/admin/vendors'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Granite', 'url' => '/admin/granite'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Sinks', 'url' => '/admin/sinks'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Appliances', 'url' => '/admin/appliances'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Cabinets', 'url' => '/admin/cabinets'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Hardware', 'url' => '/admin/hardware'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Accessories', 'url' => '/admin/accessories'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Statuses', 'url' => '/admin/statuses'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Pricing', 'url' => '/admin/pricing'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Payouts', 'url' => '/payouts'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Dynamic', 'url' => '/admin/dynamic'];
$buttons[] = ['icon' => 'question', 'color' => 'warning', 'text' => 'Punch List', 'url' => '/admin/punches'];
$buttons[] = ['icon' => 'plus', 'color' => 'default', 'text' => 'Addons', 'url' => '/admin/addons'];
$buttons[] = ['icon' => 'refresh', 'color' => 'primary', 'text' => 'Authorizations', 'url' => '/admin/authorizations'];
$buttons[] = ['icon' => 'refresh', 'color' => 'info', 'text' => 'FAQ', 'url' => '/admin/faq'];
$buttons[] = ['icon' => 'comment', 'color' => 'primary', 'text' => 'Responsibility', 'url' => '/admin/responsibilities'];
$buttons[] = ['icon' => 'dollar', 'color' => 'success', 'text' => 'Promotions', 'url' => '/admin/promotions'];


$options = BS::Buttons($buttons);
$span = BS::span(12, $options);
echo BS::row($span);

