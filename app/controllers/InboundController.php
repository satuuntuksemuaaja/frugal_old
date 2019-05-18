<?php

use vl\core\SMS as S;
class InboundController extends BaseController
{

  public function inboundMail()
  {
    if (!Input::has('mandrill_events'))
      return "OK";
    $object = array_pop(json_decode(Input::get('mandrill_events')));
    $body = $object->msg->text;

      $lines = explode("\n", $body);
        foreach ($lines AS $line)
        {
            if (preg_match('/First/', $line))
                $first = trim(str_replace("First Name*: ", null, $line));
            else if (preg_match("/Last/", $line))
                $last = trim(str_replace("Last Name*: ", null, $line));
            else if (preg_match("/Email/", $line))
                $email = trim(str_replace("Your Email Address:*: ", null, $line));
            else if (preg_match("/Mobile/", $line))
                $mobile = trim(str_replace("Mobile:: ", null, $line));
            else if (preg_match("/Phone/", $line))
                $phone = trim(str_replace("Phone Number*: ", null, $line));

            else if (preg_match("/Source/", $line))
                $source = trim(str_replace("Lead Source: ", null, $line));
            else if (preg_match("/Street/", $line))
                $street = trim(str_replace("Street:: ", null, $line));
            else if (preg_match("/City/", $line))
                $city = trim(str_replace("City:: ", null, $line));
            else if (preg_match("/State/", $line))
                $state = trim(str_replace("State:: ", null, $line));
            else if (preg_match("/Zip/", $line))
                $zip = trim(str_replace("Zip Code:: ", null, $line));
            else if (preg_match("/Description/", $line))
                $description = trim(str_replace("Description:: ", null, $line));
            else if (preg_match("/Location/", $line))
                $location = trim(str_replace("Location:: ", null, $line));
            else if (preg_match("/designer/", $line))
                $isdesigner = trim(str_replace("a designer: ", null, $line));
            else if (preg_match("/their name/", $line))
                $designer = trim(str_replace("know their name:: ", null, $line));
        }
      // Create a new Customer Record
      $customer = new Customer;
      $customer->name = $first . " " . $last;
      $customer->address = $street;
      $customer->city = $city;
      $customer->state = $state;
      $customer->zip = $zip;
      $customer->notes = $description;
      $customer->save();
      $contact = new Contact;
      $contact->customer_id = $customer->id;
      $contact->name = $customer->name;
      $contact->email = $email;
      $contact->mobile = $mobile;
      $contact->home = $phone;
      $contact->save();
      $lead = new Lead;
      $lead->source_id = 9;
      $lead->customer_id = $customer->id;
      $lead->save();
    return "OK";
  }

  public function inboundSMS()
  {
    // #107 - Forward SMS
    Log::info(Input::all());
    $forward = User::whereFrugalNumber(Input::get('destination'))->first();
    if ($forward)
    {
      // We have a target to forward.
      Log::info("Found ! $forward->id");
      $sms = new SMS;
      $sms->source = Input::get('caller');
      $sms->destination = Input::get('destination');
      $sms->message = Input::get('message');
      $sms->user_id = $forward->id;
      $sms->save();
      S::command('send', ['target' => $forward->mobile, 'message' => "From: ".$sms->source . " - ". $sms->message]);
      \Log::info("Forwarding message to $forward->mobile");
      return;
    }
    \Log::info("Inbound Message: " . Input::get('message')  ." from " . Input::get('caller'));
    // Check for admin first.
    $user = User::whereMobile(Input::get('caller'))->first();
    if ($user)
    {
      \Log::info("Inbound Message: " . Input::get('message')  ." from $user->name ($user->id)");

      $task = Task::find($user->task_id);

      if (!$task)
      {
        \Log::info("NO task for $user->id");
        return;
      }
      \Log::info("Updating Task $task->id");
      if (preg_match('/tc|lm/i', Input::get('message')))
      {
       foreach (Task::whereUrgent(true)->whereAssignedId($user->id)->get() AS $task)
       {
        $task->satisfied = 1;
        $task->save();
        $note = new TaskNote;
        $note->task_id = $task->id;
        $note->user_id = $user->id;
        if (preg_match('/tc/i', Input::get('message')))
          $note->body = "I called the customer back and talked to them.";
        else
          $note->body = "I called the customer and left a message.";
        $note->save();
       }
      }
      else
      {
        $note = new TaskNote;
        $note->task_id = $task->id;
        $note->user_id = $user->id;
        $note->body = Input::get('message');
        $note->save();
        \Log::info("Wrote " . Input::get('message') . " for task $task->id");
      }
      return "OK";
    }

    $contact = Contact::whereMobile(Input::get('caller'))->first();
    if (!$contact)
      {
        Log::info(Input::get('caller') . " sent a message (".Input::get('message')."), but we don't know who they are!");
        return null;
      }
    $customer = $contact->customer;
    $message = Input::get('message');
    if (!preg_match("/1|2|3|4/", $message))
    {
      Log::info("$message recieved but no plans of what to do with it...");
      return;
    }
    $lead = Lead::whereCustomerId($customer->id)->whereClosed(false)->first();
    if (!$lead)
    {
      Log::info("Lead not found for customer, but they responded. Weird.");
      return;
    }
    if (preg_match('/1/', $message))
        $sid = 31;
    else if (preg_match('/2/', $message))
        $sid = 32;
    else  if (preg_match('/3/', $message))
        $sid = 38;
    else if (preg_match('/4/', $message))
        $sid = 39;
    \vl\leads\StatusManager::setLead($lead, $sid);
    Log::info("Just set Status $sid for Lead: $lead->id");
    $sms = new SMS;
    $sms->source = Input::get('caller');
    $sms->destination = Input::get('destination');
    $sms->message = Input::get('message');
    $sms->save();
  }

  public function confirmation($id)
  {
    $job = Job::find($id);
    $job->schedules_confirmed = 1;
    $job->save();
    $customer = $job->quote->lead->customer;
    $contact = $customer->contacts()->first();
    $subject = "[$contact->name] Thank you for confirming!";
    $data = [];
    try
    {
      Mail::send('emails.confirmed', $data, function($message) use ($contact, $subject)
        {
          $message->to([
                $contact->email => $contact->name,
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject($subject);
        });
    }
    catch (Exception $e)
    {

    }

    return "Thank you for confirming your schedule! You can close this window.";
  }


}