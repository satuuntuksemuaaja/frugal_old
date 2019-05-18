<?php

use Carbon\Carbon;

class ReportController extends BaseController
{
    public $layout = "layouts.main";

    public function index()
    {
        $view = View::make('reports.index');
        $this->layout->title = "Reports";
        $this->layout->content = $view;
    }

    public function saveDate()
    {
        if (!Input::has('start'))
        {
            Session::forget('start');
        }
        else Session::put('start', Input::get('start'));
        if (!Input::has('end'))
        {
            Session::forget('end');
        }
        else Session::put('end', Input::get('end'));
        Session::put('rtype', Input::get('rtype'));
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function cabinets()
    {
        $view = View::make('reports.cabinets');
        $this->layout->title = "Cabinet Reports";
        $this->layout->content = $view;
    }

    public function designers()
    {
        $view = View::make('reports.designers');
        $this->layout->title = "Designer Report";
        $this->layout->content = $view;
    }

    public function sold()
    {
        $view = View::make('reports.sold');
        $this->layout->title = "Sold Report";
        $this->layout->content = $view;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportLeads()
    {
        $leads = Lead::all();

        $data = "Name,Address,City,State,Zip,Home Phone,Mobile Phone,E-mail,Created On,Status,Sold On,Lead Source,Price\n";
        foreach ($leads AS $lead)
        {
            if (!$lead->customer) continue;
            $customer = $lead->customer;
            $contact = $customer->contacts()->first();
            $created = $lead->created_at->format("m/d/y");
            $status = $lead->status ? $lead->status->name : "Not Set";
            $sold = "N/A";
            if ($lead->quotes()->whereAccepted(true)->first())
            {
                $quote = $lead->quotes()->whereAccepted(true)->first();
                if ($quote->job)
                    $sold = Carbon::parse($quote->job->contract_date)->format("m/d/y");
            }
            if ($lead->source)
                $source = $lead->source->type;
            else
                $source = "Unknown Source";
            $price = $lead->quotes()->whereFinal(1)->count() > 0 ? $lead->quotes()->whereFinal(1)->first()->finance_total : 0;
            $contact->name = str_replace(",", null, $contact->name);
            $customer->address = str_replace(",", null, $customer->address);
            $customer->state= str_replace(",", null, $customer->state);

            $data .= "{$contact->name},{$customer->address},{$customer->city},{$customer->state},{$customer->zip},{$contact->home},{$contact->mobile},{$contact->email},{$created},{$status},{$sold},{$source},{$price}\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    /**
     * Create Zip Code Exports based on #173
     */
    public function exportZips()
    {
        /*
         * Report 1: Zip codes -list zip codes with total leads and then total sales for each one (I have to show see Leads and Sold total separate for each zip)
         * Report 2: Zip codes- need to be able to determine the top 5 or 6 over all for leads and sold
         * Report 3: Zip Codes-Sold need the total $ spent in each zip code.
         */
        // Zip - Leads - Sold - Sold Amount
        $data = "Zip,Leads,Number Sold,Sold Amount Total,Quote IDs\n";
        $zips = Customer::groupBy('zip')->lists('zip');
        foreach ($zips AS $zip)
        {
            $data .= "$zip,";
            $zipCount = 0;
            $soldCount = 0;
            $soldAmount = 0;
            $quoteID = null;
            foreach (Customer::whereZip($zip)->get() as $customer)
            {
                $zipCount += $customer->leads()->count();
                foreach ($customer->leads AS $lead)
                {
                    if ($lead->quotes()->whereAccepted(true)->first())
                    {
                        $quote = $lead->quotes()->whereAccepted(true)->first();
                        if ($quote->job)
                        {
                            $quoteID .= $quote->id. "/";
                            $soldCount++;
                            $soldAmount += $quote->finance_total;
                        }
                    }

                }
            }
            $data .= "$zipCount,$soldCount,$soldAmount,$quoteID\n";

        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="zips.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    public function detailModal($user, $month)
    {
        return View::make('reports.detailmodal')->withUser($user)->withMonth($month)->render();
    }

    public function sourceDetail($source, $type)
    {
        return View::make('reports.sourcedetail')->withSource($source)->withType($type);
    }

    public function userDetail($user, $type)
    {
        return View::make('reports.userdetail')->withUser($user)->withType($type);
    }

    public function frugal()
    {
        $view = View::make('reports.frugal');
        $this->layout->title = "Frugal Profit Report";
        $this->layout->content = $view;

    }

    static public function duplicateCheck()
    {
        foreach (LeadUpdate::all() as $update)
        {
            if (LeadUpdate::whereLeadId($update->lead_id)->whereStatus($update->status)->count() > 1) // if more than one.
            {
                LeadUpdate::whereLeadId($update->lead_id)->whereStatus($update->status)->first()->delete();
            }
        }
    }
    public function dashboardRange($status, $id, $start, $end)
    {
        $updates = LeadUpdate::whereStatus($status)
            ->whereUserId($id)
         //   ->groupBy('lead_id')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)->get();
        return View::make('reports.dashboard_modal')->withUpdates($updates);
    }
}