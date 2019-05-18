<?php
use Carbon\Carbon;

class ReceivingController extends BaseController
{
  public $layout = "layouts.main";

  public function index()
  {
    $view =  View::make('receiving.index');
    $this->layout->title = "Receiving Area";
    $this->layout->content = $view;
  }

  public function view($id)
  {
    $view = View::make('receiving.view');
    $view->po = Po::find($id);
    $this->layout->title = "Purchase Order #{$view->po->number}";
    $this->layout->content = $view;
  }

  public function receive($id, $iid)
  {
    $item = PoItem::find($iid);
    $item->received = Carbon::now();
    $item->received_by = Auth::user()->id;
    $item->save();
    // Check to see if we need to close the PO.
    $po = Po::find($id);
    $close = true;
    foreach ($po->items AS $item)
      if (!$item->received_by)
        $close = false;
    if ($close)
    {
      $po->status = 'complete';
      $po->archived = 1;
      $po->save();
      return Response::json(['status' => 'success', 'action' => 'reload', 'url' => '/pos']);

    }
    return Response::json(['status' => 'success', 'action' => 'selfreload']);
  }

   public function unverify($id)
  {
    $item = PoItem::find($id);
    $item->received_by = 0;
    $item->received = '0000-00-00';
    $item->save();
    return Response::json(['status' => 'success', 'action' => 'selfreload']);
  }

}
