<?php

namespace vl\core;

use Appliance;
use Carbon\Carbon;
use Exception;
use Granite;
use Job;
use JobSchedule;
use Log;
use Mail;
use Question;
use Quote;
use Sink;
use User;
use vl\quotes\QuoteGenerator;

class ScheduleEngine
{
    /**
     * Determines what to send to the contractor.
     *
     * @param  JobSchedule $schedule [description]
     */
    static public function send(JobSchedule $schedule)
    {
        $emailContent = null;
        $smsContent = null;
        $start = Carbon::parse($schedule->start)->toDayDateTimeString();
        $customerInfo = self::getCustomer($schedule);
        $scheduleContentEmail = null; //holds day schedule and information from static calls.
        $scheduleContentSMS = null; //holds day schedule and information from static calls.
        $preNotes = "\n\nAdditional Job Notes: (if any)\n";
        if ($schedule->default_email)
        {
            $perDesignation = self::getQuestionsForDesignation($schedule);
        }
        else
        {
            $perDesignation = null;
        }
        $perDesignation .= self::getAddons($schedule, $schedule->designation_id);
        switch ($schedule->designation_id)
        {
            case 4 :
                //Cabinet Installers have 2 schedules. Get both.
                $cabinetInformation = self::cabinets($schedule);
                $installSchedules = JobSchedule::whereId($schedule->id)->get();
                foreach ($installSchedules AS $installSchedule)
                {
                    $start = Carbon::parse($installSchedule->start)->toDayDateTimeString();
                    $end = Carbon::parse($installSchedule->end)->toDayDateTimeString();
                    $scheduleContentEmail .= "<h3>$start</h3><p>$cabinetInformation[email]</p>";
                    $scheduleContentSMS .= "New Schedule: $start - See Google Calendar for More Info";
                    if (!$schedule->default_email)
                    {
                        self::eventForSchedule($installSchedule,
                            $customerInfo['email'] . $preNotes . $perDesignation . $installSchedule->notes);
                    }
                    else
                    {
                        self::eventForSchedule($installSchedule,
                            $cabinetInformation['sms'] . $customerInfo['email'] . $preNotes . $perDesignation . $installSchedule->notes);
                    }
                }
                break;
            case 3 :
                $installSchedules = JobSchedule::whereJobId($schedule->job->id)->whereDesignationId(3)
                    ->orderBy('start', 'ASC')->get();
                $day = 1;
                $graniteInformation = self::granite($schedule);
                foreach ($installSchedules AS $installSchedule)
                {
                    $start = Carbon::parse($installSchedule->start)->toDayDateTimeString();
                    $end = Carbon::parse($installSchedule->end)->toDayDateTimeString();
                    $scheduleContentEmail .= "Day {$day}: <h3>$start</h3><p>$graniteInformation[email]</p>";
                    $scheduleContentSMS .= "New Schedule: $start - See Google Calendar for More Info";
                    $day++;
                    if (!$schedule->default_email)
                    {
                        self::eventForSchedule($installSchedule,
                            $customerInfo['email'] . $preNotes . $perDesignation . $installSchedule->notes);
                    }
                    else
                    {
                        self::eventForSchedule($installSchedule,
                            $graniteInformation['sms'] . $customerInfo['email'] . $preNotes . $perDesignation . $installSchedule->notes);
                    }
                }
                break;

            case 1 :
                $plumberInformation = self::plumber($schedule);
                $scheduleContentEmail = "<h3>$start</h3><p>$plumberInformation[email]</p>";
                $scheduleContentSMS .= "New Schedule: $start - See Google Calendar for More Info";
                if (!$schedule->default_email)
                {
                    self::eventForSchedule($schedule,
                        $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                else
                {
                    self::eventForSchedule($schedule,
                        $plumberInformation['sms'] . $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                break;
            case 2 :
                $electricianInformation = self::electrician($schedule);
                $scheduleContentEmail = "<h3>$start</h3><p>$electricianInformation[email]</p>";
                $scheduleContentSMS .= "New Schedule: $start - See Google Calendar for More Info";
                if (!$schedule->default_email)
                {
                    self::eventForSchedule($schedule,
                        $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                else
                {
                    self::eventForSchedule($schedule,
                        $electricianInformation['sms'] . $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                break;
            case 11 : // Flooring Contractor
                $flooringInformation = self::flooring($schedule);
                $scheduleContentEmail = "<h3>$start</h3><p>$flooringInformation[email]</p>";
                $scheduleContentSMS .= "New Schedule: $start - See Google Calendar for More Info";
                if (!$schedule->default_email)
                {
                    self::eventForSchedule($schedule,
                        $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                else
                {
                    self::eventForSchedule($schedule,
                        $flooringInformation['sms'] . $customerInfo['email'] . $preNotes . $perDesignation . $schedule->notes);
                }
                break;
            default :
                self::eventForSchedule($schedule,
                    $preNotes . $perDesignation . $schedule->notes . $customerInfo['email']);
                break;

        }
        if (!$schedule->default_email)
        {
            $scheduleContentEmail = null;
            $scheduleContentSMS = null;
        }
        if ($schedule->notes)
        {
            $scheduleContentEmail .= "Additional Notes: Please note we will NOT be at your home at 6:00AM. This early hour is for display on our calendar only! - " . nl2br($schedule->notes);
            $scheduleContentSMS .= "NOTES: " . $schedule->notes . "\n";
        }
        if ($schedule->aux)
        {
            $start = Carbon::parse($schedule->start)->toDayDateTimeString();
            $customer = $schedule->job->quote->lead->customer->name;
            $schedule->notes = nl2br($schedule->notes);
            $scheduleContentEmail = "
<h4>On {$start}</h4><p>This job schedule has been appended to the job as an additional work order or day. The following instructions have been added:</p>
<p>$schedule->notes</p>";
            $scheduleContentSMS = "New Schedule: $start - See Google Calendar for More Info";
        }

        //So at this point we should have scheduleContentEmail and SMS to send.
        self::sendSMS($schedule,
            $scheduleContentSMS . "\n" . str_replace("<br/>", "\n", $perDesignation) . $customerInfo['sms']);
        self::sendEmail($schedule, $scheduleContentEmail . "<br/><br/>" . $perDesignation . $customerInfo['email']);
    }

    static public function getQuestionsForDesignation(JobSchedule $schedule, $contract = false)
    {
        // Get questions and answers for the designation.
        $data = null;
        $quote = $schedule->job->quote;
        $meta = unserialize($quote->meta)['meta'];
        $object = QuoteGenerator::getQuoteObject($quote);
        if (!$contract)
        {
            foreach ($object->processedQuestions AS $question => $answer)
            {
                if (($answer->answer > 0 || $answer->answer == 'Y') && $answer->question->designation_id == $schedule->designation_id)
                {
                    $data .= $answer->question->question . ': ' . $answer->answer . "\n";
                }
            } // fe
        }
        else
        {
            $questions = Question::whereContract(1)->get();
            foreach ($questions AS $question)
            {
                foreach ($quote->answers AS $answer)
                {
                    if ($answer->question_id == $question->id && $answer->question->designation_id == $schedule->designation_id &&
                        ($answer->answer > 0 || $answer->answer == 'on' || $answer->answer == 'Y')
                    )
                    {
                        $data .= "* " . sprintf($question->contract_format, $answer->answer) . "\n";
                    }
                }
            }
        } // else

        // Now check for LED.

        if ($schedule->designation_id == 2) // Electrician specific
        {

        } // if electrician.


        return $data;
    } //fn


    static public function eventForSchedule(JobSchedule $schedule, $content)
    {
        $content = str_replace("<br/>", "\n", $content);
        $content = strip_tags($content);
        $customerInfo = self::getCustomer($schedule);
        $params = [];
        $params['title'] = "Job Schedule: {$schedule->job->quote->lead->customer->name}";
        if ($schedule->job->quote->lead->customer->job_address)
        {
            $params['location'] = "{$schedule->job->quote->lead->customer->job_address}
    {$schedule->job->quote->lead->customer->job_city}, {$schedule->job->quote->lead->customer->job_state} {$schedule->job->quote->lead->customer->job_zip}";
        }
        else
        {
            $params['location'] = "{$schedule->job->quote->lead->customer->address}
    {$schedule->job->quote->lead->customer->city}, {$schedule->job->quote->lead->customer->state} {$schedule->job->quote->lead->customer->zip}";
        }
        $params['description'] = "
    $content

    Client Information:
    {$customerInfo['sms']}";
        $params['start'] = Carbon::parse($schedule->start);
        $params['end'] = Carbon::parse($schedule->end);
        try
        {
            Google::event($schedule->user, $params);
        } catch (Exception $e)
        {
            Log::info("Google Calendar Event Failed: " . $e->getMessage());
        }
    }

    static public function cabinets(JobSchedule $schedule)
    {
        $meta = unserialize($schedule->job->quote->meta);
        $cabinetInformation = null;
        foreach ($schedule->job->quote->cabinets AS $cabinet)
        {
            //$where = ($cabinet->inches) ? $cabinet->inches . " off floor/wall." : " on the floor/wall.";
            $cabinetInformation .= "Remove existing counters and cabinets (if necessary) and install <b>{$cabinet->cabinet->frugal_name}</b> Cabinets as per final drawings.<br/>";
        }
        $apps = null;
        $eitems = (isset($meta['meta']['quote_installer_extras'])) ? explode("\n",
            $meta['meta']['quote_installer_extras']) : [];
        $edata = null;
        foreach ($eitems AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[0])) continue;
            $edata .= $idata[0] . "<br/> ";
        }
        $apps .= $edata;
        // Check to see if there are any addons for cabinet installers.
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon && $addon->addon->designation_id == 4)
            {
                $apps .= "<b>Addon: </b>" . sprintf($addon->addon->contract, $addon->qty,
                        $addon->description) . "<br/>";
            }
        }
        $data['sms'] = $cabinetInformation . str_replace("<br/>", "\n", $apps);
        $data['email'] = $cabinetInformation . $apps;
        return $data;
    }

    static public function plumber(JobSchedule $schedule)
    {
        $meta = unserialize($schedule->job->quote->meta);
        $apps = null;
        if (isset($meta['meta']['quote_appliances']))
        {
            foreach ($meta['meta']['quote_appliances'] as $app_id)
            {
                $apps .= "* " . Appliance::find($app_id)->name . "<br/>";
            }
        }
        $plumbing_items = (isset($meta['meta']['quote_plumbing_extras'])) ? explode("\n",
            $meta['meta']['quote_plumbing_extras']) : [];
        $pdata = null;
        foreach ($plumbing_items AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[0])) continue;
            $pdata .= $idata[0] . "<br/> ";
        }
        $apps .= $pdata;
        // Check to see if there are any addons for plumbers.
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon && $addon->addon->designation_id == 1)
            {
                $apps .= "<b>Addon: </b>" . sprintf($addon->addon->contract, $addon->qty,
                        $addon->description) . "<br/>";
            }
        }

        $data['sms'] = "Plumber and Electrican to install the following appliances and Faucet: \n" . str_replace("<br/>",
                "\n", $apps);
        $data['email'] = "Plumber and Electrician to install the following appliances and Faucet: <br/>$apps";
        return $data;
    }

    static public function flooring(JobSchedule $schedule)
    {
        if ($schedule->job->quote->tiles()->count() == 0) return;

        $verbiage = "Flooring and Backsplash Contractor to install the following: <br/><br/>";
        foreach ($schedule->job->quote->tiles as $tile)
        {
            $in = $tile->linear_feet_counter * 12;
            $calc1 = ($in * $tile->backsplash_height) / 144;
            $pattern = $tile->pattern;
            $will = $tile->sealed == 'Yes' ? "will" : "will not";
            $verbiage .= "Install $calc1 sq. feet of customer supplied tile and grout. Tile will be installed in a $pattern design and tile {$will} be sealed.<br/>";
        }

        // Check to see if there are any addons for flooring
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon && $addon->addon->designation_id == 11)
            {
                $verbiage .= "<b>Addon: </b>" . sprintf($addon->addon->contract, $addon->qty,
                        $addon->description) . "<br/>";
            }
        }

        $data['sms'] = str_replace("<br/>", "\n", $verbiage);
        $data['email'] = $verbiage;
        return $data;
    }

    static public function electrician(JobSchedule $schedule)
    {
        $meta = unserialize($schedule->job->quote->meta);
        $apps = null;
        if (isset($meta['meta']['quote_appliances']))
        {
            foreach ($meta['meta']['quote_appliances'] as $app_id)
            {
                $apps .= "* " . Appliance::find($app_id)->name . "<br/>";
            }
        }
        $eitems = (isset($meta['meta']['quote_electrical_extras'])) ? explode("\n",
            $meta['meta']['quote_electrical_extras']) : [];
        $edata = null;
        foreach ($eitems AS $item)
        {
            $idata = explode("-", $item);
            if (!isset($idata[0])) continue;
            $edata .= $idata[0] . "<br/> ";
        }
        $apps .= $edata;
        $meta = $meta['meta'];
        if (isset($meta['quote_led_12']) && $meta['quote_led_12'])
        {
            $apps .= "Install $meta[quote_led_12] x 12 feet of LED Strip Lights\n";
        }
        if (isset($meta['quote_led_60']) && $meta['quote_led_60'])
        {
            $apps .= "Install $meta[quote_led_60] x 60\" of LED Strip Lights\n";
        }
        if (isset($meta['quote_led_connections']) && $meta['quote_led_connections'])
        {
            $apps .= "Install $meta[quote_led_connections] LED Strip Light connections\n";
        }
        if (isset($meta['quote_led_transformers']) && $meta['quote_led_transformers'])
        {
            $apps .= "Install $meta[quote_led_transformers] LED Strip Light transformers\n";
        }
        if (isset($meta['quote_led_couplers']) && $meta['quote_led_couplers'])
        {
            $apps .= "Install $meta[quote_led_couplers] LED Strip Light couplers\n";
        }
        if (isset($meta['quote_led_switches']) && $meta['quote_led_switches'])
        {
            $apps .= "Install $meta[quote_led_switches] LED Strip Light switches\n";
        }
        if (isset($meta['quote_led_feet']) && $meta['quote_led_feet'])
        {
            $apps .= "Install $meta[quote_led_feet] feet of LED Strip Light\n";
        }

        // Check to see if there are any addons for electricians
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon && $addon->addon->designation_id == 2)
            {
                $apps .= "<b>Addon: </b>" . sprintf($addon->addon->contract, $addon->qty,
                        $addon->description) . "<br/>";
            }
        }


        $data['sms'] = "Plumber and Electrican to install the following appliances:\n " . str_replace("<br/>", "\n",
                $apps);
        $data['email'] = "Plumber and Electrician to install the following appliances: <br/>$apps";
        return $data;
    }


    static public function granite(JobSchedule $schedule, $first = false)
    {
        $meta = unserialize($schedule->job->quote->meta);
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        $sinks = null;
        foreach ($meta['meta']['sinks'] AS $sink)
        {
            if (Sink::find($sink))
            {
                $sinks .= Sink::find($sink)->name . "<br/>";
            }
        }
        // Primary Granite.
        if ($first)
        {
            $graniteInfo = "<h4>Granite Template</h4>";
        }
        else
        {
            $graniteInfo = "<h4>Granite Install</h4>";
        }

        foreach ($schedule->job->quote->granites as $granite)
        {
            $graniteInfo .= "<h5><b>$granite->description</b></h5><hr/>";
            $graniteInfo .= "Granite Type: ";
            if ($granite->granite_jo)
            {
                $graniteInfo .= $granite->granite_jo;
            }
            else
            {
                $graniteInfo .= $granite->granite && !$granite->granite_override ? $granite->granite->name : $granite->granite_override;
            }
            $graniteInfo .= "<br/>";
            $graniteInfo .= "Edge: {$granite->counter_edge}<br/>";
            $graniteInfo .= "Counter Measurements: $granite->sqft<br/>";
            $graniteInfo .= "Backsplash Height (in.): $granite->backsplash_height<br/>";
            $graniteInfo .= "Raised Bar (Length): $granite->raised_bar_length<br/>";
            $graniteInfo .= "Raised Bar (Depth): $granite->raised_bar_depth<br/>";
            $graniteInfo .= "Island (Width): $granite->island_width<br/>";
            $graniteInfo .= "Island (Length): $granite->island_length<br/>";
        }
        $graniteInfo .= "<br/><b>Sinks</b>: $sinks";

        // Check to see if there are any addons for cabinet installers.
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon && $addon->addon->designation_id == 3)
            {
                $graniteInfo .= "<b>Addon: </b>" . sprintf($addon->addon->contract, $addon->qty,
                        $addon->description) . "<br/>";
            }
        }

        $data['email'] = $graniteInfo;
        $graniteInfo = str_replace("<br/>", "\n", $graniteInfo);
        $data['sms'] = $graniteInfo;
        return $data;
    }

    static public function getCustomer(JobSchedule $schedule)
    {
        $customer = $schedule->job->quote->lead->customer;
        $contact = $customer->contacts()->first();
        $data['email'] = "<h4>Customer Information:</h4>\n
<p><table border=0 cellpadding=4 width='50%'>
<tr><td align='right'><b>Customer Name: </b></td><td>$customer->name</td></tr>
<tr><td align='right'><b>Address: </b></td><td>$customer->address</td></tr>
<tr><td align='right'><b>City/State/Zip</b>: </td><td>$customer->city $customer->state $customer->zip</td></tr>
<tr><td align='right'><b>Job Address (if different): </b></td><td>$customer->job_address</td></tr>
<tr><td align='right'><b>Job City/State/Zip</b>: </td><td>$customer->job_city $customer->job_state $customer->job_zip</td></tr>

<tr><td align='right'><b>Contact E-mail Address: </b></td><td>$contact->email</td></tr>
<tr><td align='right'><b>Mobile Phone: </b></td><td>$contact->mobile</td></tr>
<tr><td align='right'><b>Home Phone: </b></td><td>$contact->home</td></tr>
</table>";
        $data['sms'] = "
Customer: $customer->name\n";
        return $data;
    }

    static public function sendSMS(JobSchedule $schedule, $data)
    {
        if ($schedule->user)
        {
            $number = $schedule->user->mobile;
            SMS::command(null,
                [
                    'target'  => $number,
                    'message' => $data
                ]);
        }
    }

    static public function sendEmail(JobSchedule $schedule, $data)
    {
        $user = $schedule->user;
        $out['content'] = $data;
        $out['user'] = $user;
        $customer = $schedule->job->quote->lead->customer->name;
        $subject = "[$customer] Frugal Job Schedule";
        Mail::send('emails.contractorSchedule', $out, function ($message) use ($user, $subject, $schedule) {
            $attached = \FrugalFile::whereQuoteId($schedule->job->quote->id)->whereAttached(true)->get();
            foreach ($attached AS $attach)
            {
                $message->attach("files/{$schedule->job->quote->id}/$attach->location");
            }
            $message->to($user->email, $user->name)->subject($subject);
        });

        $emaildata['content'] = "The job schedule has been sent to $customer. Please schedule a walkthrough!";
        // Email rich that the walkthrough has been signed to approve contractors.
        Mail::send('emails.notification', $emaildata, function ($message) use ($customer) {
            $message->to(['kimw@frugalkitchens.com']);
            $message->subject("[$customer] Job Schedules have been sent. Please schedule walkthrough!");
        });

    }


    /**
     * Send job schedules to customer.
     * @param Job $job
     * @param     $html
     */
    static public function sendSchedulesToCustomer(Job $job, $html, $cc = false)
    {
        $data = [];
        $data['job'] = $job;
        $data['content'] = $html;
        $data['noNL'] = true; // Do not nl2br
        $customer = $job->quote->lead->customer;
        $contact = $customer->contacts()->first();
        $subject = "[$contact->name] Frugal Kitchens Job Schedule";
        $rich = User::find(5);
        $designer = $job->quote->lead->user;
        try
        {
            \Log::info("CC: $cc");
            Mail::send('emails.notification', $data, function ($message) use ($contact, $subject, $rich, $designer) {
                $message->to([
                    $contact->email                => $contact->name,
                    'schedules@frugalkitchens.com' => 'Schedules',
                    $designer->email               => $designer->name,
                ])->subject($subject);
            });
            if ($cc)
            {
                Mail::send('emails.notification', $data, function ($message) use ($cc, $subject, $rich, $designer) {
                    $message->to($cc)->subject($subject);
                });
            }
        } catch (Exception $e)
        {
            \Log::info("Failed: " . $e->getMessage());
        }
        $job->schedules_sent = 1;
        $job->save();
        return;
    }

    /**
     * E-mail debeer the granite order.
     * @param Quote $quote
     */
    static public function emailDebeer(Quote $quote)
    {
        $meta = unserialize($quote->meta);
        $details = QuoteGenerator::getQuoteObject($quote);
        if (!isset($details->GTTL))
        {
            return;
        }
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        $sinks = null;
        foreach ($meta['meta']['sinks'] AS $sink)
        {
            if (Sink::find($sink))
            {
                $sinks .= Sink::find($sink)->name . "<br/>";
            }
        }
        $graniteInfo = "<h4>Granite Order</h4>";
        foreach ($quote->granites as $granite)
        {
            $graniteInfo .= "<h5><b>$granite->description</b></h5><hr/>";
            $graniteInfo .= "Granite Type: ";
            $graniteInfo .= $granite->granite && !$granite->granite_override ? $granite->granite->name : $granite->granite_override;
            $graniteInfo .= "<br/>";
            $graniteInfo .= "Edge: {$granite->counter_edge}<br/>";
            $graniteInfo .= "Counter Measurements: $granite->sqft<br/>";
            $graniteInfo .= "Backsplash Height (in.): $granite->backsplash_height<br/>";
            $graniteInfo .= "Raised Bar (Length): $granite->raised_bar_length<br/>";
            $graniteInfo .= "Raised Bar (Depth): $granite->raised_bar_depth<br/>";
            $graniteInfo .= "Island (Width): $granite->island_width<br/>";
            $graniteInfo .= "Island (Length): $granite->island_length<br/>";
        }
        $graniteInfo .= "<br/><br/><b>Sinks</b>: $sinks";
        $customer = "
      <h4>Customer Information</h4>
      {$quote->lead->customer->name}<br/>
      {$quote->lead->customer->address}<br/>
      {$quote->lead->customer->city}, {$quote->lead->customer->state}, {$quote->lead->customer->zip}<br/>
      E-mail: " . $quote->lead->customer->contacts()->first()->email . "<br/>
      Phone Number: " . $quote->lead->customer->contacts()->first()->home . " / " . $quote->lead->customer->contacts()
                ->first()->mobile;
        $data['content'] = $graniteInfo . $customer;
        Mail::send('emails.debeer', $data, function ($message) use ($quote) {
            $message->to(["paula.debeergranite@yahoo.com" => "Paula Debeer"])
                ->cc(['richard@frugalkitchens.com' => "Rich Copy"])
                ->subject("[{$quote->lead->customer->name}] New Granite order from Frugal Kitchens");
        });
    }

    static public function getAddons($schedule, $designation)
    {
        $data = "\n\n";
        foreach ($schedule->job->quote->addons as $addon)
        {
            if ($addon->addon->designation_id == $designation)
            {
                $data .= "[ADDON] " . sprintf($addon->addon->contract, $addon->qty, $addon->description) . "\n";
            }
        }
        return $data;
    }
}