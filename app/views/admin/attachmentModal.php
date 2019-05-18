<?php
$fields = [];
$pre = "<h4>This attachment will be emailed to all parties that are emailed in this action.</h4>";
$fields[] = ['type' => 'file', 'text' => 'Attachment:', 'var' => 'attachment'];
$fields[] = ['type' => 'submit', 'val' => 'Upload File', 'var' => 'upload', 'class' => 'primary'];
$form = Forms::init()->id('attachmentForm')->url("/admin/action/{$action->id}/attachment")->elements($fields)->render();
echo Modal::init()->isInline()->header("Upload Attachment")->content($pre.$form)->render();