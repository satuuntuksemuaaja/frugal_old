<?php
class ProfileController extends BaseController
{
  public $layout = "layouts.main";

  public function view($id)
  {
    $view = View::make('profiles.view');
    $view->customer = Customer::find($id);
    $this->layout->title = "Customer Profile :: " . $view->customer->name;
    $this->layout->content = $view;
  }

  public function updateCustomer($id, $field)
  {
    $customer = Customer::find($id);
    $customer->{$field} = Input::get('value');
    $customer->save();
    return Response::json(['success' => true]);
  }

  public function updateContact($id, $field)
  {
    $contact = Contact::find($id);
    if ($field == 'mobile' || $field == 'home' || $field == 'alternate')
      $value = preg_replace('/\D/', null, Input::get('value'));
    else
      $value = Input::get('value');
    $contact->{$field} = $value;
    $contact->save();
    return Response::json(['success' => true]);
  }

  public function inboundCRM($name, $number)
  {
    $contact = Contact::whereMobile($number)->first();
    if ($contact)
    {
      $view = View::make('profiles.view');
      $view->customer = $contact->customer;
      $this->layout->title = "Customer Profile :: " . $view->customer->name;
      $this->layout->content = $view;
    }
    else return "Contact not found!";
  }

}