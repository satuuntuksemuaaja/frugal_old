<?php

use Illuminate\Http\Request;
use vl\core\ScheduleEngine;
use vl\leads\StatusManager;
use vl\quotes\QuoteGenerator;

class QuoteController extends BaseController
{
    public $layout = "layouts.main";

    static public function convert()
    {
        foreach (Quote::all() as $quote)
        {
            $meta = unserialize($quote->meta)['meta'];
            if (isset($meta['quote_counter_measurements']))
            {
                $g = new QuoteGranite();
                $g->quote_id = $quote->id;
                $g->granite_id = (isset($meta['granite_id'])) ? $meta['granite_id'] : 0;
                $g->removal_type = (isset($meta['quote_countertop_removal'])) ? $meta['quote_countertop_removal'] : '';
                $g->granite_override = (isset($meta['quote_special_granite'])) ? $meta['quote_special_granite'] : '';
                $g->pp_sqft = (isset($meta['quote_special_granite_price'])) ? $meta['quote_special_granite_price'] : 0;
                $g->counter_edge = (isset($meta['quote_edge'])) ? $meta['quote_edge'] : '';
                $g->counter_edge_ft = (isset($meta['quote_edge_ft'])) ? $meta['quote_edge_ft'] : 0;
                $g->backsplash_height = (isset($meta['quote_backsplash_h'])) ? $meta['quote_backsplash_h'] : 0;
                $g->raised_bar_length = (isset($meta['quote_raised_l'])) ? $meta['quote_raised_l'] : 0;
                $g->raised_bar_depth = (isset($meta['quote_raised_d'])) ? $meta['quote_raised_d'] : 0;
                $g->island_width = (isset($meta['quote_island_w'])) ? $meta['quote_island_w'] : 0;
                $g->island_length = (isset($meta['quote_island_l'])) ? $meta['quote_island_l'] : 0;
                $g->measurements = $meta['quote_counter_measurements'];
                $g->description = "Kitchen";
                $g->save();
            }
            if (isset($meta['quote_counter_measurements2']))
            {
                $g = new QuoteGranite();
                $g->quote_id = $quote->id;
                $g->granite_id = (isset($meta['granite_id2'])) ? $meta['granite_id2'] : 0;
                $g->removal_type = (isset($meta['quote_countertop_removal2'])) ? $meta['quote_countertop_removal2'] : '';
                $g->granite_override = (isset($meta['quote_special_granite2'])) ? $meta['quote_special_granite2'] : '';
                $g->pp_sqft = (isset($meta['quote_special_granite_price2'])) ? $meta['quote_special_granite_price2'] : 0;
                $g->counter_edge = (isset($meta['quote_edge2'])) ? $meta['quote_edge2'] : '';
                $g->counter_edge_ft = (isset($meta['quote_edge_ft2'])) ? $meta['quote_edge_ft2'] : 0;
                $g->backsplash_height = (isset($meta['quote_backsplash_h2'])) ? $meta['quote_backsplash_h2'] : 0;
                $g->raised_bar_length = (isset($meta['quote_raised_l2'])) ? $meta['quote_raised_l2'] : 0;
                $g->raised_bar_depth = (isset($meta['quote_raised_d2'])) ? $meta['quote_raised_d2'] : 0;
                $g->island_width = (isset($meta['quote_island_w2'])) ? $meta['quote_island_w2'] : 0;
                $g->island_length = (isset($meta['quote_island_l2'])) ? $meta['quote_island_l2'] : 0;
                $g->measurements = $meta['quote_counter_measurements2'];
                $g->description = "Kitchen (Secondary)";
                $g->save();
            }
        } //fe
    }

    public function index()
    {
        $view = View::make('quotes.index');

        $this->layout->title = "Quotes";
        $this->layout->content = $view;
    }

    public function quoteAjax()
    {
        $now = \Carbon\Carbon::now();
        $quotes = Input::has('showAll') ? Quote::all() : Quote::whereClosed(false)
            ->orderBy('created_at',
                'DESC')
            ->take(500)
            ->get();

        foreach ($quotes AS $quote)
        {
            if (!$quote->lead)
            {
                continue;
            }

            if (!Auth::user()->superuser && !Auth::user()->manager && Auth::user()->level_id != 7)
            {
                if ($quote->lead->user_id != Auth::user()->id)
                {
                    continue;
                }

            }
            else
            {
                if (Auth::user()->level_id == 7)
                {
                    if (!$quote->lead->measure)
                    {
                        continue;
                    }

                    if ($quote->lead->measure->measurer_id != Auth::user()->id)
                    {
                        continue;
                    }

                }
            }
            $lead = $quote->lead;
            $leadColor = null;
            switch ($lead->warning)
            {
                case 'R':
                    $leadColor = 'text-danger';
                    break;
                case 'Y':
                    $leadColor = 'text-warning';
                    break;
                default:
                    $leadColor = null;
            }
            $leadIcon = $leadColor ? "<a class='mjax' data-target='#workModal' href='/lead/$lead->id/notes'><i class='fa fa-warning {$leadColor}'></i></a> " : null;
            $meta = unserialize($quote->meta);
            if (!is_array($meta))
            {
                $meta = [];
            }

            $actions = null;
            // Action Buttons
            $actions .= "<a class='tooltiped' data-toggle='tooltip' data-placement='right'
                data-original-title='View Quote' href='/quote/$quote->id/view'><i class='fa fa-search'></i></a> &nbsp; &nbsp;";
            if ($quote->paperwork)
            $actions .= "<a class='tooltiped' data-toggle='tooltip' data-placement='right'
                data-original-title='Download Contract' href='/quote/$quote->id/contract'><i class='glyphicon glyphicon-save'></i></a> &nbsp; &nbsp;";
            if (!empty($meta['meta']['quote_appliances']))
            {
                $actions .= "<a class='tooltiped mjax " . $quote->getApplianceClass() . "' data-toggle='tooltip' data-placement='right'
                data-original-title='Appliance Settings' data-toggle='modal' data-target='#files'
                href='/quote/{$quote->id}/appsettings'><i class='fa fa-wrench'></i></a> &nbsp; &nbsp;";
            }
            $actions .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#files' href='/quote/$quote->id/files'><i class='fa fa-image'></i></a> &nbsp; &nbsp;";
            $actions .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Add Task' data-toggle='modal' data-target='#files'
                href='/task/customer/{$quote->lead->customer->id}/job/0/quick'><i class='fa fa-openid'></i></a> &nbsp; &nbsp;";
            $actions .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Duplicate Quote' data-toggle='modal' data-target='#files'
                href='/quote/{$quote->id}/duplicate'><i class='fa fa-refresh'></i></a> &nbsp; &nbsp;";

            $actions .= "<a class='get tooltiped' data-original-title='Archive' href='/quote/$quote->id/archive'><i class='fa fa-eraser'></i></a></span>";

            if (Auth::user()->level_id == 7)
            {
                $actions = "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#files' href='/quote/$quote->id/files'><i class='fa fa-image'></i></a> &nbsp; &nbsp;";
            }

            $designer = (isset($quote->lead->user->name)) ? $quote->lead->user->name : "No Designer Assigned";
            $dlist = User::whereLevelId(4)
                ->get();
            $designers = [];
            foreach ($dlist AS $list)
            {
                $designers[] = ['value' => $list->id, 'text' => $list->name];
            }

            $designer = $quote->lead->user ? $quote->lead->user->name : "No Designer Assigned";
            $initial = ($quote->final) ? BS::Label('bg-green', 'FINAL') : BS::Label('bg-info',
                "<a class='get' href='/quote/$quote->id/final'>INITIAL</a>");
            $initial = "<span class='pull-right'>$initial</span>";
            if ($quote->final && !isset($meta['meta']['finance']))
            {
                $initial .= "<span class='pull-right'>" . BS::Label('bg-info',
                        "<a href='/quote/$quote->id/financing'>NEEDS FINANCING") . "</span>";
            }

            $title = ($quote->title) ? " <span class='text-info'>($quote->title)</span> " : null;
            $color = !$quote->paperwork ? '<span class="text-danger">' : null;
            $rows[] = [
                $color . $leadIcon . $actions,
                "<a href='/profile/{$quote->lead->customer->id}/view'>" . $quote->lead->customer->name . $title . "</a><br/><small>ID: {$quote->lead->customer->id}</small>",
                $designer,
                $quote->created_at->diffInDays(),
                $quote->type . $initial,
                ($quote->lead->status) ? $quote->lead->status->name : 'Unknown',
            ];
        }
        $data = new StdClass;
        $data->sEcho = 3;
        if (!isset($rows))
        {
            $rows = [];
        }
        $data->iTotalRecords = sizeOf($rows);
        $data->iTotalDisplayRecords = sizeOf($rows);
        $data->aaData = $rows;

        return Response::json($data);
    }

    public function cabinetEdit($id, $cabid)
    {
        $view = View::make('quotes.stages.cabinets');
        $view->quote = Quote::find($id);
        $view->selectedCabinet = QuoteCabinet::find($cabid);
        $this->layout->title = "Edit Cabinet";
        $this->layout->content = $view;
    }

    public function cabinetXMl($id, $cabid)
    {
        $view = View::make('quotes.xml');
        $view->cabinet = QuoteCabinet::find($cabid);
        return $view;
    }

    public function cabinetEditSave($id, $cabid)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $cabinet = QuoteCabinet::find($cabid);
        $cabinet->cabinet_id = Input::get('cabinet_id');
        $cabinet->location = Input::get('location');
        $cabinet->measure = Input::get('measure') ? 1 : 0;
        $cabinet->inches = Input::get('inches');
        $cabinet->price = Input::get('price');
        $cabinet->color = Input::get('color');
        $cabinet->delivery = Input::get('delivery');
        $cabinet->description = Input::get('description');
        $cabinet->customer_removed = Input::get('customer_removed');
        $cabinet->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    // Editable Routes Here

    private function editable(Quote $quote)
    {

        if ($quote->accepted == 1 && !Auth::user()->superuser)
        {
            return false;
        }
        else
        {
            return true;
        }

    }

    public function updateQuoteField($id, $field)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $quote->{$field} = Input::get('value');
        $quote->save();
        return Response::json(['status' => 'ok']);
    }

    public function duplicateModal($id)
    {
        $view = View::make('quotes.duplicate');
        $view->quote = Quote::find($id);
        return $view;
    }

    public function duplicate($id)
    {
        $quote = Quote::find($id);
        $new = new Quote;
        $new->accepted = $quote->accepted;
        $new->final = $quote->final;
        $new->meta = $quote->meta;
        $new->type = $quote->type;
        $new->title = Input::get('title');
        $new->lead_id = $quote->lead_id;
        $new->save();
        // Copy over cabinet data
        foreach ($quote->cabinets AS $cabinet)
        {
            $cab = new QuoteCabinet;
            $cab->quote_id = $new->id;
            $cab->data = $cabinet->data;
            $cab->override = $cabinet->override;
            $cab->location = $cabinet->location;
            $cab->measure = $cabinet->measure;
            $cab->color = $cabinet->color;
            $cab->cabinet_id = $cabinet->cabinet_id;
            $cab->name = $cabinet->name;
            $cab->inches = $cabinet->inches;
            $cab->price = $cabinet->price;
            $cab->save();
        }
        // Copy questionaires.
        foreach ($quote->answers AS $answer)
        {
            $a = new QuoteAnswer;
            $a->question_id = $answer->question_id;
            $a->quote_id = $new->id;
            $a->answer = $answer->answer;
            $a->save();
        }
        foreach ($quote->granites as $granite)
        {
            $q = new QuoteGranite();
            $q->quote_id = $new->id;
            $q->description = $granite->description;
            $q->granite_id = $granite->granite_id;
            $q->granite_override = $granite->granite_override;
            $q->pp_sqft = $granite->pp_sqft;
            $q->removal_type = $granite->removal_type;
            $q->measurements = $granite->measurements;
            $q->counter_edge = $granite->counter_edge;
            $q->counter_edge_ft = $granite->counter_edge_ft;
            $q->backsplash_height = $granite->backsplash_height;
            $q->raised_bar_length = $granite->raised_bar_length;
            $q->raised_bar_depth = $granite->raised_bar_depth;
            $q->island_width = $granite->island_width;
            $q->island_length = $granite->island_length;
            $q->save();
        }

        foreach ($quote->tiles as $tile)
        {
            $t = new QuoteTile();
            $t->quote_id = $new->id;
            $t->description = $tile->description;
            $t->linear_feet_counter = $tile->linear_feet_counter;
            $t->backsplash_height = $tile->backsplash_height;
            $t->pattern = $tile->pattern;
            $t->sealed = $tile->sealed;
            $t->save();

        }

        if (App::make('request')->ajax())
        {
            return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/quote/$new->id/view"]);
        }
        else
        {
            return Redirect::to("/quote/$new->id/view");
        }
    }

    public function sinkAdd($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        $meta['meta']['sinks'][] = Input::get('sink_id');
        if (Input::has('plumber_needed'))
        {
            $meta['meta']['sink_plumber'][] = Input::get('sink_id');
        }

        $quote->meta = serialize($meta);
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function sinkDelete($id, $instance)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        unset($meta['meta']['sinks'][$instance]);
        $quote->meta = serialize($meta);
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function convertToJob($id)
    {
        $quote = Quote::find($id);
        // Step 1 - Change the lead status to Sold
        StatusManager::setLead($quote->lead, 14);
        $quote->accepted = 1;
        $quote->save();
        // Step 2 -  Archive the Initial Quote
        $initial = Quote::whereFinal(0)
            ->whereLeadId($quote->lead->id)
            ->first();
        if ($initial) // Just in case the initial was deleted for whatever reason.
        {
            $initial->closed = 1;
            $initial->save();
        }

        // Step 2b - Create a snapshot of the quote.
        QuoteGenerator::createSnapshot($quote);

        // Step 3 - Create the Job on the Job board
        $job = new Job;
        $job->quote_id = $quote->id;
        $job->locked = 1;
        $job->contract_date = $quote->updated_at;
        $job->closed_on = \Carbon\Carbon::now();
        $job->save();

        // Step 4 - Create Records for Cabinets, Hardware, and Accessories.
        foreach ($quote->cabinets AS $cabinet)
        {
            $item = new JobItem;
            $item->job_id = $job->id;
            $item->instanceof = 'Cabinet';
            $item->reference = $cabinet->id;
            $item->save();
        }
        $meta = unserialize($quote->meta);

        if (isset($meta['meta']['quote_pulls']))
        {
            $item = new JobItem;
            $item->job_id = $job->id;
            $item->instanceof = 'Hardware';
            $item->reference = 1;
            $item->save();
        }

        if (isset($meta['meta']['quote_accessories']))
        {
            $item = new JobItem;
            $item->job_id = $job->id;
            $item->instanceof = 'Accessory';
            $item->reference = 1;
            $item->save();
        }


        // Next Step - Create a blank FFT entry.
        $fft = new FFT;
        $fft->job_id = $job->id;
        $fft->save();

        // Add the addons to the items
        foreach ($quote->addons as $addon)
        {
            if (!$addon->addon) continue;
            $item = new JobItem;
            $item->job_id = $job->id;
            $item->instanceof = 'Item';
            $item->reference = sprintf($addon->addon->contract, $addon->qty, $addon->description);
            $item->save();
        }

        // Add Tiles to the Items
        if ($quote->tiles()->count() > 0)
        {
            foreach ($quote->tiles as $tile)
            {
                $in = $tile->linear_feet_counter  * 12;
                $calc1 = ($in * $tile->backsplash_height) / 144;
                $pattern = $tile->pattern;
                $will = $tile->sealed == 'Yes' ? "will" : "will not";
                $item = new JobItem;
                $item->job_id = $job->id;
                $item->instanceof = 'Item';
                $item->reference = "$calc1 sq. feet of customer supplied tile/grout. Design: $pattern, {$will} be sealed.";
                $item->save();
            }
        }

        self::applyQuestionaireJobItems($job, $quote);
        // Now take all these items and create appropriate purchase orders
        PurchaseController::createFromJob($job);
        // Fire an email off to debeer about the new contract.
        ScheduleEngine::emailDebeer($quote);

        return Redirect::to('/jobs');
    }

    /**
     * Create job items based on any question that has a flag to put it on the job board.
     * @param $job
     * @param $quote
     */
    static public function applyQuestionaireJobItems($job, $quote)
    {
        //Log::info("Checking ". $quote->answers()->count() . " answers");
        foreach ($quote->answers AS $answer)
        {
            //  Log::info("Checking answer $answer for question {$answer->question->question}");
            if ($answer->question && $answer->question->on_job_board)
            {
                $item = new JobItem;
                $item->job_id = $job->id;
                $item->instanceof = 'Item';
                $item->reference = $answer->question->question . " - " . $answer->answer;
                $item->orderable = 0;
                $item->save();
            }
        }
        // Also do addons
        foreach ($quote->addons as $addon)
        {
            $item = new JobItem;
            $item->job_id = $job->id;
            $item->instanceof = 'Item';
            $item->reference = $addon->qty . " x " . $addon->addon->item;
            $item->orderable = 0;
            $item->save();
        }
    }

    public function contract($id)
    {
        $quote = Quote::find($id);

        $html = View::make('pdf.contract')
            ->withQuote($quote)
            ->render();
        if (Input::get('show'))
        {
            return $html;
        }
        else
        {
            return Response::make(
                PDF::load($html, 'A4', 'portrait')
                    ->output(),
                200,
                ['content-type' => 'application/pdf']);
        }

    }

    public function snapshots($id)
    {
        $view = View::make('quotes.snapshots');
        $view->quote = Quote::find($id);
        return $view;
    }

    public function paperwork($id)
    {
        $quote = Quote::find($id);
        $quote->paperwork = 1;
        $quote->save();
        \vl\leads\StatusManager::setlead($quote->lead, 10);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function needsPaperwork($id)
    {
        $quote = Quote::find($id);
        \vl\leads\StatusManager::setlead($quote->lead, 11);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function financing($id)
    {
        $view = View::make('quotes.financing');
        $view->quote = Quote::find($id);
        $this->layout->content = $view;
        $this->layout->title = "Financing Options";
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => "/quote/$id/view", 'text' => $view->quote->lead->customer->name],
            ['url' => '#', 'text' => 'Financing Options'],

        ];
    }

    public function financingSave($id, $type)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        $meta['meta']['finance']['type'] = $type;
        switch ($type)
        {
            case 'all':
                $meta['meta']['finance']['terms'] = Input::get('terms');
                break;
            case 'partial':
                unset($meta['meta']['finance']['method']);    // Just in case for #146
                $meta['meta']['finance']['down_cash'] = Input::get('down_cash');
                $meta['meta']['finance']['down_credit'] = Input::get('down_credit');
                $meta['meta']['finance']['downpayment'] = Input::get('downpayment');
                $meta['meta']['finance']['terms'] = Input::get('terms');
                $dpt = Input::get('down_cash') + Input::get('down_credit');
                if ($dpt != Input::get('downpayment'))
                {
                    return Response::json([
                        'status' => 'danger',
                        'gtitle' => 'Unable to save',
                        'gbody'  => 'Credit and Cash downpayments should equal the total downpayment amount. Try again.'
                    ]);
                }
                break;
            case 'none':
                $meta['meta']['finance']['method'] = Input::get('method');
                $meta['meta']['finance']['no_cash'] = Input::get('no_cash');
                $meta['meta']['finance']['no_credit'] = Input::get('no_credit');

                break;
        }

        $quote->meta = serialize($meta);
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/quote/$quote->id/view"]);
    }

    public function archive($id)
    {
        $quote = Quote::find($id);
        $quote->closed = 1;
        $quote->save();
        if (App::make('request')->ajax())
        {
            return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => 'Done!']);
        }
        return Redirect::to('/quotes');
    }

    public function delete($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.
        Please contact office to update quote if necessary.',
            ]);
        }
        $quote = Quote::find($id)
            ->delete();
        return Redirect::to('/quotes');
    }

    public function view($id)
    {
        $view = View::make('quotes.view');
        $view->quote = Quote::find($id);
        $this->layout->title = "Quote View";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
        ];
    }

    public function create($id = null)
    {
        $quote = new Quote;
        if ($id)
        {
            $oldquote = Quote::find($id);
            $quote->lead_id = $oldquote->lead_id;
            $quote->final = 1;
            $quote->type = $oldquote->type;
            $quote->title = '';
            $quote->save();
            $oldquote->closed = 1;
            $oldquote->save();
        }
        else
        {
            $quote->lead_id = Input::get('lead_id');
            $quote->final = 0;
            $quote->type = Input::get('type');
            $quote->title = '';
            if ($quote->type == 'Builder')
            {
                $quote->final = 1;
            }
            $quote->save();
        }
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/quote/$quote->id/start"]);
    }

    public function begin($id)
    {
        $quote = Quote::find($id);
        switch ($quote->type)
        {
            case 'Full Kitchen':
                $page = 'cabinets';
                break;
            case 'Cabinet Only':
                $page = 'cabinets';
                break;
            case 'Cabinet and Install':
                $page = 'cabinets';
                break;
            case 'Granite Only':
                $page = 'granite';
                break;
            case 'Cabinet Small Job':
                $page = 'cabinets';
                break;
            case 'Builder' :
                $page = 'cabinets';
                break;

        }
        $view = View::make("quotes.stages.$page");
        $view->quote = $quote;
        $this->layout->title = "Quote Modifications";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
        ];
    }

    public function cabinets($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.cabinets");
        $view->quote = $quote;
        $this->layout->title = "Cabinet Data";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function cabinetsSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        if (Input::hasFile('xml'))
        {
            $this->processUploadedXML($id);
            return Redirect::to("/quote/$quote->id/cabinets");
        }
        $quote->save();
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function processUploadedXML($id)
    {
        $file = Input::file('xml');
        $xml = file_get_contents($file->getRealPath());
        $quote = Quote::find($id);
        QuoteGenerator::setCabinetData($quote, $xml);
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function uploadWood($id, $cabinet_id)
    {
        $quote = Quote::find($id);
        if (Input::hasFile('wood_xml'))
        {
            $this->processUploadedWoodXML($id, $cabinet_id);
            return Redirect::to("/quote/$quote->id/cabinets");
        }
        $quote->save();
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function processUploadedWoodXML($id, $cabinet_id)
    {
        $file = Input::file('wood_xml');
        $xml = file_get_contents($file->getRealPath());
        $quote = Quote::find($id);
        $cabinet = QuoteCabinet::find($cabinet_id);
        $cabinet->wood_xml = $xml;
        $cabinet->save();
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function cabinetFieldUpdate($id, $cabid, $field)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $cabinet = QuoteCabinet::find($cabid);
        $cabinet->{$field} = Input::get('value');
        $cabinet->save();
        return Response::json(['success' => true]);
    }

    public function cabinetDelete($id, $cabid)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        QuoteCabinet::find($cabid)
            ->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function granite($id)
    {
        $quote = Quote::find($id);
        if (Input::has('del'))
        {
            QuoteGranite::find(Input::get('del'))->delete();
            return Redirect::to("/quote/$id/granite");
        }
        if (Input::has('moving')) // ajax processing to the page to allow movement
        {
            if ($quote->type == 'Full Kitchen')
            {
                $meta = unserialize($quote->meta);
                if (!isset($meta['meta']['cabinet_id']) || !isset($meta['meta']['cabinet_price']))
                {
                    return Response::json([
                        'status'  => 'alert',
                        'message' =>
                            'No cabinet information was found. Please make sure to click Update Primary/Secondary Cabinets before continuing.'
                    ]);
                }
                if (!$meta['meta']['cabinet_id'] || !$meta['meta']['cabinet_price'])
                {
                    return Response::json([
                        'status'  => 'danger',
                        'action'  => 'alert',
                        'message' =>
                            'No cabinet information was found. Please make sure to click Update Primary/Secondary Cabinets before continuing.'
                    ]);
                }
                return Response::json([
                    'status' => 'success',
                    'action' => 'reload',
                    'url'    => "/quote/$quote->id/granite"
                ]);
            }
        }

        $view = View::make("quotes.stages.granite");
        $view->quote = $quote;
        $this->layout->title = "Granite Data";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function graniteSave($id)
    {
        $request = app('request');
        /*        if (!$this->editable(Quote::find($id)))
                {

                    return Response::json([
                        'status' => 'danger',
                        'gtitle' => 'Unable to Edit',
                        'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
                    ]);
                } */
        $quote = Quote::find($id);
        if ($request->has('update'))
        {
            $quote->picking_slab = $request->get('picking_slab');
            $quote->save();
            $g = $request->has('g_id') ? QuoteGranite::find($request->get('g_id')) : new QuoteGranite();
            $request->merge([
                'quote_id' => $id
            ]);
            if (!$request->get('description'))
            {
                $request->merge(['description' => 'Kitchen']);
            }
            if (!$request->has('g_id'))
            {
                $g = QuoteGranite::create($request->except(['updateGranite', 'g_id', 'update', 'picking_slab']));
            }
            else
            {
                if ($this->editable($quote))
                {
                    $g->update($request->except(['updateGranite', 'g_id', 'update', 'picking_slab']));
                }
                else
                {
                    $g->update(['granite_jo' => $request->get('granite_jo')]);
                }
            }
        }

        return Redirect::to("/quote/$quote->id/granite");
    }

    public function graniteDelete($id, $type)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }

        return Redirect::to("/quote/$id/granite");
    }

    public function appliances($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        if (Input::has('moving')) // ajax processing to the page to allow movement
        {
            if ($quote->type == 'Full Kitchen' || $quote->type == 'Granite Only')
            {
                $meta = unserialize($quote->meta);
                if ($quote->granites()->count() == 0)
                {
                    return Response::json([
                        'status'  => 'alert',
                        'message' =>
                            'Granite information is missing. Please click save on each granite column.'
                    ]);
                }

                return Response::json([
                    'status' => 'success',
                    'action' => 'reload',
                    'url'    => "/quote/$quote->id/appliances"
                ]);
            }
            return Response::json([
                'status' => 'success',
                'action' => 'reload',
                'url'    => "/quote/$quote->id/appliances"
            ]);

        }
        $view = View::make("quotes.stages.appliances");
        $view->quote = $quote;
        $this->layout->title = "Appliances";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function appliancesSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);

        if (Input::has('sink_id'))
        {
            $meta['meta']['sink_id'] = Input::get('sink_id');
            if (Input::has('sink_id2'))
            {
                $meta['meta']['sink_id2'] = Input::get('sink_id2');
            }
        }
        if (Input::has('appliances'))
        {
            $meta['meta']['quote_appliances'] = [];
            foreach (Input::all() AS $key => $val)
            {
                if (preg_match('/app_/', $key))
                {
                    $key = trim(str_replace("app_", null, $key));
                    $meta['meta']['quote_appliances'][] = $key;
                }
            }
        }
        $meta['meta']['progress_appliance'] = true;
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();
        if (App::make('request')->ajax())
        {
            return Response::json(['status' => 'success', 'action' => 'selfreload']);
        }
        else
        {
            return Redirect::to("/quote/$quote->id/appliances");
        }
    }

    public function accessories($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.accessories");
        $view->quote = $quote;
        $this->layout->title = "Accessories";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function accessoriesSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);

        $accessories = [];
        foreach (Input::all() AS $key => $val)
        {
            if (preg_match("/acc_/i", $key))
            {
                $key = str_replace("acc_", null, $key);
                if ($val > 0)
                {
                    $accessories[$key] = $val;
                }
            }
        }

        $meta['meta']['progress_accessories'] = true;
        $meta['meta']['quote_accessories'] = $accessories;
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function accessoryRemove($id, $aid)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        foreach ($meta['meta']['quote_accessories'] AS $idx => $acc)
        {
            if ($idx == $aid)
            {
                unset($meta['meta']['quote_accessories'][$idx]);
            }
        }

        $quote->meta = serialize($meta);
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function hardware($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.hardware");
        $view->quote = $quote;
        $this->layout->title = "Hardware";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    } // saving hardware

    public function hardwareSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (Input::has('pulls'))
        {
            $pulls = [];
            foreach (Input::all() AS $key => $val)
            {
                if (preg_match("/pull_/i", $key))
                {
                    $key = str_replace("pull_", null, $key);
                    if ($val > 0)
                    {
                        $pulls[$key] = $val;
                    }
                }
            }

            $meta['meta']['quote_pulls'] = $pulls;
            $meta['meta']['progress_pulls'] = true;

        } // if Pulls

        if (Input::has('knobs'))
        {
            $knobs = [];
            foreach (Input::all() AS $key => $val)
            {
                if (preg_match("/knob/i", $key))
                {
                    $key = str_replace("knob_", null, $key);
                    if ($val > 0)
                    {
                        $knobs[$key] = $val;
                    }
                }
            }

            $meta['meta']['quote_knobs'] = $knobs;
            $meta['meta']['progress_knobs'] = true;

        } // if Pulls
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function hardwareDelete($id, $type, $hid)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        switch ($type)
        {
            case 'pull':
                foreach ($meta['meta']['quote_pulls'] AS $pull => $qty)
                {
                    if ($pull == $hid)
                    {
                        unset($meta['meta']['quote_pulls'][$pull]);
                    }
                }

                break;
            case 'knob':
                foreach ($meta['meta']['quote_knobs'] AS $knob => $qty)
                {
                    if ($knob == $hid)
                    {
                        unset($meta['meta']['quote_knobs'][$knob]);
                    }
                }

                break;
        }
        $quote->meta = serialize($meta);
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function additional($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.additional");
        $view->quote = $quote;
        $this->layout->title = "Additional Items";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function additionalSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if ($this->validatePrices() !== true)
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Price Validation Failed',
                'gbody'  => 'All misc items, plumbing, electrical and installer items must be entered in ITEM - PRICE format!',
                'ev'     => "alert('Error in misc items, plumbing, electrical or installer items. Items should be in ITEM - PRICE format!')",
            ]);
        }

        $meta['meta']['quote_misc'] = Input::get('quote_misc');
        $meta['meta']['quote_plumbing_extras'] = Input::get('quote_plumbing_extras');
        $meta['meta']['quote_electrical_extras'] = Input::get('quote_electrical_extras');
        $meta['meta']['quote_installer_extras'] = Input::get('quote_installer_extras');
        $meta['meta']['quote_special'] = Input::get('quote_special');
        $meta['meta']['quote_coupon'] = Input::get('quote_coupon');
        $meta['meta']['quote_discount'] = Input::get('quote_discount');
        $meta['meta']['quote_discount_reason'] = Input::get('quote_discount_reason');
        $meta['meta']['progress_additional'] = true;

        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->promotion_id = Input::get('promotion_id');
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    private function validatePrices()
    {
        if (Input::get('quote_misc'))
        {
            foreach (explode("\n", trim(Input::get('quote_misc'))) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if (Input::get('quote_plumbing_extras'))
        {
            foreach (explode("\n", trim(Input::get('quote_plumbing_extras'))) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if (Input::get('quote_electrical_extras'))
        {
            foreach (explode("\n", trim(Input::get('quote_electrical_extras'))) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if (trim(Input::get('quote_installer_extras')))
        {
            foreach (explode("\n", Input::get('quote_installer_extras')) AS $items)
            {
                $x = explode("-", $items);
                // dd($x);

                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        return true;
    }

    public function questionaire($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.questionaire");
        $view->quote = $quote;
        $this->layout->title = "Questionaire";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Quotes'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ],
        ];
    }

    public function questionaireSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        foreach (Input::all() AS $key => $val)
        {
            if (preg_match("/question_/i", $key))
            {
                $key = str_replace("question_", null, $key);
                $question = Question::find($key);
                if (!$val && $val != '0')
                {

                        return Response::json([
                            'status' => 'danger',
                            'gtitle' => "Unable to Save",
                            'gbody'  => "You must answer $question->question"
                        ]);

                }
                $answer = QuoteAnswer::whereQuoteId($quote->id)
                    ->whereQuestionId($key)
                    ->first();
                if (!$answer)
                {
                    $answer = new QuoteAnswer;
                }

                if ($val == 'on')
                {
                    $val = 'Y';
                }

                $answer->question_id = $key;
                $answer->quote_id = $quote->id;
                $answer->answer = $val;
                $answer->save();

            }
        }
        $meta = unserialize($quote->meta);
        $meta['meta']['progress_questionaire'] = true;
        $quote->meta = serialize($meta);
        $quote->save();

        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function led($id)
    {
        $quote = Quote::find($id);
        $view = View::make("quotes.stages.led");
        $view->quote = $quote;
        $this->layout->title = "Led and Tile";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Led and Tile'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ]
        ];
    }

    public function ledSave($id)
    {
        if (!$this->editable(Quote::find($id)))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'Unable to Edit',
                'gbody'  => 'This quote is already accepted! Please contact office to update quote if necessary.'
            ]);
        }
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (Input::has('led'))
        {
            if (Input::has('quote_led_12'))
            {
                $meta['meta']['quote_led_12'] = Input::get('quote_led_12');
            }
            if (Input::has('quote_led_60'))
            {
                $meta['meta']['quote_led_60'] = Input::get('quote_led_60');
            }
            if (Input::has('quote_led_transformers'))
            {
                $meta['meta']['quote_led_transformers'] = Input::get('quote_led_transformers');
            }
            if (Input::has('quote_led_connections'))
            {
                $meta['meta']['quote_led_connections'] = Input::get('quote_led_connections');
            }
            if (Input::has('quote_led_couplers'))
            {
                $meta['meta']['quote_led_couplers'] = Input::get('quote_led_couplers');
            }
            if (Input::has('quote_led_switches'))
            {
                $meta['meta']['quote_led_switches'] = Input::get('quote_led_switches');
            }
            if (Input::has('quote_led_feet'))
            {
                $meta['meta']['quote_led_feet'] = Input::get('quote_led_feet');
            }
            if (Input::has('quote_puck_lights'))
            {
                $meta['meta']['quote_puck_lights'] = Input::get('quote_puck_lights');
            }

            $meta['meta']['progress_led'] = true;
        }
        else
        {
            if (Input::has('tile_id') || Input::get('tile_id') > 0)
            {
                $tile = QuoteTile::find(Input::get('tile_id'));
            }
            else $tile = new QuoteTile();
            $tile->linear_feet_counter = Input::get('linear_feet_counter');
            $tile->backsplash_height = Input::get('backsplash_height');
            $tile->quote_id = $quote->id;
            $tile->pattern = Input::get('pattern');
            $tile->sealed = Input::get('sealed');
            $tile->description = Input::get('description') ?: "Main Tile";
            $tile->save();
        }

        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function removeTile($id, $tid)
    {
        QuoteTile::find($tid)->delete();
        return Response::json(['status' => 'success', 'action' => 'reload', "url" => "/quote/$id/led"]);
    }

    public function summary($id)
    {
        $quote = Quote::find($id);
        $html = View::make('pdf.summary')
            ->withQuote($quote)
            ->render();
        return Response::make(
            PDF::load($html, 'A4', 'portrait')
                ->output(),
            200,
            ['content-type' => 'application/pdf']);

    } //fn

    public function addons($id)
    {
        $view = View::make('quotes.stages.addons')->withQuote(Quote::find($id));
        $this->layout->title = "Led and Tile";
        $this->layout->content = $view;
        $this->layout->crumbs = [
            ['url' => '/quotes', 'text' => 'Led and Tile'],
            ['url' => '#', 'text' => $view->quote->lead->customer->name],
            [
                'url'  => "/quote/{$view->quote->id}/view",
                'text' => ($view->quote->final) ? "Final Quote" : "Initial Quote"
            ]
        ];
        if (Input::has('delete'))
        {
            $qaddon = QuoteAddon::find(Input::get('delete'));
            $qaddon->delete();
            return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/quote/{$view->quote->id}/addons"]);
        }

    }

    public function addonsUpdate($id)
    {
        if (!Input::get('item_id'))
        {
            return Response::json([
                'status' => 'danger',
                'gtitle' => 'NO Item Selected',
                'gbody'  => 'You must select an addon'
            ]);
        }
        $addon = Input::has('update') ? QuoteAddon::find(Input::get('update'))->addon
            : Addon::find(Input::get('item_id'));
        $oprice = $addon->price;

        if (Input::has('update')) // Updating an addon
        {
            $qaddon = QuoteAddon::find(Input::get('update'));
            //$qaddon->addon_id = Input::get('item_id');
            $qaddon->price = Input::get('price') > 0 ? Input::get('price') : $oprice;
            $qaddon->qty = Input::get('qty');
            $qaddon->description = Input::get('description');
            $qaddon->save();
        }
        else // Creating an addon.
        {

            $qaddon = new QuoteAddon;
            $qaddon->quote_id = $id;
            $qaddon->addon_id = Input::get('item_id');
            $qaddon->price = Input::get('price') > 0 ? Input::get('price') : $oprice;
            $qaddon->qty = Input::get('qty');
            $qaddon->description = Input::get('description');
            $qaddon->save();
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function saveResponsibilities($id)
    {
        $quote = Quote::find($id);
        // Lets itterate through all available responsibilities and add/remove if necessary.
        foreach (Responsibility::all() as $r)
        {
            $quote->responsibilities()->whereResponsibilityId($r->id)->delete();
            if (Input::has("rs_$r->id")) // checked.
            {
                (new QuoteResponsibility)->create([
                    'quote_id'          => $id,
                    'responsibility_id' => $r->id
                ]);
            }
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Show appliance settings modal
     * @param $id
     */
    public function appSettings($id)
    {
        $quote = Quote::find($id);
        return View::make('quotes.appsettings')->withQuote($quote)->render();
    }

    /**
     * Save appliance settings
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appSettingsSave($id)
    {
        $request = App::make('request');
        foreach ($request->all() as $key => $val)
        {
            if (preg_match("/app_/", $key))
            {
                $key = str_replace("app_", null, $key);
                $x = explode("_", $key);
                $aid = $x[0]; // _id
                $type = $x[1]; //_brand, model size
                QuoteAppliance::find($aid)->update([$type => $val]);
            }
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Send customer a link to fill in their appliances makes and models.
     * @param $id
     */
    public function appSettingsSend($id)
    {
        $quote = Quote::find($id);
        $data = [
            'quote' => $quote,

        ];
        $contact = $quote->lead->customer->contacts()->first();
        Mail::send('emails.appliances', $data, function ($message) use ($contact) {
            $message->to([
                $contact->email => $contact->name,
                //       'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject("IMPORTANT! Please confirm your appliances for your Frugal Kitchens and Cabinets Job");
        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Customer Appliance Route.
     * @param $id
     */
    public function customerAppliances($id)
    {
        $this->layout->title = "Customer Appliances";
        $this->layout->content = View::make('quotes.custappliances')->withQuote(Quote::find($id))->render();
    }

    /**
     * Customer Appliance Route.
     * @param $id
     */
    public function customerAppliancesThanks($id)
    {
        $this->layout->title = "Thank You!";
        $this->layout->content = View::make('quotes.custappliancesthanks')->withQuote(Quote::find($id))->render();
    }


    /**
     * Save appliances from customer.
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function customerAppliancesSave($id)
    {
        foreach (Input::all() as $key => $val)
        {
            if (preg_match("/app_/", $key))
            {
                $key = str_replace("app_", null, $key);
                $x = explode("_", $key);
                $aid = $x[0]; // _id
                $type = $x[1]; //_brand, model size
                QuoteAppliance::find($aid)->update([$type => $val]);
            }
        }
        return Response::json([
            'status' => 'success',
            'action' => 'reload',
            'url'    => "/customer/$id/appliances/thanks"
        ]);
    }

    /**
     * Cover quote meta tile into quote_tile relationships
     */
    static public function convertTile()
    {

        foreach (Quote::all() as $quote)
        {
            $quote->tiles()->delete();
            $meta = unserialize($quote->meta)['meta'];
            if (!empty($meta['quote_tile_feet']))
            {
                (new QuoteTile)->create([
                    'quote_id'            => $quote->id,
                    'description'         => 'Main Tile',
                    'linear_feet_counter' => !empty($meta['quote_tile_feet']) ? $meta['quote_tile_feet'] : 0,
                    'backsplash_height'   => !empty($meta['quote_tile_backsplash']) ? $meta['quote_tile_backsplash'] : 0,
                    'pattern'             => !empty($meta['quote_tile_pattern']) ? $meta['quote_tile_pattern'] : '',
                    'sealed'              => !empty($meta['quote_tile_sealed']) ? $meta['quote_tile_sealed'] : ''
                ]);
            } //if
        } //fe
    } // static


} //class