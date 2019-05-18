<?php
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 11/19/16
 * Time: 4:59 PM
 */
class PayoutController extends BaseController
{
    public $layout = "layouts.main";

    /**
     * Show payout manager index.
     */
    public function index()
    {
        /*foreach (FFT::whereClosed(false)->where('pre_assigned', '>', 0)->get() as $fft)
        {
            $job = Job::find($fft->job_id);
            if ($job)
            {
                $job->paid = 0;
                $job->save();
            }
        }*/

        $view = View::make('payouts.index')->render();
        $this->layout->title = "Payout Manager";
        $this->layout->content = $view;
    }


    /**
     * Create A new Payout
     */
    public function create()
    {
        $job = Job::find(Input::get('withJob'));
        $view = View::make('payouts.create')->withJob($job)->render();
        $this->layout->title = "Create Payout Entry";
        $this->layout->content = $view;
    }

    public function store()
    {
        $job = Job::find(Input::get('job_id'));
        if ($job->payout_additionals)
        {
            $adds = unserialize($job->payout_additionals);
        }
        else $adds = [];
        $payout = new Payout();
        $payout->job_id = Input::get('job_id');
        $payout->designation_id = Input::get('designation_id');
        $payout->save();
        $adds[] = [$payout->id];
        $adds = serialize($adds);
        $job->payout_additionals = $adds;
        $job->save();
        return Redirect::to("/payouts/$payout->id");
    }

    public function destroy($id)
    {
        PayoutItem::wherePayoutId($id)->delete();
        $payout = Payout::find($id)->delete();
        return Redirect::to('/payouts');
    }

    /**
     * Show payout manager index.
     */
    public function show($id)
    {
        $payout = Payout::find($id);
        if (Input::has('delete'))
        {
            $i = PayoutItem::find(Input::get('delete'));
            $payout->total = $payout->total - $i->amount;
            $payout->save();
            $i->delete();

            return Redirect::to("/payouts/$id");
        }
        if (Input::has('approve'))
        {
            $customer = $payout->job->quote->lead->customer->contacts()->first()->email;
            $custname = $payout->job->quote->lead->customer->contacts()->first()->name;
            $data['content'] = "The payout for {$payout->user->name} for $custname has been approved. Please cut a check and record the check number.";
            Mail::send('emails.notification', $data, function ($message) use ($payout, $customer, $custname)
            {
                $message->to(['kimw@frugalkitchens.com']);
                $message->subject("[$custname] Payment to {$payout->user->name} has been approved.");
            });
            $payout->approved = 1;
            $payout->save();
            return Redirect::to("/payouts#{$payout->job->id}");
        }
        $view = View::make('payouts.show')->with('payout', $payout)->render();
        $this->layout->title = $payout->user ? $payout->user->name : "No User Assigned";
        $this->layout->content = $view;
    }

    /**
     * Update payout information.
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        $payout = Payout::find($id);
        if (Input::has('items'))
        {
            $item = Input::get('itemid') ? PayoutItem::find(Input::get('itemid')) : new PayoutItem();
            $oldAmount = $item->amount;
            $item->payout_id = $id;
            $item->item = Input::get('item');
            $item->amount = Input::get('amount');
            $item->save();
            // Take the Difference and apply it to total
            if ($oldAmount < $item->amount) // The old was 100 the new is 200
            {
                $diff = $item->amount - $oldAmount;
            }
            else  // Old was 200 new is 100
            {
                $diff = $oldAmount - $item->amount;
                $diff = $diff * -1;
            }
            $payout->total = $payout->total + $diff;
            $payout->save();
            return Redirect::to("/payouts/$payout->id");

        }
        $payout->total = Input::get('total');
        $payout->invoice = Input::get('invoice');
        $payout->notes = Input::get('notes');
        if (Input::get('user_id'))
            $payout->user_id = Input::get('user_id');
        if (Auth::user()->id == 1 || Auth::user()->id == 5 || Auth::user()->id == 7)
        {
            if ($payout->approved != Input::get('approved') && Input::get('approved'))
            {
                $customer = $payout->job->quote->lead->customer->contacts()->first()->email;
                $custname = $payout->job->quote->lead->customer->contacts()->first()->name;
                $data['content'] = "The payout for {$payout->user->name} for $custname has been approved. Please cut a check and record the check number.";

                Mail::send('emails.notification', $data, function ($message) use ($payout, $customer, $custname)
                {
                    $message->to(['kimw@frugalkitchens.com']);
                    $message->subject("[$custname] Payment to {$payout->user->name} has been approved.");
                });
            }
            $payout->approved = Input::get('approved') == 1 ? 1 : 0;
        }
        if (Input::get('paid'))
        {
            if (!Input::get('check'))
            {
                return Redirect::to("/payouts/$payout->id")->withError("No Check Found to Mark as Paid");
            }
            $payout->paid = 1;
            $payout->paid_on = Carbon::now();
        }
        else $payout->paid = 0;
        $payout->check = Input::get('check');
        $payout->save();

        // If all are paid then mark this job paid and archive this
        $ok = true;
        foreach (Payout::whereJobId($payout->job_id)->get() as $pay)
        {
            if (!$pay->paid) $ok = false;
        }
        if ($ok)
        {
            $job = Job::find($payout->job_id);
          //  $job->paid = 1;
            $job->save();
        }
        return Redirect::to("/payouts?#{$payout->job->id}");
    }

    public function report($id)
    {
        if (empty($id))
        {
            return Redirect::to("/payouts")->withError("No user to create a report for.");
        }
        $view = View::make('payouts.report')->withUser(User::find($id))->render();
        $this->layout->title = "Create Payout Report";
        $this->layout->content = $view;
    }

    /**
     * Generate a CSV report of the user.
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReport($id)
    {
        $start = Carbon::now();
        $user = User::find($id);
        $start = $start->subDays(7);
        $rows[] = ['Customer', 'Job Date', 'Paid On', 'Check Number', 'Amount', 'Items'];
        $req = app('request');
        $inc = [];
        foreach ($req->all() as $req => $id)
        {
            if (preg_match('/p_/', $req))
            {
                $r = str_replace("p_", null, $req);
                $inc[] = $r;
            }
        }
        foreach (Payout::whereIn('id', $inc)->get() as $payout)
        {
            $rows[] = [
              $payout->job->quote->lead->customer->name,
              $payout->job->start_date,
              $payout->paid_on,
              $payout->check,
              $payout->total,
              null
            ];
            foreach ($payout->items as $item)
            {
                $rows[] = [
                    null,null,null,null,null,
                    strip_tags(str_replace(",", null, $item->item)) . " - " . "$" . $item->amount
                ];
            }
        }
        $data = null;
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-'.$user->name.'.csv"',
        ];

        return Response::make($data, 200, $headers);

    }
}