<?php
use Carbon\Carbon;

class PurchaseController extends BaseController
{
    public $layout = "layouts.main";

    public function index()
    {
        $request = app('request');
        if ($request->get('export'))
            return $this->export();
        $view = View::make('pos.index');
        $this->layout->title = "Purchase Orders";
        $this->layout->content = $view;
    }

    /**
     * Purchase Order Exporter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export()
    {
        $data = null;
        $rows = [];
        $rows[] = ['PO #', 'Name', 'Vendor', 'Ordered Date', 'Ship Date', 'Order Type'];
        foreach (Po::whereArchived(false)->get() as $po)
        {
            $rows[] = [
                $po->id,
                $po->customer ? $po->customer->name : "Unknown",
                $po->vendor ? $po->vendor->name : '',
                Carbon::parse($po->submitted)->format("m/d/y"),
                $po->projected_ship,
                $po->type
            ];
        }
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }
        // Next Report
        $data .= "\n\n\n";
        $rows = [];
        $rows[] = ['Item', 'Name', 'Status', 'Order Date', 'Completed', 'Created'];
        foreach (Po::whereArchived(false)->get() as $po)
        {
            foreach ($po->items as $item)
            {
                $rows[] = [
                   str_replace(",", "-", $item->item),
                   $item->po->customer->name,
                   $item->received_by ? "Received on ". Carbon::parse($item->received)->format("m/d/y") : "Not Received",
                   Carbon::parse($item->po->submitted)->timestamp > 0 ? Carbon::parse($item->po->submitted)->format("m/d/y") : "Not Ordered",
                   $item->received_by ? "Yes" : "No",
                   $item->created_at->format("m/d/y h:i a")
                ];
            }
        }
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }



        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pos.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    /**
     * Delete Purchase Order
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function delete($id)
    {
        Po::find($id)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }


    /**
     * When an XML is overridden we need to update the PO to the matching
     * purchase order. Delete all items in that PO, and reconstruct it.
     *
     * @param  Quote $quote [description]
     * @param  QuoteCabinet $cabinet [description]
     * @return null|void [type]                [description]
     * @internal param $oldVendor [description]
     */
    static public function overridePO(Quote $quote, QuoteCabinet $cabinet)
    {
        // First the job.
        $job = $quote->job;
        if (!$job) return null;
        $po = Po::whereJobId($job->id)->whereVendorId($cabinet->cabinet->vendor->id)->whereType('Cabinets')
            ->whereObjectId($cabinet->id)->first();
        if (!$po) return;
        // Now we have the old PO.
        $po->items()->delete();
        // Now all items are gone.
        $cabData = unserialize($cabinet->override);
        foreach ($cabData AS $idx => $cabitem)
        {
            $item = new PoItem;
            $item->po_id = $po->id;
            $item->job_item_id = $cabinet->id;
            if (!isset($cabitem['description'])) $cabitem['description'] = null;
            $item->item = $cabitem['sku'] . " - " . $cabitem['description'];
            $item->qty = $cabitem['qty'];
            $item->user_id = 0;
            $item->save();
        } // each item
        $po->vendor_id = $cabinet->cabinet->vendor_id;
        if (!preg_match('/UPDATED/i', $po->title))
        {
            $po->title = $po->title .= " (UPDATED! New XML)";
        }
        $po->status = 'draft';
        $po->save();
    }

    /**
     * Create Appropriate purchase orders from a job.
     *
     * @param Job $job
     */
    static public function createFromJob(Job $job)
    {
        // We need to be able to create POs for Cabinets, Hardware and Accessories
        $meta = unserialize($job->quote->meta);
        // Step 1: Cabinets
        if (JobItem::whereJobId($job->id)->whereInstanceof('Cabinet')->count() > 0)
        {
            $cabinetBatches = JobItem::whereJobId($job->id)->whereInstanceof('Cabinet')->get();
            foreach ($cabinetBatches AS $cabinets)
            {
                $cabinet = QuoteCabinet::find($cabinets->reference);
                $po = new Po;
                $po->job_id = $job->id;
                $po->customer_id = $job->quote->lead->customer->id;
                $po->title = $cabinet->cabinet->frugal_name . " Cabinet Order for Job: $job->id";
                $po->status = 'draft';
                $po->user_id = 0;
                $po->type = 'Cabinets';
                $po->vendor_id = $cabinet->cabinet->vendor_id;
                $po->object_id = $cabinet->id;
                $po->save();
                self::getNumber($po);

                // Create individual cabinet items for the PO
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
                    $item = new PoItem;
                    $item->po_id = $po->id;
                    $item->job_item_id = $cabinet->id;
                    if (!isset($cabitem['description'])) $cabitem['description'] = null;
                    $item->item = $cabitem['sku'] . " - " . $cabitem['description'];

                    $item->qty = $cabitem['qty'];
                    $item->user_id = 0;
                    $item->save();
                } // each item
            } // fe cabinet order
        } // if cabinets are required.

        // Hardware
        if (isset($meta['meta']['quote_pulls']) || isset($meta['meta']['quote_knobs']))
        {
            $vendorSet = false;
            $po = new Po;
            $po->job_id = $job->id;
            $po->customer_id = $job->quote->lead->customer->id;
            $po->title = "Hardware Order for Job: $job->id";
            $po->status = 'draft';
            $po->user_id = 0;
            $po->type = 'Hardware';
            $po->save();
            self::getNumber($po);

            if (isset($meta['meta']['quote_pulls']))
            {
                foreach ($meta['meta']['quote_pulls'] as $pl => $qty)
                {
                    $hardware = Hardware::find($pl);
                    if (!$hardware) continue;
                    $item = new PoItem;
                    $item->po_id = $po->id;
                    $item->job_item_id = 0;
                    $item->item = "(PULL) " . $hardware->sku . " - " . $hardware->description;
                    $item->qty = $qty;
                    $item->user_id = 0;
                    $item->save();
                    if (!$vendorSet)
                    {
                        $po->vendor_id = $hardware->vendor_id;
                        $po->save();
                        $vendorSet = true;
                    }
                } // fe pull
            } // if pulls
            if (isset($meta['meta']['quote_knobs']))
            {
                foreach ($meta['meta']['quote_knobs'] as $pl => $qty)
                {
                    $hardware = Hardware::find($pl);
                    if (!$hardware) continue;
                    $item = new PoItem;
                    $item->po_id = $po->id;
                    $item->job_item_id = 0;
                    $item->item = "(KNOB) " . $hardware->sku . " - " . $hardware->description;
                    $item->qty = $qty;
                    $item->user_id = 0;
                    $item->save();
                    if (!$vendorSet)
                    {
                        $po->vendor_id = $hardware->vendor_id;
                        $po->save();
                        $vendorSet = true;
                    }
                } //fe knob
            } // if knobs
        } // if pulls OR knobs.

        // Now Accessories.
        $vendorSet = false;
        if (isset($meta['meta']['quote_accessories']) && $meta['meta']['quote_accessories'])
        {
            $po = new Po;
            $po->job_id = $job->id;
            $po->customer_id = $job->quote->lead->customer->id;
            $po->title = "Accessory Order for Job: $job->id";
            $po->status = 'draft';
            $po->user_id = 0;
            $po->type = 'Accessories';
            $po->save();
            self::getNumber($po);
            foreach ($meta['meta']['quote_accessories'] as $acc => $qty)
            {
                $accessory = Accessory::find($acc);
                if (!$accessory) continue;
                if (!$vendorSet)
                {
                    $po->vendor_id = $accessory->vendor_id;
                    $po->save();
                    $vendorSet = true;
                }
                $item = new PoItem;
                $item->po_id = $po->id;
                $item->job_item_id = 0;
                $item->item = "(ACCESSORY) " . $accessory->sku . " - " . $accessory->description;
                $item->qty = $qty;
                $item->user_id = 0;
                $item->save();
            } // fe acc
        } // if acc
    } // fn

    /**
     * Generate new Purchase Order
     *
     * @param Po $po
     */
    static public function getNumber(Po $po)
    {
        $start = 1000;
        $count = PO::whereCustomerId($po->customer_id)->count();
        $number = $start + $count;
        if ($po->customer)
        {

            $add = null;
            if ($po->customer->id < 10)
            {
                $add = '000';
            }
            else
            {
                if ($po->customer->id < 100)
                {
                    $add = '00';
                }
                else
                {
                    if ($po->customer->id < 1000)
                    {
                        $add = '0';
                    }
                }
            }
            $po->number = $add . $po->customer->id . '-' . $number;
        }
        else
        {
            $po->number = '0000-' . $number;
        }
        $po->save();
    }

    /**
     * Create Purchase Order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $po = new Po;
        if (!Input::has('vendor_id') || !Input::has('title'))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Create',
                'gbody'  => 'A vendor must be selected and a description is required.'
            ]);
        }
        $po->user_id = Auth::user()->id;
        $po->customer_id = Input::get('customer_id');
        $po->title = Input::get('title');
        $po->vendor_id = Input::get('vendor_id');
        $po->status = 'draft';
        $po->type = 'Other';

        // Get Job Number if there is one.
        $customer = Customer::find(Input::get('customer_id'));
        foreach ($customer->quotes AS $quote)
        {
            $job = Job::whereQuoteId($quote->id)->first();
            if ($job)
            {
                $po->job_id = $job->id;
            }
        }
        $po->save();
        self::getNumber($po);
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/po/$po->id"]);
    }

    public function view($id)
    {
        $view = View::make('pos.view');
        $view->po = Po::find($id);
        $this->layout->title = "Purchase Order #{$view->po->number}";
        $this->layout->content = $view;
    }

    public function newItem($id)
    {
        $po = Po::find($id);
        $desc = Input::get('item');
        if (Input::get('punch_item_id'))
        {
            $ji = JobItem::find(Input::get('punch_item_id'));
            $desc = $ji->reference;
        }
        $item = new PoItem;
        $item->po_id = $po->id;
        $item->item = $desc;
        $item->user_id = Auth::user()->id;
        $item->qty = Input::get('qty');
        $item->punch = Input::get('punch_item_id') ? true : false;
        $item->job_item_id = Input::get('punch_item_id') ?: 0;
        $item->warranty_id = Input::get('warranty_id') ?: 0;
        $item->fft_id = Input::get('fft_id') ?: 0;
        $item->service_id = Input::get('service_id') ?: 0;

        $item->save();
        if (!empty($ji))
        {
            $ji->po_item_id = $item->id;
            $ji->save();
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function order($id)
    {
        $po = Po::find($id);
        $po->status = 'ordered';
        $po->submitted = Carbon::now();
        $po->save();
        // If there are any items linked then we need to set them as ordered
        foreach ($po->items as $item)
        {
            if ($item->punch)
            {
                $ji = JobItem::find($item->job_item_id);
                $ji->ordered = Carbon::now();
                $ji->save();
            }
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function confirm($id)
    {
        $po = Po::find($id);
        $po->status = 'confirmed';
        $po->save();
        foreach ($po->items as $item)
        {
            if ($item->punch)
            {
                $ji = JobItem::find($item->job_item_id);
                $ji->confirmed = Carbon::now();
                $ji->save();
            }
        }

        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Receive a PO Item.
     *
     * @param $id
     * @param $iid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function receive($id, $iid)
    {
        $item = PoItem::find($iid);
        $item->received = Carbon::now();
        $item->received_by = Auth::user()->id;
        $item->save();
        // Is this item linked to a Punch item? If so we need to update it too.
        if ($item->punch)
        {
            $ji = JobItem::find($item->job_item_id);
            $ji->received = Carbon::now();
            $ji->verified = Carbon::now();
            $ji->save();
        }

        // Check to see if we need to close the PO.
        $po = Po::find($id);
        $close = true;
        foreach ($po->items AS $item)
        {
            if (!$item->received_by)
            {
                $close = false;
            }
        }
        if ($close)
        {
            $po->status = 'complete';
            $po->archived = 1;
            $po->save();
            $this->updateCompletedPunch($po);
            return Response::json(['status' => 'success', 'action' => 'selfreload']);

        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function archive($po)
    {
        $po = Po::find($po);
        $po->archived = 1;
        $po->save();
        return Redirect::to('/pos');
    }

    /**
     * Sends an update notification when ALL purchase orders have been completed.
     *
     * @param Po $po
     */
    public function updateCompletedPunch(Po $po)
    {
        // First let's see how many other POs are tied to this.
        $root = explode("-", $po->number);
        $root = $root[0];
        $pass = true;
        foreach (Po::where('number', 'like', $root . '-%')->get() as $p)
        {
            if (!$p->archived)
            {
                $pass = false;
            }
        }
        if (!$pass) return;

        try
        {
            $customer = $po->job ? $po->job->quote->lead->customer->contacts()
                ->first()->name : $po->customer->contacts()->first()->name;
        } catch (Exception $e)
        {
            $customer = null;
        }

        Mail::send('emails.completedpo', ['po' => $po, 'customer' => $customer], function ($message) use ($po)
        {
            $message->to("schedules@frugalkitchens.com", "Frugal Schedules")
                ->subject("Purchase Order #$po->number has been received.");
        });

    }


    public function changeType($id)
    {
        $po = Po::find($id);
        $po->type = Input::get('value');
        $po->save();
        return Response::json(['success' => true]);
    }

    public function changeInvoice($id)
    {
        $po = Po::find($id);
        $po->company_invoice = Input::get('value');
        $po->save();
        return Response::json(['success' => true]);
    }

    public function changeProjected($id)
    {
        $po = Po::find($id);
        $po->projected_ship = Input::get('value');
        $po->save();
        return Response::json(['success' => true]);
    }

    public function unverify($id)
    {
        $item = PoItem::find($id);
        $item->received_by = 0;
        $item->received = '0000-00-00';
        $item->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function removeItem($id, $iid)
    {
        PoItem::find($iid)->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    /**
     * Command is run from the dailycheck command
     */
    static public function dailyCheck()
    {
        foreach (Po::whereStatus('ordered')->whereArchived(false)->whereEmailed(false)->get() as $po)
        {
            if ($po->vendor && $po->vendor->confirmation_days)
            {
                $days = $po->vendor->confirmation_days;
            }
            else
            {
                $days = 5;
            }
            if (Carbon::parse($po->submitted)->diffInDays() > $days)
            {
                \Log::info("PO #$po->id was ordered " . Carbon::parse($po->submitted)
                        ->diffInDays() . " days ago and is not received.");

                Mail::send('emails.poconfirm', [], function ($message) use ($po)
                {
                    $now = Carbon::now()->format('m/d/y');
                    $x = Carbon::parse($po->submitted)->diffInDays();
                    $message->to("orders@frugalkitchens.com", "Frugal Reports")
                        ->subject("[$now] Purchase Order #{$po->number} Ordered $x days ago. Not Received");
                });
                $po->emailed = 1;
                $po->save();

            }
        }
    }

    /**
     *
     * @param $id
     * @return mixed
     */
    public function spawn($id)
    {
        $po = Po::find($id);
        $child = new Po;
        $child->customer_id = $po->customer_id;
        $child->title = "Replacement/Misc Order (Original #$po->number)";
        $child->user_id = $po->user_id;
        $child->status = 'draft';
        $child->parent_id = $po->id;
        $child->job_id = $po->job_id;
        $child->save();
        self::getNumber($child);
        return Redirect::to("/po/$child->id");
    }


}