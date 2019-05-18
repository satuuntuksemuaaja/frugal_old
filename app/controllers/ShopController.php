<?php

class ShopController extends BaseController
{
    public $layout = "layouts.main";

    public function index()
    {
        $view = View::make('shop.index');
        $this->layout->title = "Shop Work";
        $this->layout->content = $view;
    }

    /**
     * Create new shop order.
     */
    public function store()
    {
        $job = Job::find(Input::get('job_id'));
        $shop = new Shop();
        $shop->job_id = Input::get('job_id');
        $shop->user_id = Auth::user()->id;
        $shop->active = 1;
        $shop->save();

        foreach ($job->quote->cabinets AS $cabinet)
        {
            $cab = new ShopCabinet();
            $cab->quote_cabinet_id = $cabinet->id;
            $cab->shop_id = $shop->id;
            $cab->save();
        }
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/shop"]);
    }

    public function notes($id, $cabid)
    {
        $cab = ShopCabinet::find($cabid);
        $cab->notes = Input::get('value');
        $cab->save();
        return Response::json(['status' => 'success']);
    }

    public function archive($id)
    {
        $shop = Shop::find($id);
        $shop->active = 0;
        $shop->save();
        // Email status.
        $customer = $shop->job->quote->lead->customer->contacts()->first()->email;
        $custname = $shop->job->quote->lead->customer->contacts()->first()->name;

        $content = Auth::user()->name . " has marked Shop Work Completed for $custname. The following notes have been added.<br/><br/>";
        foreach ($shop->cabinets AS $cabinet)
        {
            $content .= $cabinet->cabinet->cabinet->name . "/" . $cabinet->cabinet->color . " - " . $cabinet->notes . "<br/>";
        }

        $data['content'] = $content;
        Mail::send('emails.notification', $data, function ($message) use ($customer, $custname) {
            $message->to(['punch@frugalkitchens.com']);
            $message->subject("[$custname] Shop Work has been Completed!");
        });

        // If this has a job item then we need to mark it complete.
        if ($shop->job_item_id)
        {
            $shop->jobitem->update([
                'ordered'             => \Carbon\Carbon::now(),
                'confirmed'           => \Carbon\Carbon::now(),
                'received'            => \Carbon\Carbon::now(),
                'verified'            => \Carbon\Carbon::now(),
                'contractor_complete' => 1
            ]);
        }

        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/buildup"]);

    }

    /**
     * Toggle completions.
     * @param $id
     * @param $type
     * @return array
     */
    public function setType($id, $type)
    {
        $item = ShopCabinet::find($id);
        $item->{$type} = \Carbon\Carbon::now();
        $item->save();
        $complete = true;
        foreach ($item->shop->cabinets as $cab)
        {
            if (!$cab->completed)
            {
                $complete = false;
            }
        }
        if ($complete)
            $item->shop->update(['active' => false]);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Delete a cab
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function deleteItem($id)
    {
        ShopCabinet::find($id)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }
}
