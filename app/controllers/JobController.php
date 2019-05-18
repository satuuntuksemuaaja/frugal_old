<?php

use Carbon\Carbon;
use Thujohn\Pdf\Pdf;
use vl\core\ScheduleEngine;
use vl\jobs\JobBoard;
use vl\quotes\QuoteGenerator;

class JobController extends BaseController
{
    public $layout = "layouts.main";

    public function exportForm()
    {
        $view = View::make('jobs.export');
        $this->layout->title = "Job Board Export to Excel";
        $this->layout->content = $view;
    }

    public function buildupNote($id)
    {
        return View::make('jobs.buildup')->withJob(Job::find($id));
    }

    /**
     * Save a buildup note
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function buildupnoteSave($id)
    {
        $job = Job::find($id);
        $note = new BuildupNote();
        $note->user_id = Auth::user()->id;
        $note->job_id = $id;
        $note->note = Input::get('note');
        $note->save();
        $data = [
            'job'  => $job,
            'note' => $note->note
        ];
        // Mail it out
        Mail::send('emails.buildupnote', $data, function ($message) use ($job) {
            $message->to(['orders@frugalkitchens.com']);
            $message->subject("[{$job->quote->lead->customer->name}] (New Buildup Note) From " . Auth::user()->name);

        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function review($id)
    {
        $job = Job::find($id);
        $job->reviewed = 1;
        $job->save();

        // #366 - Send email to customer with attachment of list of items they are responsible for for their job.
        if ($job->quote->responsibilities()->count() > 0)
        {
            $pdf = new PDF;
            $file = uniqid();
            $data = View::make('emails.rpdf')->withJob($job)->render();
            $pdf->load($data, 'A4', 'portrait')->output("/tmp/$file");
            // Render a PDF as as an attachment.
            Mail::send('emails.responsibilities', ['job' => $job], function ($message) use ($job, $file) {
                $message->to($job->quote->lead->customer->contacts->first()->email);
                $message->attach("/tmp/$file.pdf");
                $message->subject("Thank you for choosing Frugal Kitchens and Cabinets!");
            });
            @unlink("/tmp/$file.pdf");
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function arrival($id)
    {
        if (Auth::user()->id != 5)
        {
            return Response::json(['status' => 'success', 'action' => 'selfreload']);
        }

        $job = Job::find($id);
        $job->sent_cabinet_arrival = 1;
        $job->save();
        $customer = $job->quote->lead->customer;
        $contact = $customer->contacts()->first();
        // Now we should probably send it.
        $subject = "[Frugal Kitchens/$customer->name] Your Cabinets are Shipping!";
        $data = [
            'customer' => $customer,
            'contact'  => $contact,
            'content'  => "Hi $contact->name, We wanted to let you know that we have received a tentative ship date. 
            Could you please call us at 770-486-1247 to set up an installation date! 
            <b>Please remember that these dates are approximate</b> and we can’t guarantee the date we set up until we receive the cabinets. 
            If you an exact date we will not be able to schedule anything till we have the product in our possession."
        ];
        Mail::send('emails.notification', $data, function ($message) use ($contact, $subject) {
            $message->to([
                $contact->email             => $contact->name,
                'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject($subject);
        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }


    public function export()
    {
        $jobs = Job::whereClosed(true);

        if (Input::has('start'))
        {
            $start = Carbon::parse(Input::get('start'))->toDateString();
            $jobs = $jobs->where('closed_on', '>=', $start);
        }
        $jobs = $jobs->orderBy('closed_on', 'ASC')->get();
        $data = "Job Closed,Customer,Designer\n";
        foreach ($jobs AS $job)
        {
            if (!$job->quote || !$job->quote->lead || !$job->quote->lead->customer) continue;
            $designer = ($job->quote->lead->user) ? $job->quote->lead->user->name : "Unassigned Designer";
            $data .= Carbon::parse($job->closed_on)->format("m/d/y") . "," . $job->quote->lead->customer->name . "," .
                $designer . "\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="jobexp.csv"',
        ];

        return Response::make($data, 200, $headers);


    }

    public function index()
    {
        if (Auth::user()->level_id == 4)
        {
            return Redirect::to('/leads/');
        }
        $view = View::make('jobs.index');
        $view->jobs = Job::whereClosed(0)->orderBy('start_date', 'DESC')->get();
        $this->layout->title = "Job Board";
        $this->layout->content = $view;
    }

    public function markPaid($id)
    {
        $job = Job::find($id);
        $job->paid = 1;
        $job->save();
        return ['success', 'action' => 'reassign', 'message' => 'Archived!'];
    }

    public function lockToggle($id)
    {
        $schedule = JobSchedule::find($id);
        $schedule->locked = ($schedule->locked) ? 0 : 1;
        $schedule->save();
        $icon = ($schedule->locked) ? "lock" : "unlock";
        return Response::json([
            'status'  => 'success',
            'action'  => 'reassign',
            'message' => "<i class='fa fa-{$icon}'></i>"
        ]);
    }

    public function changeTime($id, $type)
    {
        $view = View::make('picker');
        $schedule = JobSchedule::find($id);
        $view->pre = "Change Schedule {$type} time.";
        $view->url = "/schedule/$schedule->id/change/{$type}";
        $view->timevalue = $schedule->{$type}->format("h:i a");
        $view->datevalue = $schedule->{$type}->format("m/d/Y");

        if (!$schedule->user)
        {
            $view->fail = "You must select a contractor before selecting a date.";
        }
        return $view;
    }

    public function changeTimeSave($id, $type)
    {
        $time = Carbon::parse(Input::get('date') . " " . Input::get('time'));
        $schedule = JobSchedule::find($id);
        $schedule->{$type} = $time;
        if ($type == 'start')
        {
            $schedule->end = $time->format("Y-m-d") . " 18:00:00";
        }
        $schedule->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function defaultEmail($id)
    {
        $schedule = JobSchedule::find($id);
        $schedule->default_email = ($schedule->default_email) ? 0 : 1;
        $schedule->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateReference($id)
    {
        $item = JobItem::find($id);
        $item->reference = Input::get('value');
        $item->save();
        return Response::json(['success' => true]);
    }


    public function createAuxSchedule($id)
    {
        $schedule = new JobSchedule;
        $schedule->job_id = $id;
        $schedule->aux = 1;
        $schedule->locked = 1;
        $schedule->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function deleteItem($id)
    {
        JobItem::find($id)->delete();
        return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => 'Deleted!']);
    }

    public function construction($id)
    {
        $job = Job::find($id);
        $job->construction = 1;
        $job->save();
        return Response::json([
            'status'  => 'success',
            'action'  => 'reassign',
            'message' => "<i class='fa fa-check text-success'></i>"
        ]);
    }

    public function startsForm($id)
    {
        $view = View::make('picker');
        $job = Job::find($id);
        $view->datevalue = Carbon::parse($job->start_date)->format('m/d/Y');
        $view->pre = "Select a Start Date";
        $view->url = "/job/$job->id/starts";
        return $view;
    }

    public function starts($id)
    {
        $job = Job::find($id);
        $int = strtotime(Input::get('date') . " " . Input::get('time'));
        $date = date("Y-m-d", $int);
        $job->start_date = $date;
        $job->save();
        JobSchedule::whereJobId($job->id)->whereLocked(false)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function close($id)
    {
        $job = Job::find($id);
        $job->closed = 1;
        $job->closed_on = Carbon::now();
        $job->save();
        $exists = FFT::whereJobId($job->id)->count();
        if ($exists == 0)
        {
            $fft = new FFT;
            $fft->job_id = $job->id;
            $fft->warranty = 0;
            $fft->save();
        }

        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => '/jobs']);
    }

    public function getMoney($id)
    {
        $job = Job::find($id);
        $job->has_money = 1;
        $job->save();
        return Response::json([
            'status'  => 'success',
            'action'  => 'reassign',
            'message' =>
                "<i class='text-success fa fa-check'></i>"
        ]);
    }

    public function notes($id)
    {
        $view = View::make('jobs.notes');
        $view->job = Job::find($id);
        return $view;

    }

    public function notesSave($id)
    {
        $job = Job::find($id);
        $note = new JobNote();
        $note->job_id = $id;
        $note->user_id = Auth::user()->id;
        $note->note = Input::get('note');
        $note->save();
        return Response::json(['status' => "success", 'action' => 'selfreload']);
    }

    public function track($id, $type, $reference)
    {
        $view = View::make('jobs.trackModal');
        $view->job = Job::find($id);
        $view->type = $type;
        $view->reference = $reference;
        return $view;
    }

    public function trackSave($id, $type, $reference)
    {
        $pass = true;
        $item = JobItem::whereJobId($id)->whereInstanceof($type)->whereReference($reference)->first();
        if (!$item)
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Update',
                'gbody'  => 'No record found.'
            ]);
        }
        $state = 'complete';
        if ($item->verified == '0000-00-00')
        {
            $state = 'verify';
        }
        if ($item->received == '0000-00-00')
        {
            $state = 'receive';
        }
        if ($item->confirmed == '0000-00-00')
        {
            $state = 'confirm';
        }
        if ($item->ordered == '0000-00-00')
        {
            $state = 'order';
        }
        switch ($state)
        {
            case 'order' :
                $item->ordered = Carbon::now();
                break;
            case 'confirm' :
                $item->confirmed = Carbon::now();
                break;
            case 'receive' :
                $item->received = Carbon::now();
                break;
            case 'verify' :
                $item->verified = Carbon::now();
                break;
        }

        if ($state == 'verify')
        {
            if ($item->instanceof == 'Cabinet')
            {
                $itemmeta = unserialize($item->meta);
                if (!is_array($itemmeta))
                {
                    $itemmeta = [];
                }
                $cabinet = QuoteCabinet::find($reference);
                if ($cabinet->override)
                {
                    $cabData = unserialize($cabinet->override);
                }
                else
                {
                    $cabData = unserialize($cabinet->data);
                }
                foreach ($cabData AS $idx => $cabitem)
                {
                    if (!array_key_exists($idx, $itemmeta)) $pass = false;
                }

            } // if cab

            if ($item->instanceof == 'Hardware')
            {
                $pass = true;
                $itemmeta = unserialize($item->meta);
                if (!is_array($itemmeta))
                {
                    $itemmeta = [];
                }
                if (isset($itemmeta['meta']['quote_pulls']))
                {
                    foreach ($itemmeta['meta']['quote_pulls'] as $pl => $qty)
                    {
                        if ($qty)
                        {
                            if (!array_key_exists($pl, $itemmeta)) $pass = false;
                        }
                    }
                }


                if (isset($itemmeta['meta']['quote_knobs']))
                {
                    foreach ($itemmeta['meta']['quote_knobs'] as $pl => $qty)
                    {
                        if ($qty)
                        {
                            if (!array_key_exists($pl, $itemmeta)) $pass = false;
                        }
                    }
                }
            } //hardware
            if ($item->instanceof == 'Accessory')
            {
                $itemmeta = unserialize($item->meta);
                if (!is_array($itemmeta))
                {
                    $itemmeta = [];
                }
                if (isset($itemmeta['meta']['quote_accessories']))
                {
                    foreach ($itemmeta['meta']['quote_accessories'] as $acc => $qty)
                    {
                        if (!array_key_exists($acc, $itemmeta)) $pass = false;
                    }
                }
            }// acc
        } // if verify
        if (!$pass)
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Save',
                'gbody'  => 'Items are not verified.'
            ]);
        }
        else
        {
            $item->save();
            return Response::json(['status' => 'success', 'action' => 'selfreload']);
        }

    }

    public function items($id)
    {
        $view = View::make('jobs.itemsModal');
        $view->job = Job::find($id);
        return $view;
    }

    public function verifyItem($id)
    {
        $item = JobItem::find($id);
        $item->verified = Carbon::now();
        $item->save();
        return Response::json([
            'status'  => 'success',
            'action'  => 'reassign',
            'message' => 'Verified on ' . Carbon::now()
        ]);
    }


    public function schedules($id)
    {
        $view = View::make('jobs.schedules');
        $view->job = Job::find($id);
        $this->layout->title = "Job Schedule";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/jobs', 'text' => 'Jobs'],
            ['url' => "/quote/{$view->job->quote->id}/view", 'text' => $view->job->quote->lead->customer->name],
            ['url' => '#', 'text' => "View Schedules"]
        ];
    }

    public function createSchedule($id, $sid, $day, $designation)
    {
        $job = Job::find($id);
        $schedule = ($sid > 0) ? JobSchedule::find($sid) : new JobSchedule;

        if (!$schedule->start)
        {
            $jobStart = Carbon::parse($job->start_date)->timestamp;
            $startDay = JobBoard::getDateForDay($jobStart, $day);
            switch ($day)
            {
                case 1 :
                    $startTime = ($designation == 8) ? "06:00:00" : "08:00:00";
                    $endTime = ($designation == 8) ? "08:00:00" : "18:00:00";
                    break;
                case 2 :
                    $startTime = ($designation == 4) ? "08:00:00" : "16:00:00";
                    $endTime = ($designation == 4) ? "18:00:00" : "18:00:00";
                    break;
                case 4 :
                    $startTime = "10:00:00";
                    $endTime = "18:00:00";
                    break;
                case 5 :
                    $startTime = "08:00:00";
                    $endTime = "12:00:00";
                    break;
                case 6 :
                    $startTime = "08:00:00";
                    $endTime = "12:00:00";
                    break;
                case 7 :
                    $startTime = "08:00:00";
                    $endTime = "12:00:00";
                    break;
                case 8 :
                    $startTime = "08:00:00";
                    $endTime = "12:00:00";
                    break;

            } // switch
            $start = date("m/d/y", $startDay) . " " . $startTime;
            $end = date("m/d/y", $startDay) . " " . $endTime;
            $schedule->designation_id = $designation;
            if ($designation == 5)
            {
                $schedule->customer_notes = "Go over project with foreman and address any concerns you might have.";
            }
            $schedule->start = Carbon::parse($start);
            $schedule->end = Carbon::parse($end);
            $schedule->user_id = Input::get('value');
        } // no schedule start
        else
        {
            $schedule->user_id = Input::get('value');
        }
        $schedule->job_id = $job->id;
        $schedule->save();
        return Response::json(['success' => true]);
    }

    public function scheduleDate($id, $method)
    {
        $schedule = JobSchedule::find($id);
        switch ($method)
        {
            case 'start' :
                $schedule->start = date("Y-m-d H:i:s", strtotime(Input::get('value')));
                break;
            case 'end' :
                $schedule->end = date("Y-m-d H:i:s", strtotime(Input::get('value')));
                break;
            case 'contractor' :
                $schedule->user_id = Input::get('value');
                $schedule->designation_id = User::find(Input::get('value'))->designation_id;
            case 'notes' :
                $schedule->notes = Input::get('value');
                break;
            case 'customer_notes' :
                $schedule->customer_notes = Input::get('value');
                break;
                break;
        }
        $schedule->save();
        return Response::json(['success' => true]);
    }

    public function delete($id)
    {
        $job = Job::find($id);
        $job->fft->delete();
        $job->quote->accepted = 0;
        $job->quote->closed = 0;
        $job->quote->save();
        $job->quote->lead->status_id = 10;
        $job->quote->lead->save();
        $job->schedules()->delete();
        $job->delete();

        return Redirect::to("/jobs");
    }

    public function scheduleSend($id)
    {
        $schedule = JobSchedule::find($id);
        ScheduleEngine::send($schedule);
        switch ($schedule->designation_id)
        {
            case 3 :
                $allSets = JobSchedule::whereJobId($schedule->job->id)->whereDesignationId($schedule->designation->id)
                    ->get();
                foreach ($allSets AS $set)
                {
                    $set->sent = 1;
                    $set->save();
                }
        }
        $schedule->sent = 1;
        $schedule->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function xml($id)
    {
        $job = Job::find($id);
        $view = View::make('jobs.xml');
        $view->job = $job;
        return $view;
    }

    public function xmlSave($id)
    {
        $job = Job::find($id);
        foreach (Input::all() AS $key => $val)
        {
            if (preg_match("/c_/", $key))
            {
                $id = trim(str_replace("c_", null, $key));
                $file = Input::file("cabinet_{$id}");
                if ($file)
                {
                    $xml = file_get_contents($file->getRealPath());
                    QuoteGenerator::setCabinetData($job->quote, $xml, $id);
                }
            }
        }
        return Redirect::to('/jobs');
    }

    public function sendToCustomer($id)
    {
        $view = View::make('jobs.send');
        $view->job = Job::find($id);
        $this->layout->title = "Send Job To Customer";
        $this->layout->content = $view;
    }

    /**
     * Send the email to the customer with the schedules.
     *
     * @param $id
     * @return null
     */
    public function finalSend($id)
    {

        if (Input::has('user_id') && Input::get('user_id') > 0)
        {
            $email = User::find(Input::get('user_id'))->email;
        }
        else
        {
            $email = Input::get('email');
        }
        ScheduleEngine::sendSchedulesTocustomer(Job::find($id), Input::get('body'), $email);
        return null;
    }

    public function verifyIndex($id, $idx)
    {
        $item = JobItem::find($id);
        $meta = unserialize($item->meta);
        $meta[$idx] = '1';
        $item->meta = serialize($meta);
        $item->save();

        // Check all indexes.
        $alls = unserialize($item->meta);
        $pass = false;
        if ($pass)
        {
            $item->verified = Carbon::now();
            $item->save();
            $this->emailItemCompletion($item->job);
            return Response::json(['status' => 'success', 'action' => 'selfreload']);
        }
        return Response::json(['status' => 'succes', 'action' => 'reassign', 'message' => 'Verified!']);
    }

    public function unlock($id)
    {
        $job = Job::find($id);
        $job->locked = 0;
        $job->save();
        return Redirect::to('/jobs');
    }

    public function createItem($id, $type)
    {
        $item = new JobItem;
        $item->job_id = $id;
        $item->instanceof = $type;
        $item->reference = 1;
        $item->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function scheduleDelete($id)
    {
        JobSchedule::find($id)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function scheduleClose($id)
    {
        $view = View::make('jobs.closeModal');
        $view->schedule = JobSchedule::find($id);
        return $view;
    }

    public function scheduleCloseSave($id)
    {
        $schedule = JobSchedule::find($id);
        $schedule->contractor_notes = Input::get('notes');
        $schedule->complete = 1;
        $schedule->save();
        \vl\jobs\JobBoard::checkSchedulesForClosing($schedule->job);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * CRON Job to Check to see if signatures have been received
     * for Change orders. Only check change orders that are open and
     * have not been signed.
     */
    static public function dailyCheck()
    {
        $now = Carbon::now();
        foreach (Job::whereSchedulesSent(true)->get() as $job)
        {
            // Only if we have a start date.
            if (Carbon::parse($job->start_date)->timestamp == 0) continue;
            // If job is closed then continue.
            if ($job->closed) continue;
            // If schedule has already been signed..
            if ($job->schedules_confirmed) continue;

            $data['job'] = $job;
            $customer = $job->quote->lead->customer->contacts()->first();
            $diff = $now->diffInHours($job->schedule_sent_on);
            // Check to make sure it's not a long way away either way.
            if ($diff > 1000) continue;

            if ($diff <= 48 && $diff >= 24)
            {
                Log::info("Sent an email to Sam for Job: $job->id Diff: " . $diff);
                Mail::send('emails.scheduleUnsigned', $data, function ($message) use ($customer, $job) {
                    $message->to(['shelam@frugalkitchens.com']);
                    $message->subject("[$customer->name] (48 HOUR WARNING) Job #: $job->id ($customer->name) schedules not confirmed.");

                });
            }
            elseif ($diff > 0 and $diff <= 24)
            {
                $data['t4'] = true;
                Mail::send('emails.scheduleUnsigned', $data, function ($message) use ($customer) {
                    $message->to($customer->email);
                    $message->subject("[$customer->name] (24 HOUR NOTICE) Your Frugal Kitchens Schedule has not been confirmed!");

                });
                Log::info("24 Hour Warning sent to Customer $job->id ($diff)");
            }
            else
            {
                Mail::send('emails.scheduleUnsigned', $data, function ($message) use ($customer, $job) {
                    $message->to(['shelam@frugalkitchens.com']);
                    $message->subject("[$customer->name] (> 48 Hours) Job ($job->id) for $customer->name schedule has not been confirmed within 48 hours - Close?");

                });
                Log::info("Past 24 hour mark.. Send to cancel for $job->id ($diff)");
            }
        }
    }

    /**
     * Download PDF of Checklist
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function checklist($id)
    {
        $data = View::make('jobs.checklist')->withJob(Job::find($id))->render();
        $pdf = new PDF;
        $pdf->load($data, 'A4', 'portrait')->download("job_checklist_$id");
    }

    public function contractorComplete($id)
    {
        $item = JobItem::find($id);
        $item->contractor_complete = 1;
        $item->save();
        return ['status' => 'success', 'action' => 'selfreload'];
    }

    /**
     * Show the authorization page editor.
     * @param $id
     */
    public function auth($id)
    {
        $view = View::make('jobs.auth')->withJob(Job::find($id));
        $this->layout->title = "Customer Job Authorizations";
        $this->layout->content = $view;
    }

    public function newAuth($id)
    {
        $job = Job::find($id);
        $auth = $job->authorization;
        $item = new AuthorizationItem();
        $item->authorization_id = $auth->id;
        if (Input::get('auth_id'))
        {
            $item->item = Input::get('auth_id');
        }
        else $item->item = Input::get('description');
        $item->save();
        return ['status' => 'success', 'action' => 'selfreload'];
    }

    /**
     * Show form for authorizations to have customer sign.
     * @param $id
     */
    public function authSign($id)
    {
        $view = View::make('jobs.authsign')->withJob(Job::find($id));
        $this->layout->title = "Customer Job Authorizations";
        $this->layout->content = $view;
    }

    public function authSignSave($id)
    {
        if (!Input::has('output'))
        {
            return "Invalid Signature";
        }
        $auth = Job::find($id)->authorization;
        $auth->signature = Input::get('output');
        $auth->signed_on = Carbon::now();
        $auth->save();

        // #22 - Fire off Signature page to Kim, Rich and Customer.
        $outputName = str_random(10);
        $view = View::make('jobs.authsign')->withJob($auth->job)->withRaw(true)->render();
        $pdfPath = public_path() . "/uploads" . '/' . $outputName . '.pdf';
        $pdf = new PDF;
        File::put($pdfPath, $pdf->load($view, 'A4', 'portrait')->output());
        $data['content'] = "A new job authorization has been signed.";
        $customer = $auth->job->quote->lead->customer->contacts()->first()->email;
        $custname = $auth->job->quote->lead->customer->contacts()->first()->name;
        // #364 - Add designer.
        $job = Job::find($id);
        $designer = $job->quote->lead->user;

        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname, $designer) {
            $message->to(['punch@frugalkitchens.com', $designer->email]);
            $message->subject("[$custname] A new job authorization form has been signed!");
            $message->attach($pdfPath);
        });

        if (!Auth::check())
        {
            return "Thank you for signing. You can close this window now.";
        }
        else
        {
            return Redirect::to("/jobs");
        }
    }

    /**
     * Delete Item
     * @param $id
     * @param $aid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authDelete($id, $aid)
    {
        AuthorizationItem::find($aid)->delete();
        return Redirect::to("/job/{$id}/auth");
    }

    /**
     * Send email to customer.
     * @param $id
     */
    public function authSend($id)
    {
        $auth = Job::find($id)->authorization;
        $data['auth'] = $auth;
        $customer = $auth->job->quote->lead->customer->contacts()->first()->name;
        $email = $auth->job->quote->lead->customer->contacts()->first()->email;

        Mail::send('emails.jobauth', ['auth' => $auth], function ($message) use ($auth, $customer, $email) {
            $message->to($email, "Frugal Kitchens")
                ->subject("Prior Authorization Required - PLEASE READ!");
        });
        return ['status' => 'success', 'action' => 'selfreload'];
    }

    /**
     * Remove signature.
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authRemoveSig($id)
    {
        $job = Job::find($id);
        $auth = $job->authorization;
        $auth->signature = '';
        $auth->save();
        return Redirect::to("/job/{$id}/auth");
    }

    public function picked($id)
    {
        $job = Job::find($id);
        $job->quote->picked_slab = 1;
        $job->quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }
}