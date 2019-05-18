<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ChangeController extends BaseController
{
    public $layout = "layouts.main";

    /**
     * Show all Change Orders
     */
    public function index()
    {
        $view = View::make('changes.index');
        $this->layout->title = "Change Orders";
        $this->layout->content = $view;
    }

    /**
     * Create View for Change Orders
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create()
    {
        if (!Input::get('job_id'))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Create',
                'gbody'  => "You must select a job."
            ]);
        }

        $order = new ChangeOrder;
        $order->job_id = Input::get('job_id');
        $order->user_id = Auth::user()->id;
        $order->save();
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/change/$order->id"]);
    }

    public function removeSignature($id)
    {
        $order = ChangeOrder::find($id);
        $order->signed = 0;
        $order->signature = '';
        $order->save();
        return Redirect::to('/changes');
    }

    public function view($id)
    {
        $view = View::make('changes.view');
        $view->order = ChangeOrder::find($id);
        $this->layout->title = "Change Order Modifications";
        $this->layout->content = $view;
    }

    public function addItem($id)
    {
        $order = ChangeOrder::find($id);
        $item = new ChangeOrderDetail;
        $item->change_order_id = $id;
        $item->description = Input::get('description');
        $item->price = Input::get('price');
        $item->orderable = Input::get('orderable') == 'Yes' ? 1 : 0;
        $item->user_id = Auth::user()->id;
        $item->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateItem($id, $iid, $field)
    {
        $item = ChangeOrderDetail::find($iid);
        $item->{$field} = Input::get('value');
        $item->save();
        return Response::json(['success' => true]);
    }

    public function signaturePad($id)
    {
        $view = View::make('changes.adminpad');
        $view->order = ChangeOrder::find($id);
        $this->layout->title = "Change Order Signature";
        $this->layout->content = $view;
    }

    public function sign($id)
    {
        if (!Input::has('output'))
        {
            return Redirect::to("/change/$id");
        }
        $order = ChangeOrder::find($id);
        $order->signed = 1;
        $order->signed_on = Carbon::now();
        $order->signature = Input::get('output');
        $order->save();

        // #22 - Fire off Signature page to Kim, Rich and Customer.
        $outputName = str_random(10);
        $view = View::make('changes.view')->withOrder($order)->withRaw(true)->render();
        $pdfPath = public_path() . "/uploads" . '/' . $outputName . '.pdf';
        File::put($pdfPath, PDF::load($view, 'A4', 'portrait')->output());
        $data['content'] = "A new signature has been generated via Change Orders - see attached document";
        $customer = $order->job->quote->lead->customer->contacts()->first()->email;
        $custname = $order->job->quote->lead->customer->contacts()->first()->name;

        Mail::send('emails.notification', $data, function ($message) use ($pdfPath, $customer, $custname)
        {
            $message->to(['punch@frugalkitchens.com']);
            $message->subject("[$custname] A new signed Frugal Kitchens document has been created!");
            $message->attach($pdfPath);
        });
        $this->notifyChangeOrder($order);


        if (!Auth::check())
        {
            return "Your change order request has been approved. You can close this window now.";
        }
        else
        {
            return Redirect::to("/change/$id");
        }
    }

    public function send($id)
    {
        $order = ChangeOrder::find($id);
        $order->sent = 1;
        $order->sent_on = Carbon::now();
        $order->save();
        $data['order'] = $order;
        $customer = $order->job->quote->lead->customer->contacts()->first();

        Mail::send('emails.changerequest', $data, function ($message) use ($customer)
        {
            $message->to(['changeorder@frugalkitchens.com', $customer->email]);
            $message->subject("[$customer->name] (PENDING APPROVAL) A new change order has been requested.");

        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function customerSign($id, $jid)
    {
        $order = ChangeOrder::whereId($id)->whereJobId($jid)->first();
        $view = View::make('changes.adminpad');
        $view->customer = true;
        $view->order = $order;
        if (!$order)
        {
            return "No Change Order Found";
        }
        $this->layout = View::make('layouts.locked');
        $this->layout->title = "Customer Signature";
        $this->layout->content = $view;

    }

    public function close($id)
    {
        $change = ChangeOrder::find($id);
        $change->closed = 1;
        $change->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Decline can be run outside of Authentication so simply return
     * a string.
     *
     * @param $id
     * @return string
     */
    public function decline($id)
    {
        $change = ChangeOrder::find($id);
        $change->declined = 1;
        $change->closed = 1;
        $change->save();
        return "This change order has been declined. Please contact our offices at 770.460.4331 if you need anything else.";
    }

    /**
     * CRON Job to Check to see if signatures have been received
     * for Change orders. Only check change orders that are open and
     * have not been signed.
     */
    static public function dailyCheck()
    {
        $noticeAddress = "shelam@frugalkitchens.com";
        $now = Carbon::now();
        foreach (ChangeOrder::whereClosed(false)->whereSigned(false)->get() as $order)
        {
            $data['order'] = $order;
            if (empty($order->job->quote->lead->customer)) continue;
            $customer = $order->job->quote->lead->customer->contacts()->first();
            $diff = $now->diffInHours($order->sent_on);
            if ($order->sent_on->timestamp == 0) continue;
            if ($diff <= 48 && $diff >= 24)
            {
                Log::info("Sent an email to Sam for $order->id Diff: " . $diff);
                Mail::send('emails.changerequest', $data, function ($message) use ($customer, $order, $noticeAddress)
                {
                    $message->to([$noticeAddress]);
                    $message->subject("[$customer->name] (48 HOUR WARNING) Change Order $order->id for $customer->name has not been signed.");

                });
            }
            elseif ($diff > 0 and $diff <= 24)
            {
                $data['t4'] = true;
                Mail::send('emails.changerequest', $data, function ($message) use ($customer, $noticeAddress)
                {
                    //$message->to($customer->email);
                    $message->subject("[$customer->name] (24 HOUR NOTICE) Your change Order has not been signed!");

                });
                Log::info("24 Hour Warning sent to Customer $order->id ($diff)");
            }
            else
            {
                Mail::send('emails.changerequest', $data, function ($message) use ($customer, $order, $noticeAddress)
                {
                    $message->to([$noticeAddress]);
                    $message->subject("[$customer->name] (> 48 Hours) Change Order ($order->id) for $customer->name has not been signed within 48 hours - Close?");

                });
                Log::info("Past 24 hour mark.. Send to cancel for $order->id ($diff)");
            }
        }



    } // class

    /**
     * Set item ordered by person and set timestamp
     *
     * @param $id
     * @param $iid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderItem($id, $iid)
    {
        $change = ChangeOrder::find($id);
        $item = ChangeOrderDetail::find($iid);
        $item->ordered_by = Auth::user()->id;
        $item->ordered_on = Carbon::now();
        $item->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function delete($id)
    {
        ChangeOrderDetail::find($id)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    /**
     * @param \ChangeOrder $order
     * @param bool         $justArray
     * @return array
     */
    public function notifyChangeOrder(ChangeOrder $order, $justArray = false)
    {
        // Create alerts for change orders that are signed, and have not been ordered 48 hours after they
        // have been signed. This will email orders@frugalkitchens.com
        $itemList = [];
        $customer = $order->job->quote->lead->customer;
        $noticeAddress = "orders@frugalkitchens.com";
            // Customer, Change Order ID, Signed On, Item, Charged
            foreach ($order->items AS $item)
            {
                if (!$item->orderable) continue;

                    $itemList[] = [
                        $customer,
                        $order->id,
                        $order->signed_on->format("m/d/y"),
                        $item->description,
                        "$". number_format($item->price)
                    ];

        } //fe
        if ($justArray) return $itemList;

        if (!empty($itemList))
        {
            $data = [];
            $data['itemList'] = $itemList;
            Mail::send('emails.changerequestOrder', $data, function ($message) use ($customer, $order, $noticeAddress)
            {
                $message->to([$noticeAddress]);
                $message->subject("[$customer->name] Change Order Item Order List");
            });
        }

    }

}