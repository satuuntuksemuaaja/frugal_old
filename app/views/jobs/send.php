<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/14/15
 * Time: 4:11 PM
 */
$body = View::make('emails.schedules')->withJob($job)->render();
$fields[] = ['type' => 'summernote', 'var' => 'body', 'span' => 10, 'val' => $body];
$fields[] = ['text' => 'E-mail Address to CC: (if Any)', 'type' => 'input', 'var' => 'cc', 'span' => 5, 'name' => 'cc', 'class' => 'cc'];
$opts[] = ['val' => 0, 'text' => '-- Select Frugal User -- (optional)'];
foreach (User::whereActive(true)->get() as $user)
{
    $opts[] = ['val' => $user->id, 'text' => $user->name];
}
$fields[] = ['type' => 'select', 'class' => 'userid', 'text' => 'CC Frugal User:', 'opts' => $opts, 'span' => 10, 'var' => 'user_id'];

$forms = Forms::init()->id('emailForm')->labelspan(1)->elements($fields)->url("/job/$job->id/finalSend")->render();
$send = Button::init()->text("Send to Customer")->color('primary note')->formid('emailForm')->icon('arrow-right')->render();
$span = BS::span(12, $forms . $send);
echo BS::row($span);

echo BS::encap("

$('.note').click(function()
{

var code = $('#summernote').code();
var cc = $('.cc').val();
var userid = $('.userid').val();
var serial = { body : code, email : cc, user_id : userid };

 $.ajax({type: 'post', url: '/job/{$job->id}/finalSend' , data: serial, datatype: 'json', success: function (data)
			    	    {
			    	       alert('Schedules were emailed to customer.');
			    	    }}); // success

});


");