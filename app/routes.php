<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Inbound Mail and SMS
Route::any('inbound/mail', 'InboundController@inboundMail');
Route::post('inbound/sms', 'InboundController@inboundSMS');
Route::get('login', 'IndexController@login');
Route::post('login', 'IndexController@auth');
Route::get('login/{hash}', 'IndexController@loginBypass');
Route::get('confirm/job/{id}', 'InboundController@confirmation');
Route::get('admin/user/{id}/google/authorize', 'AdminController@googleAuthorize');
Route::get('googlecallback', 'AdminController@googleCallback');

Route::get('change/{id}/job/{jid}/sign', 'ChangeController@customerSign');
Route::get('punch/{id}/job/{jid}/sign', 'FFTController@customerSign');
Route::post('fft/{id}/signoff', 'FFTController@sign');
Route::post('fft/{id}/pay', 'FFTController@pay');
Route::get('fft/{id}/shop', 'FFTController@shopFromFFT');
Route::get('shopitem/{id}/delete', 'ShopController@deleteItem');

Route::get('shopitem/{id}/{type}', 'ShopController@setType');
Route::get('job/{id}/authsign', 'JobController@authSign');
Route::post('job/{id}/authsign', 'JobController@authSignSave');

Route::get('customer/{id}/appliances', 'QuoteController@customerAppliances');
Route::post('customer/{id}/appliances', 'QuoteController@customerAppliancesSave');
Route::get('customer/{id}/appliances/thanks', 'QuoteController@customerAppliancesThanks');

Route::get('change/{id}/decline', 'ChangeController@decline');
Route::post('change/{id}/signature', 'ChangeController@sign');
Route::get('logout', function ()
{
    Auth::logout();
    Session::flush();
    return Redirect::to("/");
});
Route::group(['before' => 'auth'], function ()
{
    Route::get('receiving', 'ReceivingController@index');
    Route::get('receiving/{id}', 'ReceivingController@view');
    Route::get('receiving/{id}/item/{iid}/receive', 'ReceivingController@receive');
    Route::get('receiving/item/{id}/unverify', 'ReceivingController@unverify');
    // Buildup
    Route::get('buildup', 'BuildController@index');
    Route::get('build/{id}/build', 'BuildController@build');
    Route::get('build/{id}/load', 'BuildController@load');
    Route::get('build/{id}/left', 'BuildController@left');
    Route::get('job/{id}/buildupnote', 'JobController@buildupnote');
    Route::post('job/{id}/buildupnote', 'JobController@buildupnoteSave');
    Route::get('quote/{id}/files', 'FilesController@showModal');
    Route::get('quote/{id}/appsettings', 'QuoteController@appSettings');
    Route::post('quote/{id}/appsettings', 'QuoteController@appSettingsSave');
    Route::get('quote/{id}/appsettings/send', 'QuoteController@appSettingsSend');


    Route::get('shop', 'ShopController@index');
    Route::post('shop/new', 'ShopController@store');
    Route::get('shop/{id}', 'ShopController@show');
    Route::get('shop/{id}/complete', 'ShopController@archive');
    Route::post('shop/{id}/{cabid}/notes', 'ShopController@notes');

});
// Ajax Pulls
Route::group(["before" => "auth|notrec"], function ()
{
    Route::get('inbound/crm/{name}/{number}', 'ProfileController@inboundCRM');
    Route::get('ajax/status', 'AjaxController@status');
    Route::get('ajax/locations', 'AjaxController@locations');
    Route::get('ajax/designers', 'AjaxController@designers');

    Route::get('admin', 'AdminController@index');

    Route::get('admin/{type}', 'AdminController@editor');

    Route::post('admin/{type}', 'AdminController@editorSave');
    Route::get('admin/{type}/{id}/delete', 'AdminController@delete');
    Route::get('admin/{type}/{id}', 'AdminController@editor');
    Route::post('admin/{type}/{id}', 'AdminController@editorSave');
    Route::get('admin/users/{id}/sms', 'AdminController@sms');
    Route::post('admin/questionaire/{qid}/condition/update', 'AdminController@updateCondition');
    Route::get('admin/status/{id}/expiration/{eid}', 'AdminController@showExpiration');
    Route::post('admin/status/{id}/expiration/{eid}', 'AdminController@saveExpiration');
    Route::post('admin/status/{id}/expiration', 'AdminController@saveExpiration');
    Route::get('admin/status/{id}/expiration/{eid}/action/{aid}', 'AdminController@showExpiration');
    Route::post('admin/status/{id}/expiration/{eid}/action', 'AdminController@saveAction');
    Route::post('admin/status/{id}/expiration/{eid}/action/{aid}', 'AdminController@saveAction');
    Route::get('admin/status/{id}/expiration/{eid}/delete', 'AdminController@deleteAction');

    Route::get('admin/action/{id}/attachment', 'AdminController@attachmentModal');
    Route::post('admin/action/{id}/attachment', 'AdminController@attachment');
    Route::get('mobile', 'MobileController@index');
    Route::get('mobile/schedule/{id}/view', 'MobileController@view');
    Route::post('mobile/schedule/{id}/update', 'MobileController@update');
    Route::get('mobile/job/{id}/punch', 'MobileController@punch');
    Route::post('mobile/job/{id}/punch', 'MobileController@punchSave');

    Route::get('/', 'IndexController@index');
    Route::get('/dashboard', 'IndexController@dashboard');


    Route::get('profile/{id}/view', 'ProfileController@view');
    Route::post('customer/{id}/{field}/update', 'ProfileController@updateCustomer');
    Route::post('contact/{id}/{field}/update', 'ProfileController@updateContact');

    Route::get('reports', 'ReportController@index');
    Route::post('reports', 'ReportController@saveDate');
    Route::get('report/cabinets', 'ReportController@cabinets');
    Route::get('report/designers', 'ReportController@designers');
    Route::get('report/sold', 'ReportController@sold');
    Route::get('report/frugal', 'ReportController@frugal');
    Route::get('report/all/leads', 'ReportController@exportLeads');
    Route::get('report/all/zips', 'ReportController@exportZips');
    Route::get('leads', 'LeadsController@index');

    Route::post('lead/{id}/status/update', 'LeadsController@updateStatus');
    Route::post('lead/{id}/location/update', 'LeadsController@updateLocation');
    Route::get('lead/{id}/showroom/update', 'LeadsController@showRoom');
    Route::post('lead/{id}/showroom/update', 'LeadsController@updateShowroom');
    Route::post('lead/{id}/closing/update', 'LeadsController@updateClosing');
    Route::get('lead/{id}/closing/update', 'LeadsController@closingModal');
    Route::get('lead/{id}/measure/update', 'LeadsController@measurerModal');
    Route::post('lead/{id}/measure/update', 'LeadsController@updateMeasure');
    Route::post('lead/{id}/designer/update', 'LeadsController@updateDesigner');
    Route::post('lead/{id}/measurer/update', 'LeadsController@updateMeasurer');
    Route::post('lead/{id}/source/update', 'LeadsController@updateSource');

    Route::post('lead/create', 'LeadsController@createLead');
    Route::get('lead/{id}/quote', 'LeadsController@quoteModal');
    Route::get('lead/{id}/notes', 'LeadsController@notes');
    Route::post('lead/{id}/notes', 'LeadsController@notesSave');
    Route::get('lead/{id}/archive', 'LeadsController@archive');
    Route::get('lead/{id}/followups', 'LeadsController@followup');
    Route::get('lead/{id}/followups/{fid}', 'LeadsController@followup');
    Route::get('lead/{id}/followups/{fid}/close', 'LeadsController@closeFollowup');

    Route::post('lead/{id}/followups/{fid}', 'LeadsController@followupSave');


    Route::get('customers', 'CustomerController@index');
    Route::post('customer/{id}/notes/save', 'CustomerController@notes');

    Route::get('file/{id}/delete', 'FilesController@delete');
    Route::get('file/{id}/attach', 'FilesController@attachToggle');
    Route::get('quotes', 'QuoteController@index');
    Route::get('quotes/ajax/list', 'QuoteController@quoteAjax');
    Route::get('quote/{id}/start', 'QuoteController@begin');
    Route::get('quote/{id}/summary', 'QuoteController@summary');
    Route::get('quote/{id}/duplicate', 'QuoteController@duplicateModal');
    Route::post('quote/{id}/duplicate', 'QuoteController@duplicate');
    Route::get('quote/{id}/snapshots', 'QuoteController@snapshots');
    Route::post('quote/{id}/files/upload', 'FilesController@upload');
    Route::get('quote/{id}/archive', 'QuoteController@archive');
    Route::get('quote/{id}/delete', 'QuoteController@delete');
    Route::get('quote/{id}/financing', 'QuoteController@financing');
    Route::get('quote/{id}/contract', 'QuoteController@contract');
    Route::get('quote/{id}/convert', 'QuoteController@convertToJob');
    Route::get('quote/{id}/cabinet/{cid}/xml', 'QuoteController@cabinetXML');
    Route::post('quote/{id}/financing/{type}', 'QuoteController@financingSave');
    Route::get('quote/{id}/paperwork', 'QuoteController@paperwork');
    Route::get('quote/{id}/needspaperwork', 'QuoteController@needsPaperwork');
    Route::get('quote/{id}/cabinets', 'QuoteController@cabinets');
    Route::post('quote/{id}/cabinets/new', 'QuoteController@cabinetsSave');
    Route::get('quote/{id}/cabinet/{cid}/remove', 'QuoteController@cabinetDelete');
    Route::get('quote/{id}/cabinet/{cabid}/edit', 'QuoteController@cabinetEdit');
    Route::post('quote/{id}/cabinet/{cabid}/edit', 'QuoteController@cabinetEditSave');
    Route::get('quote/{id}/granite', 'QuoteController@granite');
    Route::post('quote/{id}/granite', 'QuoteController@graniteSave');
    Route::get('quote/{id}/granite/{type}/remove', 'QuoteController@graniteDelete');
    Route::post('quote/{id}/cabinet/{cabinet_id}/wood', 'QuoteController@uploadWood');

    Route::get('quote/{id}/appliances', 'QuoteController@appliances');
    Route::post('quote/{id}/appliances', 'QuoteController@appliancesSave');
    Route::post('quote/{id}/sinks/add', 'QuoteController@sinkAdd');
    Route::get('quote/{id}/sink/{instance}/remove', 'QuoteController@sinkDelete');

    Route::get('quote/{id}/accessories', 'QuoteController@accessories');
    Route::post('quote/{id}/accessories', 'QuoteController@accessoriesSave');
    Route::get('quote/{id}/accessory/{aid}/delete', 'QuoteController@accessoryRemove');

    Route::get('quote/{id}/hardware', 'QuoteController@hardware');
    Route::post('quote/{id}/hardware', 'QuoteController@hardwareSave');
    Route::get('quote/{id}/hardware/{type}/{hid}/delete', 'QuoteController@hardwareDelete');
    Route::get('quote/{id}/additional', 'QuoteController@additional');
    Route::post('quote/{id}/additional', 'QuoteController@additionalSave');

    Route::get('quote/{id}/questionaire', 'QuoteController@questionaire');
    Route::post('quote/{id}/questionaire', 'QuoteController@questionaireSave');

    Route::get('quote/{id}/addons', 'QuoteController@addons');
    Route::post('quote/{id}/addons', 'QuoteController@addonsUpdate');

    Route::post('quote/{id}/responsibilities', 'QuoteController@saveResponsibilities');
    Route::get('quote/{id}/led', 'QuoteController@led');
    Route::post('quote/{id}/led', 'QuoteController@ledSave');
    Route::get('quote/{id}/tile/{tid}/delete', 'QuoteController@removeTile');
    Route::post('quotes/create', 'QuoteController@create');
    Route::get('quote/{id}/final', 'QuoteController@create');
    Route::get('quote/{id}/view', 'QuoteController@view');
    Route::post('quote/{id}/{field}/liveupdate', 'QuoteController@updateQuoteField');

    // Jobs
    Route::get('jobs', 'JobController@index');
    Route::get('jobs/export', 'JobController@exportForm');
    Route::post('jobs/export', 'JobController@export');
    Route::get('job/{id}/track/{type}/reference/{ref}', 'JobController@track');
    Route::get('job/{id}/track/{type}/reference/{ref}/save', 'JobController@trackSave');
    Route::get('job/{id}/items', 'JobController@items');
    Route::get('job/{id}/picked', 'JobController@picked');
    Route::get('job/{id}/review', 'JobController@review');
    Route::get('job/{id}/arrival', 'JobController@arrival');

    Route::get('fft_designation/{id}', 'FFTController@designation');
    Route::post('fft_designation/{id}', 'FFTController@setDesignation');
    Route::get('job/{id}/notes', 'JobController@notes');
    Route::get('fft/{id}/notes', 'FFTController@notes');
    Route::post('fft/{id}/notes', 'FFTController@notesSave');

    Route::get('job/{id}/money', 'JobController@getMoney');
    Route::get('job/{id}/unlock', 'JobController@unlock');
    Route::get('job/{id}/xml', 'JobController@xml');
    Route::post('job/{id}/xml', 'JobController@xmlSave');
    Route::get('job/{id}/auth', 'JobController@auth');
    Route::post('job/{id}/auth/new', 'JobController@newAuth');
    Route::get('job/{id}/authdelete/{aid}', 'JobController@authDelete');
    Route::get('job/{id}/authsend', 'JobController@authSend');
    Route::get('job/{id}/authremove', 'JobController@authRemoveSig');
    Route::get('job/{id}/checklist', 'JobController@checklist');
    Route::get('job/{id}/starts', 'JobController@startsForm');
    Route::post('job/{id}/starts', 'JobController@starts');
    Route::get('job/{id}/construction', 'JobController@construction');
    Route::post('job/{id}/notes', 'JobController@notesSave');
    Route::get('item/{id}/verify', 'JobController@verifyItem');
    Route::post('item/{id}/reference', 'JobController@updateReference');
    Route::get('job/{id}/schedules', 'JobController@schedules');
    Route::get('schedule/{id}/default', 'JobController@defaultEmail');
    Route::get('schedule/{id}/lock', 'JobController@lockToggle');
    Route::get('job/{id}/schedules/new', 'JobController@createAuxSchedule');
    Route::get('job/{id}/delete', 'JobController@delete');
    Route::get('job/{id}/close', 'JobController@close');
    Route::get('job/{id}/create/{type}', 'JobController@createItem');
    Route::get('job/{id}/sendSchedules', 'JobController@sendToCustomer');
    Route::get('job/{id}/paid', 'JobController@markPaid');
    Route::post('job/{id}/finalSend', 'JobController@finalSend');
    Route::post('job/{id}/schedule/{sid}/day/{day}/designation/{designation}', 'JobController@createSchedule');
    Route::post('schedule/{id}/{method}', 'JobController@scheduleDate');
    Route::get('schedule/{id}/send', 'JobController@scheduleSend');
    Route::get('schedule/{id}/delete', 'JobController@scheduleDelete');
    Route::get('schedule/{id}/close', 'JobController@scheduleClose');
    Route::get('schedule/{id}/change/{type}', 'JobController@changeTime');
    Route::post('schedule/{id}/change/{type}', 'JobController@changeTimeSave');
    Route::post('schedule/{id}/contractor/close', 'JobController@scheduleCloseSave');
    Route::get('item/{id}/verify/{idx}', 'JobController@verifyIndex');
    Route::get('item/{id}/delete', 'JobController@deleteItem');
    Route::get('item/{id}/contractor_complete', 'JobController@contractorComplete');

    Route::get('fft', 'FFTController@FFTIndex');
    Route::get('fft/{id}/change/{type}', 'FFTController@changeTime');
    Route::post('fft/{id}/change/{type}', 'FFTController@changeTimeSave');
    Route::get('fft/{id}/payment', 'FFTController@payment');
    Route::get('fft/{id}/items', 'FFTController@itemsModal');
    Route::post('fft/{id}/items', 'FFTController@saveItem');
    Route::get('fft/{id}/close', 'FFTController@close');
    Route::post('/fft/{id}/hours', 'FFTController@hours');
    Route::get('fft/{id}/signature', 'FFTController@signature');
    Route::get('fft/{id}/signature/pdf', 'FFTController@signaturePDF');
    Route::get('fft/{id}/signoff', 'FFTController@signoff');
    Route::get('fft/{id}/signoff/pdf', 'FFTController@signoffPDF');
    Route::get('orderitem/{id}/delete', 'ChangeController@delete');
  //  Route::post('fft/{id}/signoff', 'FFTController@signoffSave');
    Route::post('fft/{id}/signature', 'FFTController@signatureSave');
    Route::post('fft/{id}/item/create', 'FFTController@createItem');
    Route::get('warranties', 'FFTController@warrantyIndex');
    Route::post('warranty/new', 'FFTController@newWarranty');
    Route::get('service', 'FFTController@serviceIndex');
    Route::post('service/new', 'FFTController@newService');

    Route::get('fft/{id}/item/{item}/update', 'FFTController@trackItem');
    Route::post('fft/{id}/{item}/liveupdate', 'FFTController@liveUpdate');
    Route::get('tasks', 'TasksController@index');
    Route::post('tasks/new', 'TasksController@createTask');
    Route::get('task/{id}/view', 'TasksController@view');
    Route::get('task/{id}/close', 'TasksController@closeTask');
    Route::post('task/{id}/note/create', 'TasksController@createNote');
    Route::get('task/customer/{cid}/job/{jid}/quick', 'TasksController@quickModal');

    // Change Orders
    Route::get('changes', 'ChangeController@index');
    Route::post('changes/new', 'ChangeController@create');
    Route::get('change/{id}', 'ChangeController@view');
    Route::post('change/{id}/item/new', 'ChangeController@addItem');
    Route::get('change/{id}/signature', 'ChangeController@signaturePad');
    Route::get('change/{id}/orderItem/{iid}', 'ChangeController@orderItem');
    Route::get('change/{id}/signature/remove', 'ChangeController@removeSignature');
    Route::get('change/{id}/send', 'ChangeController@send');
    Route::get('change/{id}/close', 'ChangeController@close');
    Route::post('change/{id}/item/{iid}/{field}', 'ChangeController@updateItem');

    // Punch List to Customer
    Route::get('fft/{id}/punch/send', 'FFTController@emailPunch');
    // Purchase Orders
    Route::get('pos', 'PurchaseController@index');
    Route::post('pos/new', 'PurchaseController@create');
    Route::get('po/{id}', 'PurchaseController@view');
    Route::get('po/{id}/child', 'PurchaseController@spawn');
    Route::get('po/{id}/delete', 'PurchaseController@delete');
    Route::get('pos/{id}/archive', 'PurchaseController@archive');

    Route::post('po/{id}/items/new', 'PurchaseController@newItem');
    Route::get('po/{id}/order', 'PurchaseController@order');
    Route::post('po/{id}/type', 'PurchaseController@changeType');
    Route::post('po/{id}/inv', 'PurchaseController@changeInvoice');
    Route::post('po/{id}/projected', 'PurchaseController@changeProjected');
    Route::get('item/{id}/unverify', 'PurchaseController@unverify');


    Route::get('item/{id}/replacement', 'FFTController@toggleReplacement');
    Route::get('item/{id}/orderable', 'FFTController@toggleOrderable');
    Route::post('item/{id}/notes', 'FFTController@updateNotes');
    Route::post('item/{id}/contractor_notes', 'FFTController@updateContractorNotes');
    Route::get('po/{id}/confirm', 'PurchaseController@confirm');
    Route::get('po/{id}/item/{iid}/receive', 'PurchaseController@receive');
    Route::get('po/{id}/item/{iid}/remove', 'PurchaseController@removeItem');

    Route::get('punches/{job}', 'PunchController@index');
    Route::get('punches/{job}/{id}', 'PunchController@showPunch');

    Route::get('report/designers/{user}/{month}', 'ReportController@detailModal');
    Route::get('report/sources/{source}/{type}', 'ReportController@sourceDetail');
    Route::get('report/user/{user}/{type}', 'ReportController@userDetail');
    Route::get('report/dashboard/range/{status}/{id}/{start}/{end}', 'ReportController@dashboardRange');
    Route::post('admin/granite/{id}/price', 'AdminController@setGranitePrice');


    Route::get('payouts', 'PayoutController@index');
    Route::get('payouts/create', 'PayoutController@create');
    Route::post('payouts/create', 'PayoutController@store');
    Route::get('payouts/{id}/delete', 'PayoutController@destroy');
    Route::get('payouts/{id}', 'PayoutController@show');

    Route::get('payouts/report/{id}', 'PayoutController@report');
    Route::post('payouts/report/{id}', 'PayoutController@createReport');
    Route::post('payouts/{id}', 'PayoutController@update');


});