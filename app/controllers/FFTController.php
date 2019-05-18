<?php

use Carbon\Carbon;
use vl\core\Google;

class FFTController extends BaseController
{
    public $layout = "layouts.main";

    public function FFTIndex()
    {
        $view = View::make('fft.index');
        $view->warranty = false;
        $this->layout->title = "FFTs";
        $this->layout->content = $view;

    }

    public function changeTime($id, $type)
    {
        $view = View::make('picker');
        $fft = FFT::find($id);
        $text = ($type == 'schedule') ? "Scheduled" : "Pre-Scheduled";
        $view->pre = "Change Schedule $text time.";
        $view->url = "/fft/$fft->id/change/{$type}";
        $view->timevalue = $type == 'schedule' ? $fft->schedule_start->format("h:i a") : $fft->pre_schedule_start->format("h:i a");
        $view->datevalue = $type == 'schedule' ? $fft->schedule_start->format("m/d/Y") : $fft->pre_schedule_start->format("m/d/Y");
        return $view;
    }

    public function changeTimeSave($id, $type)
    {
        $time = Carbon::parse(Input::get('date') . " " . Input::get('time'));
        $fft = FFT::find($id);
        $customer = $fft->customer ? $fft->customer : $fft->job->quote->lead->customer;
        $contact = $customer->contacts()->first();

        if ($type == 'schedule')
        {
            $fft->schedule_start = $time;
        }
        else
        {
            $fft->pre_schedule_start = $time;
        }
        $fft->save();
        if ($type == 'schedule')
        {
            $title = ($fft->warranty) ? "Warranty Scheduled" : "FFT Scheduled";
            $verbiage = ($fft->warranty) ? "A warranty" : "A Frugal's Final Touch";
            $hours = ($fft->warranty) ? null : "Estimated Hours Required to Complete: {$fft->hours}";
            $params = [];
            $params['title'] = "{$title}: {$customer->name}";
            $params['location'] = "{$customer->address}
        {$customer->city}, {$customer->state} {$customer->zip}";
            $params['description'] = "
{$verbiage} has been scheduled for {$customer->name}

Client Information:
{$customer->name}
{$customer->address}
{$customer->city}, {$customer->state} {$customer->zip}
Phone: {$contact->phone}
Mobile: {$contact->mobile}
--------------
{$hours}
Additional Notes: $fft->notes
        ";
            $params['start'] = Carbon::parse($fft->schedule_start);
            $params['end'] = Carbon::parse($fft->schedule_start)->addMinutes(120);
            try
            {
                Google::event($fft->assigned, $params);
            } catch (Exception $e)
            {
                Log::info("Google Calendar Event Failed: " . $e->getMessage());
            }
        }
        else
        {
            if ($customer->job_address)
            {
                $address = "{$customer->job_address}
                {$customer->job_city}, {$customer->job_state}, {$customer->job_zip}";
            }
            else
            {
                $address = "{$customer->address}
                {$customer->city}, {$customer->state}, {$customer->zip}";
            }
            $title = ($fft->warranty) ? "Warranty Pre-Scheduled" : "FFT Pre-Scheduled";
            $verbiage = ($fft->warranty) ? "A warranty" : "A Frugal's Final Touch";
            $params = [];
            $params['title'] = "{$title}: {$customer->name}";
            $params['location'] = $address;
            $params['description'] = "
  {$verbiage} has been pre-scheduled for {$customer->name}

  Client Information:
  {$address}
  Phone: {$contact->phone}
  Mobile: {$contact->mobile}
  ----------------------------
  Additional Notes: $fft->notes

  ";
            $params['start'] = Carbon::parse($fft->pre_schedule_start);
            $params['end'] = Carbon::parse($fft->pre_schedule_start)->addMinutes(120);
            try
            {
                Google::event($fft->preassigned, $params);
            } catch (Exception $e)
            {
                Log::info("Google Calendar Event Failed: " . $e->getMessage());
            }
        }
        // Need to email kim with details.
        $data['content'] = nl2br($params['description']);
        // Email rich that the walkthrough has been signed to approve contractors.
        Mail::send('emails.notification', $data, function ($message) use ($customer, $time) {
            $time = $time->format("m/d/y h:ia");
            $message->to(['kimw@frugalkitchens.com']);
            $message->subject("[$customer->name] Punch has been scheduled for $time, Send Invoice to Customer");
        });


        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function hours($id)
    {
        $fft = FFT::find($id);
        $fft->hours = Input::get('value');
        $fft->save();
        return Response::json(['success' => true]);
    }

    public function close($id)
    {
        $fft = FFT::find($id);
        $fft->closed = 1;
        $fft->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function customerSign($id, $jid)
    {
        $fft = FFT::whereId($id)->whereJobId($jid)->first();
        $view = View::make('fft.signoff');
        $view->customer = true;
        $view->fft = $fft;
        if (!$fft)
        {
            return "No Punch Found";
        }
        $this->layout = View::make('layouts.locked');
        $this->layout->title = "Customer Signature Required";
        $this->layout->content = $view;

    }


    public function sign($id)
    {
        if (!Input::has('output'))
        {
            return "Invalid Signature";
        }
        $order = FFT::find($id);
        $order->signoff = Input::get('output');
        $order->signoff_stamp = Carbon::now();
        $order->save();

        // #22 - Fire off Signature page to Kim, Rich and Customer.
        $outputName = str_random(10);
        $view = View::make('fft.signoff')->withFft($order)->withRaw(true)->render();
        $pdfPath = public_path() . "/uploads" . '/' . $outputName . '.pdf';
        File::put($pdfPath, PDF::load($view, 'A4', 'portrait')->output());
        $data['content'] = "A new signature has been generated via Punch Lists - see attached document";
        $customer = $order->job->quote->lead->customer->contacts()->first()->email;
        $custname = $order->job->quote->lead->customer->contacts()->first()->name;

        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname) {
            $message->to(['punch@frugalkitchens.com']);
            $message->subject("[$custname] A new signed Punch List document has been created!");
            $message->attach($pdfPath);
        });
        $data['content'] = "$custname Walkthrough has been signed. Login to Frugalk and Payouts and approve contractor payouts.";
        // Email rich that the walkthrough has been signed to approve contractors.
        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname) {
            $message->to(['rich@frugalkitchens.com']);
            $message->subject("[$custname] Walkthrough Signed - Contractor Payouts Requiring Approval");
        });


        if (!Auth::check())
        {
            return "Your punch list has been approved. You can close this window now.";
        }
        else
        {
            return Redirect::to("/fft");
        }
    }


    public function signature($id)
    {
        $fft = FFT::find($id);
        if (!$fft->job)
        {
            // This may be a warranty item so we need to look at the customer id and find out what job this was.
            foreach ($fft->customer->quotes AS $quote)
            {
                if ($quote->accepted)
                {
                    $fft->job_id = $quote->job->id;
                    $fft->save();
                }
            }
        }
        if (!$fft->job_id)
        {
            return "I can't find the job for {$fft->customer->name}. Call Chris, this might be on old frugalk.";
        }
        $view = View::make('fft.signature');
        $view->fft = FFT::find($id);
        $this->layout->title = "FFTs";
        $this->layout->content = $view;
    }

    public function signoff($id)
    {
        $view = View::make('fft.signoff');
        $view->fft = FFT::find($id);
        $this->layout->title = "Signoff";
        $this->layout->content = $view;
    }

    public function signoffSave($id)
    {
        if (!Input::has('output'))
        {
            return Redirect::to('/fft');
        }
        $fft = FFT::find($id);
        $fft->signoff = Input::get('output');
        $fft->signoff_stamp = Carbon::now();
        $fft->save();

        // #22 - Fire off Signature page to Kim, Rich and Customer.
        $outputName = str_random(10);
        $view = View::make('fft.signoff')->withFft(FFT::find($id))->withRaw(true)->render();
        $pdfPath = "uploads" . '/' . $outputName . '.pdf';
        File::put($pdfPath, PDF::load($view, 'A4', 'portrait')->output());
        $data['content'] = "Thanks for confirming your Frugal's Final Touch Punch List. Your signoff sheet and signature has been attached to this email.";
        $customer = $fft->job->quote->lead->customer->contacts()->first()->email;
        $custname = $fft->job->quote->lead->customer->name;

        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname) {
            $message->to(['punch@frugalkitchens.com', $customer]);
            //  $message->to(['chris@vocalogic.com']);
            $message->subject("[$custname] Your Frugal's Final Touch Confirmation has been received!");
            $message->attach($pdfPath);
        });
        return Redirect::to("/fft/$id/signoff");
    }

    public function signoffPDF($id)
    {
        $html = View::make('fft.signoff')->withFft(FFT::find($id))->withRaw(true)->render();
        $pdf = new PDF;
        PDF::load($html, 'A4', 'portrait')->download("fft_signoff_$id");
    }

    public function signaturePDF($id)
    {
        $html = View::make('fft.signature')->withFft(FFT::find($id))->withRaw(true)->render();
        $pdf = new PDF;
        PDF::load($html, 'A4', 'portrait')->download("ffft_$id");
    }

    /**
     * Save the signature of the signed off FFT.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signatureSave($id)
    {
        if (!Input::has('output'))
        {
            return Redirect::to('/fft');
        }
        $fft = FFT::find($id);
        $fft->signed = Carbon::now();
        $fft->signature = Input::get('output');
        $fft->save();

        // #22 - Fire off Signature page to Kim, Rich and Customer.
        $outputName = str_random(10);
        $view = View::make('fft.signature')->withFft(FFT::find($id))->withRaw(true)->render();
        $pdfPath = "uploads" . '/' . $outputName . '.pdf';
        File::put($pdfPath, PDF::load($view, 'A4', 'portrait')->output());
        $data['content'] = "A new signature has been generated - see attached document. The following items are being sent to our ordering department. Frugal Kitchens will contact you once the items are received and are ready to be installed. This could take up to 4 weeks. We will call you as soon as we get them.";
        $customer = $fft->job->quote->lead->customer->contacts()->first()->email;
        $custname = $fft->job->quote->lead->customer->name . " ({$fft->job->quote->lead->customer->id})";

        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname) {
            $message->to(['punch@frugalkitchens.com', $customer]);
            $message->subject("[$custname] A new signed Frugal Kitchens document has been created!");
            $message->attach($pdfPath);
        });
        return Redirect::to("/fft/$id/signature");
    }

    public function liveUpdate($id, $item)
    {
        $fft = FFT::find($id);
        switch ($item)
        {
            case 'assigned' :
                $fft->user_id = Input::get('value');
                break;
            case 'preassigned' :
                $fft->pre_assigned = Input::get('value');
                break;
            case 'notes' :
                if ($fft->warranty)
                {
                    $fft->warranty_notes = Input::get('value');
                }
                else
                {
                    $fft->notes = Input::get('value');
                }
                break;
        }
        $fft->save();
        return Response::json(['success' => true]);
    }

    public function warrantyIndex()
    {
        $view = View::make('fft.index');
        $view->warranty = true;
        $this->layout->title = "Warranties";
        $this->layout->content = $view;
    }

    public function serviceIndex()
    {
        $view = View::make('fft.index');
        $view->warranty = false;
        $view->service = true;
        $this->layout->title = "Service Work";
        $this->layout->content = $view;
    }


    /**
     * Create a new warranty.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newService()
    {
        $fft = new FFT;
        $fft->warranty = 0;
        $fft->service = 1;
        $fft->job_id = Input::get('job_id');
        $fft->customer_id = Input::get('customer_id');
        $fft->save();

        if (!$fft->job_id)
        {
            // Try to find a job for this customer.
            $customer = Customer::find(Input::get('customer_id'));
            foreach ($customer->quotes as $quote)
            {
                if ($quote->closed)
                    $fft->update(['job_id' => $quote->job->id]);
            }
        }

        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Create a new warranty.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newWarranty()
    {
        $fft = new FFT;
        $fft->warranty = 1;
        $fft->job_id = Input::get('job_id');
        $fft->customer_id = Input::get('customer_id');
        $fft->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Show new item modal.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function itemsModal($id)
    {
        $fft = FFT::find($id);
        $view = View::make('fft.items');
        $view->fft = $fft;
        return $view;
    }

    /**
     * Create new punch list item.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createItem($id)
    {
        $fft = FFT::find($id);
        if (!Input::has('item'))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to add',
                'gbody'  => 'You must add the item
        before saving'
            ]);
        }
        $item = new JobItem;
        $item->job_id = $fft->job_id;
        if ($fft->warranty) $item->instanceof = "Warranty";
        if ($fft->service) $item->instanceof = "Service";
        if (!$item->instanceof) $item->instanceof = "FFT";
        $item->reference = Input::get('item');
        $item->orderable = (Input::has('orderable')) ? 1 : 0;
        $item->replacement = (Input::has('replacement')) ? 1 : 0;
        if (Input::hasFile('image1'))
        {
            $origname = uniqid() . ".jpg";
            $path = public_path() . "/punchimages";
            Input::file('image1')->move($path, $origname);
            $item->image1 = $origname;
        }
        if (Input::hasFile('image2'))
        {
            $origname = uniqid() . ".jpg";
            $path = public_path() . "/punchimages";
            Input::file('image2')->move($path, $origname);
            $item->image2 = $origname;
        }
        if (Input::hasFile('image3'))
        {
            $origname = uniqid() . ".jpg";
            $path = public_path() . "/punchimages";
            Input::file('image3')->move($path, $origname);
            $item->image3 = $origname;
        }
        $item->save();
        if (Input::has('shop'))
        {
            // Create a job work item with this.
            $item = JobItem::find($item->id);
            $shop = new Shop();
            $shop->user_id = Auth::user()->id;
            $shop->active = 1;
            $shop->job_id = $item->job_id;
            $shop->job_item_id = $item->id;
            $shop->save();
            foreach ($item->job->quote->cabinets AS $cabinet)
            {
                $cab = new ShopCabinet();
                $cab->quote_cabinet_id = $cabinet->id;
                $cab->shop_id = $shop->id;
                $cab->notes = '';
                $cab->save();
            }
        }

        // #368 - If a punch item is added and there is an FFT signoff signature
        // then we need to email punches@frugalkitchens.com
        if ($fft->signature)
        {
            Mail::send('emails.punchwalk', ['fft' => $fft, 'item' => $item], function ($message) use ($fft) {
                $message->to("punch@frugalkitchens.com", "Frugal Kitchens")
                    ->subject("Punch Item Added after Signoff!");
            });
        }
        return Redirect::to("/punches/$id");
    }

    public function emailPunch($id)
    {

        $fft = FFT::find($id);
        $data['fft'] = $fft;
        $customer = $fft->job->quote->lead->customer->contacts()->first()->name;
        $email = $fft->job->quote->lead->customer->contacts()->first()->email;

        Mail::send('emails.punch', ['fft' => $fft], function ($message) use ($fft, $customer, $email) {
            $message->to($email, "Frugal Kitchens")
                ->subject("We have prepared a punch list for your review. Please Read!");
        });
        Mail::send('emails.punch', ['fft' => $fft], function ($message) use ($fft, $customer, $email) {
            $message->to("punch@frugalkitchens.com", "Frugal Kitchens")
                ->subject("We have prepared a punch list for your review. Please Read!");
        });
        return Redirect::to("/punches/$id");

    }

    /**
     * Update item verification in punch list.
     *
     * @param $id
     * @param $item
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function trackItem($id, $item)
    {
        $item = JobItem::find($item);

        if (!$item->orderable)
        {
            $item->verified = Carbon::now();
            $item->save();
            return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "Item Completed!"]);
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
                $what = "Ordered";
                break;
            case 'confirm' :
                $item->confirmed = Carbon::now();
                $what = "Confirmed";
                break;
            case 'receive' :
                $item->received = Carbon::now();
                $what = "Received";
                break;
            case 'verify' :
                $item->verified = Carbon::now();
                $what = "Verified";
                break;
            default:
                $what = "nothing";
        }
        $item->save();
        $now = date("m/d/y");
        $this->checkItemsForCompletion($item->job);
        return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "{$what} on {$now}"]);
    }

    /**
     * Check to see if each orderable item has been received.
     *
     * @param Job $job
     * @internal param FFT $fft
     */
    public function checkItemsForCompletion(Job $job)
    {
        $pass = true;
        foreach ($job->items()
                     ->where('instanceof', 'FFT')
                     ->where('orderable', true)
                     ->get() AS $item)
        {
            if (Carbon::parse($item->verified)->timestamp <= 0)
            {
                $pass = false;
            }
        }
        if ($pass)
        {
            $this->emailPunchCompletion($job);
        }
        return;
    }

    /**
     * Send an email letting everyone know that all items on the job
     * board have been addressed.
     *
     * @param Job $job
     */
    public function emailPunchCompletion(Job $job)
    {
        $customer = $job->quote->lead->customer->contacts()->first()->name;
        Mail::send('emails.itemscomplete', ['job' => $job], function ($message) use ($job, $customer) {
            $message->to("schedules@frugalkitchens.com", "Frugal Schedules")
                ->subject("All orderable punch list items for {$customer} have been received.");
        });
    }

    /**
     * Signifies that payment has been received for the FFT
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payment($id)
    {
        $fft = FFT::find($id);
        $fft->payment = 1;
        $fft->save();
        return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "Received"]);
    }

    /**
     * This function is run via cron. It will check to see if all
     * FFT emails to customers have been sent out 48 hours from the
     * signed date.
     */
    static public function checkMailOuts()
    {
        foreach (FFT::whereOrderedEmail(false)->get() AS $fft)
        {
            if ($fft->signed->timestamp > 0) // Only if signed.
            {
                $target = $fft->signed->addHours(48);
                $now = Carbon::now();
                $diff = $target->diffInHours($now);
                Log::info("$fft->id: Difference is: $diff");
                if ($diff >= 48) // if NOW is after our target fire the email.
                {
                    // Send email.. It's been 48 hours.
                    $fft->ordered_email = 1;
                    $fft->save();
                    try
                    {
                        $customer = $fft->job->quote->lead->customer;
                        Mail::send('emails.ordered', ['customer' => $customer], function ($message) use ($customer) {
                            $contact = $customer->contacts()->first();
                            Log::info("Notifying $contact->email of Ordered Parts");
                            $message->to($contact->email, $contact->name)
                                ->subject("Your Frugal's Final Touch Items Have Been Ordered!");
                        });

                    } catch (Exception $e)
                    {
                        Log::info("Message failed: " . $e->getMessage());
                    }

                }
            } // if timestamp > 0
        } // if email not sent.
    }

    public function toggleOrderable($id)
    {
        $fft = JobItem::find($id);
        $fft->orderable = $fft->orderable ? 0 : 1;
        $fft->save();
        return ['status' => 'success', 'action' => 'selfreload'];
    }

    public function toggleReplacement($id)
    {
        $fft = JobItem::find($id);
        $fft->replacement = $fft->replacement ? 0 : 1;
        $fft->save();
        return ['status' => 'success', 'action' => 'selfreload'];

    }

    public function updateNotes($id)
    {
        $item = JobItem::find($id);
        $item->notes = Input::get('value');
        $item->save();
        if (Input::get('value'))
        {
            Mail::send('emails.item', ['item' => $item], function ($message) use ($item) {
                $message->to("punch@frugalkitchens.com", "Frugal Punches")
                    ->subject("[#{$item->job->id}] Notes have been added to a punch list item!");
            });
        }
        return Response::json(['success' => true]);
    }

    public function updateContractorNotes($id)
    {
        $item = JobItem::find($id);
        $item->contractor_notes = Input::get('value');
        $item->save();
        if (Input::get('value'))
        {
            Mail::send('emails.item', ['item' => $item], function ($message) use ($item) {
                $message->to("punch@frugalkitchens.com", "Frugal Punches")
                    ->subject("[#{$item->job->id}] Contractor Notes Added!");
            });
        }
        return Response::json(['success' => true]);
    }

    /**
     * Manually add an item to a job.
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveItem($id) // $id is job
    {
        $job = Job::find($id);
        $item = new JobItem;
        $item->job_id = $job->id;
        $item->instanceof = "Item";
        $item->reference = Input::get('item');
        $item->orderable = 0;
        $item->replacement = 0;
        $item->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function pay($id)
    {
        $fft = FFT::find($id);
        $paid = Input::get('paid') ? 1 : 0;
        $fft->paid_reason = Input::get('paid_reason');
        $text = $fft->paid ? "PAID" : "UNPAID";
        $data['content'] = "Punch list for {$fft->job->quote->lead->customer->name} has been marked as $text. <br/><br/>The following notes were provided: <b>$fft->paid_reason</b>";
        Mail::send('emails.notification', $data, function ($message) use ($fft, $text) {
            $message->to(['kimw@frugalkitchens.com']);
            $message->subject("[{$fft->job->quote->lead->customer->name}] Punches have been marked as $text!");
        });
        Mail::send('emails.notification', $data, function ($message) use ($fft, $text) {
            $message->to(['punch@frugalkitchens.com']);
            $message->subject("[{$fft->job->quote->lead->customer->name}] Punches have been marked as $text!");
        });
        $fft->paid = $paid;
        $fft->save();
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/"]);
    }

    /**
     * Create shop work from FFT job
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shopFromFFT($id)
    {
        $fft = FFT::find($id);
        $shop = (new Shop)->create([
            'user_id' => Auth::user()->id,
            'active'  => 1,
            'job_id'  => $fft->job_id
        ]);
        foreach ($fft->job->quote->cabinets AS $cabinet)
        {
            $cab = new ShopCabinet();
            $cab->quote_cabinet_id = $cabinet->id;
            $cab->shop_id = $shop->id;
            $cab->save();
        }
        return Redirect::to("/shop");
    }

    public function notes($id)
    {
        $view = View::make('fft.notes');
        $view->fft = FFT::find($id);
        return $view;
    }

    public function notesSave($id)
    {
        $note = new FFTNote();
        $note->fft_id = $id;
        $note->user_id = Auth::user()->id;
        $note->note = Input::get('note');
        $note->save();
        return Response::json(['status' => "success", 'action' => 'selfreload']);
    }

    public function designation($id)
    {
        $view = View::make('fft.designation');
        $view->item = JobItem::find($id);
        return $view;
    }

    public function setDesignation($id)
    {
        $item = JobItem::find($id);
        $item->designation_id = Input::get('designation_id');
        $item->save();
        return Response::json(['status' => "success", 'action' => 'selfreload']);

    }

}