<?php
$headers = ['Question', 'Quote Type', 'Image'];
$rows = [];
$faqs = Faq::whereActive(true)->get();
foreach ($faqs AS $faq)
{
    $rows[] = [
        "<a href='/admin/faq/$faq->id'>$faq->question</a>",
        $faq->type,
        $faq->image ? "<img src='/faq/$faq->image' height='100'>" : "No Image"
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);
$fields = [];
$faq = (isset($id)) ? Faq::find($id) : new Faq;
$fields[] = ['type' => 'textarea', 'text' => 'Question:', 'var' => 'question', 'val' => $faq->question, 'span' => 7];
$fields[] = ['type' => 'textarea', 'text' => 'Answer:', 'var' => 'answer', 'val' => $faq->answer, 'span' => 7];
$fields[] = ['type' => 'file', 'text' => 'Image:', 'var' => 'image', 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Figure:', 'var' => 'figure', 'val' => $faq->figure, 'span' => 7];
$fields[] = [
    'type' => 'select',
    'text' => 'Quote Type:',
    'var'  => 'type',
    'opts' => [
        ['val' => $faq->type, 'text' => $faq->type],
        ['val' => 'Full Kitchen', 'text' => "Full Kitchen"],
        ['val' => 'Cabinet Only', 'text' => 'Cabinet Only'],
        ['val' => 'Cabinet and Install', 'text' => 'Cabinet and Install'],
        ['val' => 'Granite Only', 'text' => 'Granite Only'],
        ['val' => 'Builder', 'text' => 'Builder']
    ],
    'span' => 7

];
$fields[] = ['type' => 'submit',  'var' => 'save', 'class' => 'primary', 'val' => 'Save'];

$title = ($faq->id) ? "Edit $faq->item" : "New FAQ";
$url = ($faq->id) ? "/admin/faq/$faq->id" : "/admin/faq";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$delete = $faq->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/faq/$faq->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);