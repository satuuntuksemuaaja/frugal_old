<?php
class CustomerController extends BaseController
{
  public $layout = "layouts.main";

  public function index()
  {
    $view = View::make('customers.index');
    $view->customers = Customer::orderBy('name', "ASC")->get();
    $this->layout->title = "Customer Manager";
    $this->layout->content = $view;
  }

  public function notes($id)
  {
    $customer = Customer::find($id);
    $note = new Note;
    $note->note = Input::get('notes');
    $note->customer_id = $id;
    $note->user_id = Auth::user()->id;
    $note->save();
    return Response::json(['status' => 'success', 'action' => 'selfreload']);
  }

}