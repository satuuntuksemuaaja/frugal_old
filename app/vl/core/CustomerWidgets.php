<?php
namespace vl\core;

use Button;
use Carbon\Carbon;
use Customer;
use Editable;
use FFT;
use Forms;
use Job;
use LeadSource;
use Panel;
use Table;
use Task;

class CustomerWidgets
{

    static public function customerWidget(Customer $customer)
    {
        $rows = [];
        $rows[] = ["<span class='pull-right'><b>Customer Number:</b>", $customer->id];
        $name = Editable::init()->id("name")->placement('right')->type('text')
                        ->title("Customer Name")->linkText($customer->name ?: "Empty")
                        ->url("/customer/{$customer->id}/name/update")->render();
        $address = Editable::init()->id("address")->placement('right')->type('text')
                           ->title("Customer Address")->linkText($customer->address ?: "Empty")
                           ->url("/customer/{$customer->id}/address/update")->render();
        $city = Editable::init()->id("city")->placement('right')->type('text')
                        ->title("Customer City")->linkText($customer->city ?: "Empty")
                        ->url("/customer/{$customer->id}/city/update")->render();
        $state = Editable::init()->id("state")->placement('right')->type('text')
                         ->title("Customer State")->linkText($customer->state ?: "Empty")
                         ->url("/customer/{$customer->id}/state/update")->render();
        $zip = Editable::init()->id("zip")->placement('right')->type('text')
                       ->title("Customer Zip Code")->linkText($customer->zip ?: "Empty")
                       ->url("/customer/{$customer->id}/zip/update")->render();

        $jaddress = Editable::init()->id("address")->placement('right')->type('text')
            ->title("Job Address")->linkText($customer->job_address ?: "Empty")
            ->url("/customer/{$customer->id}/job_address/update")->render();
        $jcity = Editable::init()->id("jcity")->placement('right')->type('text')
            ->title("Job City")->linkText($customer->job_city ?: "Empty")
            ->url("/customer/{$customer->id}/job_city/update")->render();
        $jstate = Editable::init()->id("state")->placement('right')->type('text')
            ->title("Job State")->linkText($customer->job_state ?: "Empty")
            ->url("/customer/{$customer->id}/job_state/update")->render();
        $jzip = Editable::init()->id("zip")->placement('right')->type('text')
            ->title("Job Zip Code")->linkText($customer->job_zip ?: "Empty")
            ->url("/customer/{$customer->id}/job_zip/update")->render();

        $rows[] = ["<span class='pull-right'><b>Name:</b>", $name];
        $rows[] = ["<span class='pull-right'><b>Address:</b>", $address];
        $rows[] = ["<span class='pull-right'><b>City/State/Zip:</b>",
            $city . ", " . $state . ". " . $zip];
        $rows[] = ["<span class='pull-right'><b>Job Address:</b>", $jaddress];
        $rows[] = ["<span class='pull-right'><b>City/State/Zip:</b>",
                   $jcity . ", " . $jstate . ". " . $jzip];

        $table = Table::init()->rows($rows)->render();
        $data = $table;
        $data .= "<h4>Customer Contacts</h4>";
        $rows = [];
        foreach ($customer->contacts AS $contact)
        {
            $name = Editable::init()->id("contactname")->placement('right')->type('text')
                            ->title("Contact Name")->linkText($contact->name ?: "Empty")
                            ->url("/contact/{$contact->id}/name/update")->render();
            $email = Editable::init()->id("contactemail")->placement('right')->type('text')
                             ->title("Contact E-mail")->linkText($contact->email ?: "Empty")
                             ->url("/contact/{$contact->id}/email/update")->render();
            $mobile = Editable::init()->id("contactmobile")->placement('right')->type('text')
                              ->title("Contact Mobile Number")->linkText(Formatter::numberFormat($contact->mobile))
                              ->url("/contact/{$contact->id}/mobile/update")->render();
            $home = Editable::init()->id("contacthome")->placement('right')->type('text')
                            ->title("Contact Home Number")->linkText(Formatter::numberFormat($contact->home))
                            ->url("/contact/{$contact->id}/home/update")->render();
            $alt = Editable::init()->id("contactalt")->placement('right')->type('text')
                           ->title("Contact Alternate Number")->linkText(Formatter::numberFormat($contact->alternate))
                           ->url("/contact/{$contact->id}/alternate/update")->render();

            $rows[] = ["<span class='pull-right'><b>Contact:</b>", $name];
            $rows[] = ["<span class='pull-right'><b>E-mail:</b>", $email];
            $rows[] = ["<span class='pull-right'><b>Mobile:</b>", $mobile];
            $rows[] = ["<span class='pull-right'><b>Home:</b>", $home];
            $rows[] = ["<span class='pull-right'><b>Alternate:</b>", $alt];
        }
        $table = Table::init()->rows($rows)->render();
        $data .= $table;
        return Panel::init('primary')->header("Customer Details")->content($data)->render();
    }

    static public function leadWidget(Customer $customer)
    {
        $headers = ['Age', 'Status', 'Designer', 'Lead Source'];
        $rows = [];
        foreach (LeadSource::whereActive(true)->get() AS $source)
            $sources[] = ['value' => $source->id, 'text' => str_replace("'", null, $source->type)];
        foreach ($customer->leads AS $lead)
        {
            $source = Editable::init()->id("sources")->source($sources)->placement('left')->type('select')
                              ->title("Lead Source")->linkText($lead->source ? $lead->source->type : null)
                              ->url("/lead/{$lead->id}/source/update")->render();
            $rows[] = [$lead->created_at->diffInDays(),
                ($lead->status) ? $lead->status->name : "New",
                ($lead->user) ? $lead->user->name : "No Designer",
                $source];
        }
        $table = Table::init()->headers($headers)->rows($rows)->render();
        return Panel::init('info')->header("Leads")->content($table)->render();
    }

    static public function quoteWidget(Customer $customer)
    {
        $headers = ['Quote', 'Type', 'Status', 'Accepted'];
        $rows = [];
        foreach ($customer->quotes AS $quote)
        {
            $title = ($quote->title) ? $quote->title : $quote->id;
            $rows[] = ["<a href='/quote/$quote->id/view'>$title</a>",
                $quote->type,
                ($quote->final) ? "Final Quote" : "Initial Quote",
                ($quote->accepted) ? "Yes" : "No"
            ];
        }
        $table = Table::init()->headers($headers)->rows($rows)->render();
        return Panel::init('warning')->header("Quotes")->content($table)->render();
    }

    static public function changeOrders(Job $job)
    {
        if ($job->orders->count() == 0) return null;
        $data = null;
        foreach ($job->orders AS $order)
        {
            $data .= "<b>Date: " . $order->created_at->format("m/d/y") . "</b><br/><br/>";
            foreach ($order->items AS $item)
                $data .= $item->description . " - $" . number_format($item->price, 2) . "<br/>";
            $data .= "<hr/>";
        }
        return $data;
    }

    static public function jobWidget(Customer $customer)
    {
        $headers = ['#', 'Contractor', 'Starts', 'Notes', 'Incoming Notes'];
        $rows = [];
        foreach ($customer->quotes AS $quote)
        {
            if ($quote->job)
            {
                if (!$cData = self::changeOrders($quote->job))
                {
                    $pop = null;
                }
                else $pop = "class='popovered' " . \BS::popover("Change Order Details", $cData, 'left');
                $rows[] = ["<a $pop href='/job/{$quote->job->id}/schedules'>{$quote->job->id}</a>",
                    null, null, Carbon::parse($quote->job->start_date)->format('m/d/y'),
                    nl2br($quote->job->notes)
                ];
                foreach ($quote->job->schedules AS $schedule)
                {
                    $rows[] = [null,
                        ($schedule->user) ? $schedule->user->name : "Unassigned",
                        Carbon::parse($schedule->start)->format("m/d/y"),
                        nl2br($schedule->notes),
                        nl2br($schedule->contractor_notes)];
                } // ea schedule
            } // ea job
        }// ea quote
        $table = Table::init()->headers($headers)->rows($rows)->responsive()->render();
        return Panel::init('success')->header("Jobs")->content($table)->render();
    }// fn

    static public function FFTWidget(Customer $customer, $warranty = false)
    {

        // Since there aren't many of these we can just sort through them.
        $headers = ['Pre-Assign', 'Pre-Schedule', 'Assigned', 'Scheduled', 'Notes'];
        $rows = [];
        $ffts = FFT::whereClosed(false)->whereWarranty($warranty)->get();
        foreach ($ffts AS $fft)
        {
            if (@$fft->job->quote->lead->customer->id == $customer->id)
            {
                $preassign = ($fft->pre_assigned) ? $fft->preassigned->name : "Unassigned";
                $assigned = ($fft->assigned) ? $fft->assigned->name : "Unassigned";
                $preschedule = ($fft->pre_schedule_start != '0000-00-00 00:00:00') ?
                    Carbon::parse($fft->pre_schedule_start)->format("m/d/y h:i a") : "Unscheduled";
                $scheduled = ($fft->schedule_start != '0000-00-00 00:00:00') ?
                    Carbon::parse($fft->schedule_start)->format("m/d/y h:i a") : "Unscheduled";
                $rows[] = [$preassign, $preschedule, $assigned, $scheduled, nl2br($fft->notes)];
            }
        }
        $table = Table::init()->headers($headers)->rows($rows)->render();
        return Panel::init('danger')->header($warranty ? "Warranty Items" : "Final Touch")->content($table)->render();
    }

    static public function notesWidget(Customer $customer)
    {
        $headers = ['From', 'Note'];
        $rows = [];
        foreach ($customer->notes AS $note)
            $rows[] = [$note->user->name . " ( " . $note->created_at->format('m/d/y') . " )", nl2br($note->note)];
        $table = Table::init()->headers($headers)->rows($rows)->render();

        $fields = [];
        $fields[] = ['type' => 'textarea',
                     'span' => 9, 'rows' => 10, 'var' => 'notes', 'text' => 'Notes:', 'val' => null];
        $form = Forms::init()->id('notesForm')->url("/customer/$customer->id/notes/save")->elements($fields)->render();
        $save = Button::init()->text("Save Notes")->icon('check')->color('primary post')->formid('notesForm')->render();
        return Panel::init('default')->header("Customer Notes")->content($table . $form)->footer($save)->render();
    }

    static public function tasksWidget(Customer $customer)
    {
        $headers = ['Task', 'Assigned', 'Due'];
        $rows = [];
        $tasks = Task::whereClosed(false)->get();
        foreach ($tasks AS $task)
        {
            if (
                ($task->job && $task->job->quote->lead->customer->id == $customer->id) ||
                ($task->customer_id == $customer->id)
            )
            {
                $rows[] = ["<a href='/task/$task->id/view'>$task->subject</a>", ($task->assigned) ? $task->assigned->name : "Unassigned",
                    ($task->due != '0000-00-00') ? Carbon::parse($task->due)->format('m/d/y') : "No Due Date"];
            }
        }
        $table = Table::init()->headers($headers)->rows($rows)->render();
        $add = Button::init()->text("Add Task")->color('primary')->modal('workModal', true)
                     ->url("/task/customer/$customer->id/job/0/quick?fromProfile=true")->icon('plus')->render();
        return Panel::init('primary')->header("Task List for Customer")->content($table)->footer($add)->render();
   }

}