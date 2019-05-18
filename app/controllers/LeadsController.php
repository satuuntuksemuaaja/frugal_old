<?php
use Carbon\Carbon;
use vl\core\NotificationEngine;
use vl\leads\StatusManager;

class LeadsController extends BaseController
{
    public $layout          = "layouts.main";

    private function datatables()
    {
        $request = app('request');


        $column = $request->get('order')[0]['column'];
        if (!$column)
            $column = 1;
        $mode = $request->get('order')[0]['dir'];
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];
        $colmap = [
            0 => 'customer.name',
            1 => 'id',
            2 => 'created_at',
            3 => 'status_id',
            4 => 'source_id',
            5 => 'showroom.scheduled',
            6 => 'closing.scheduled',
            7 => 'measure.scheduled',
            8 => 'user_id'
        ];
        $leads = (new Lead)->with(['customer', 'showroom', 'closing', 'measure', 'status', 'source']);

        if ($search)
            $leads = $leads->whereHas('customer', function($t) use ($search)
            {
                $t->where('name', 'like', "%$search%");
            })->orWhereHas('source', function($t) use ($search)
            {

                $t->where('type', 'like', "%$search%");
            });
        if ($start)
            $leads = $leads->skip($start);
        if ($length)
            $leads = $leads->take($length);

        if (!Auth::user()->superuser && !Auth::user()->manager && Auth::user()->level_id != 7 && Auth::user()->level_id != 10)
        {
            $leads = $leads->whereUserId(Auth::user()->id);

        }
        else
        {
            if (Auth::user()->level_id == 7)
            {
                $leads = $leads->whereHas('measure', function($t)
                {
                   $t->where('measurer_id', Auth::user()->id);
                });
            }
        }
        if ($column > 4 && $column < 8)
        {
            $leads = $leads->get();
            if ($mode == 'asc')
            $leads = $leads->sortBy($colmap[$column]);
            else $leads = $leads->sortByDesc($colmap[$column]);
        }
          else
          {
              if ($column == 1)
              {
                  $leads = $leads->orderBy($colmap[$column], 'DESC')->get();
              }
              else
              {
                  $leads = $leads->orderBy($colmap[$column], $mode)->get();
              }
          }
        $dblock = [];
        foreach ($leads AS $lead)
        {
            if (!Auth::user()->superuser && !Auth::user()->manager && Auth::user()->level_id != 7 && Auth::user()->level_id != 10)
            {
                if ($lead->user_id != Auth::user()->id) continue;
            }
            if (!$request->has('showAll'))
            {
                if ($lead->archived) continue;
            }
                if ($this->renderLead($lead))
                {
                    $dblock[] = $this->renderLead($lead);
                }
        }
        $ret = [
            'draw' => $request->get('draw', 0),
            'recordsTotal' => $leads->count(),
            'recordsFiltered' => Lead::all()->count(),
            'data' => $dblock
        ];
        return $ret;

    }

    public function index()
    {
        $request = app('request');
        if ($request->has('draw'))
        {
            return $this->datatables();
        }
        $view = View::make('leads.index');
        if (Input::has('showAll'))
        {
            $view->leads = Lead::orderBy('created_at', 'DESC')->get();
        }
        else
        {
            $view->leads = Lead::whereClosed(0)->whereArchived(0)->orderBy('updated_at', 'DESC')->get();
        }
        $this->layout->title = "Leads";
        $this->layout->content = $view;
    }

    public function updateSource($id)
    {
        $lead = Lead::find($id);
        $lead->source_id = Input::get('value');
        $lead->save();
        return Response::json(['success' => true]);
    }

    public function showRoom($id)
    {
        $view = View::make('picker');
        $lead = Lead::find($id);
        $view->pre = "Select a showroom date";
        $view->url = "/lead/$lead->id/showroom/update";
        if (!$lead->showroom)
        {
            $view->fail = "You must select a showroom location before selecting a date.";
        }
        if (!$lead->user)
        {
            $view->fail = "Cannot set showroom without a designer.";
        }
        return $view;
    }

    public function closingModal($id)
    {
        $view = View::make('picker');
        $lead = Lead::find($id);
        $view->pre = "Select a closing date";
        $view->url = "/lead/$lead->id/closing/update";
        if (!$lead->measure || !$lead->measure->measurer_id)
        {
            $view->fail = "You must select a Digital Measurer First";
        }
        return $view;
    }

    public function measurerModal($id)
    {
        $view = View::make('picker');
        $lead = Lead::find($id);
        $view->pre = "Select a Digital Measuring date";
        $view->url = "/lead/$lead->id/measure/update";
        if (!$lead->measure || !$lead->measure->measurer_id)
        {
            $view->fail = "You must select a Digital Measurer First";
        }
        return $view;
    }

    public function archive($id)
    {
        $lead = Lead::find($id);
        $lead->archived = 1;
        $lead->save();
        return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => 'Done!']);
    }

    public function createSecondaryLead($customerid)
    {
        $oldlead = Lead::where('customer_id', $customerid)->first();
        $oldcustomer = Customer::find($customerid);
        $oldcontact = Contact::where('customer_id', $customerid)->first();
        if (!Input::has('name'))
        {
            $lead = new Lead();
            $lead->customer_id = $customerid;
            $lead->source_id = Input::get('source_id');
            $lead->user_id = Input::get('user_id');
            $lead->save();
        }
        else
        {
            $c = new Customer;
            $c->name = Input::get('name');
            $c->address = $oldcustomer->address;
            $c->city = $oldcustomer->city;
            $c->state = $oldcustomer->state;
            $c->zip = $oldcustomer->zip;
            $c->job_address = $oldcustomer->job_address;
            $c->job_city = $oldcustomer->job_city;
            $c->job_state = $oldcustomer->job_state;
            $c->job_zip = $oldcustomer->job_zip;
            $c->save();
            $co = new Contact;
            $co->customer_id = $c->id;
            $co->name = $oldcontact->name;
            $co->email = $oldcontact->email;
            $co->mobile = $oldcontact->mobile;
            $co->home = $oldcontact->home;
            $co->alternate = $oldcontact->alternate;
            $co->primary = 1;
            $co->save();
            $lead = new Lead();
            $lead->customer_id = $c->id;
            $lead->save();
        }
    }

    public function createLead()
    {
        $lead = new Lead;
        if (Input::has('customer_id') && Input::get('customer_id') > 0)
        {
            $lead->customer_id = Input::get('customer_id');
            $this->createSecondaryLead(Input::get('customer_id'));
            return Response::json(['status' => 'success', 'action' => 'selfreload']);
        }
        else
        {
            $customer = new Customer;
            $customer->name = Input::get('name');
            $customer->address = Input::get('address');
            $customer->city = Input::get('city');
            $customer->state = Input::get('state');
            $customer->zip = Input::get('zip');
            $customer->job_address = Input::get('job_address');
            $customer->job_city = Input::get('job_city');
            $customer->job_state = Input::get('job_state');
            $customer->job_zip = Input::get('job_zip');
            $customer->save();
            $contact = new Contact;
            $contact->customer_id = $customer->id;
            $contact->name = Input::get('name');
            $contact->email = Input::get('email');
            $contact->mobile = preg_replace('/\D/', null, Input::get('mobile'));
            $contact->home = preg_replace('/\D/', null, Input::get('home'));
            $contact->alternate = preg_replace('/\D/', null, Input::get('alternate'));
            $contact->primary = 1;
            $contact->save();
            $lead->customer_id = $customer->id;
        }
        $lead->source_id = Input::get('source_id');
        $lead->user_id = Input::get('user_id');
        $lead->save();
        if (!$lead->showroom)
        {
            $showroom = new Showroom;
            $showroom->lead_id = $lead->id;
            $showroom->location = Input::get('location');
            $showroom->save();
        }
        return Response::json(['status' => 'success', 'action' => 'selfreload']);

    }

    public function quoteModal($id)
    {
        $lead = Lead::find($id);
        $view = View::make('leads.newQuote');
        $view->lead = $lead;
        return $view;
    }


    public function updateStatus($id)
    {
        $lead = Lead::find($id);
        StatusManager::setLead($lead, Input::get('value'));
        return Response::json(['success' => true]);
    }

    public function updateDesigner($id)
    {
        $lead = Lead::find($id);
        $lead->user_id = Input::get('value');
        $lead->save();
        return Response::json(['success' => true]);
    }

    public function updateLocation($id)
    {
        $lead = Lead::find($id);
        if (!$lead->showroom)
        {
            $showroom = new Showroom;
            $showroom->lead_id = $lead->id;
            $showroom->location = Input::get('value');
            $showroom->save();
        }
        else
        {
            $lead->showroom->location = Input::get('value');
            $lead->showroom->save();
        }
        return Response::json(['success' => true]);
    }

    public function notes($id)
    {
        $lead = Lead::find($id);
        $view = View::make('leads.notes');
        $view->lead = $lead;
        return $view;
    }

    /**
     * Create a note for a lead.
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notesSave($id)
    {
        $lead = Lead::find($id);
        $note = new LeadNote;
        $note->lead_id = $lead->id;
        $note->user_id = Auth::user()->id;
        $note->note = Input::get('notes');
        $note->save();
        $lead->last_note = Carbon::now();
        $lead->warning = '';
        $lead->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateShowroom($id)
    {
        $lead = Lead::find($id);
        $int = strtotime(Input::get('date') . " " . Input::get('time'));
        $date = date("Y-m-d H:i:s", $int);
        if (!$lead->user_id)
        {
            return "Cannot set showroom without a designer.";
        }

        if (!$lead->showroom || !$lead->showroom->location)
        {
            return "Cannot set showroom date without a location.";
        }
        $lead->showroom->scheduled = $date;
        $lead->showroom->save();
        StatusManager::setLead($lead, 4);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateClosing($id)
    {
        $lead = Lead::find($id);
        $int = strtotime(Input::get('date') . " " . Input::get('time'));
        $date = date("Y-m-d H:i:s", $int);
        if (!$lead->measure)
        {
            return "You must select a Digital Measurer First";
        }
        if (!$lead->measure->measurer_id)
        {
            return "You must select a Digital Measurer First";
        }

        if (!$lead->closing)
        {
            $closing = new Closing;
            $closing->lead_id = $lead->id;
            $closing->scheduled = $date;
            $closing->save();
            $lead = Lead::find($id);
        }
        else
        {
            $lead->closing->scheduled = $date;
            $lead->closing->save();
            $lead->save();
        }
        StatusManager::setLead($lead, 35);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateMeasurer($id)
    {
        $lead = Lead::find($id);
        if (!$lead->measure)
        {
            $measure = new Measure;
            $measure->lead_id = $lead->id;
            $measure->measurer_id = Input::get('value');
            $measure->save();
        }
        else
        {
            $lead->measure->measurer_id = Input::get('value');
            $lead->measure->save();
            $lead->save();
        }
        return Response::json(['success' => true]);
    }

    public function updateMeasure($id)
    {
        $lead = Lead::find($id);
        $int = strtotime(Input::get('date') . " " . Input::get('time'));
        $date = date("Y-m-d H:i:s", $int);
        if (!$lead->measure || !$lead->measure->measurer_id)
        {
            return "Unable to set Date without a Measurer.";
        }
        $lead->measure->scheduled = $date;
        $lead->measure->save();
        StatusManager::setLead($lead, 36);
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }


    /**
     * Show followups
     * @param $id
     * @param $fid
     */

    public function followup($id, $fid = null)
    {
        $lead = Lead::find($id);
        if ($lead->followups()->count() == 0)
            StatusManager::generateFollowups($lead);
        $view = View::make('leads.followups');
        $view->lead = Lead::find($id);
        $view->followup = ($fid) ? Followup::find($fid) : null;
        $this->layout->title = "Followups";
        $this->layout->content = $view;
    }




    /**
     * Update Followup
     * @param $id
     * @param $fid
     * @return array
     */
    public function followupSave($id, $fid)
    {

        $followup = Followup::find($fid);
        if ($followup->status_id != Input::get('status_id'))
        {
            $change = true;
        }
        else
        {
            $change = false;
        }
        $followup->user_id = Auth::user()->id;
        if (Input::has('status_id'))
            $followup->status_id = Input::get('status_id');
        $followup->comments = Input::get('comments');
        $followup->save();
        $data = [];
        $data['followup'] = $followup;
        $data['comment'] = Input::get('comments');
        $data['designer'] = $followup->lead->user;
        $data['user'] = Auth::user();

        try
        {
            Mail::send('emails.followup', $data, function ($message) use($data)
            {
                Log::info("Firing followup email to admin@frugalkitchens and {$data['designer']->name} ");
                $message->to($data['designer']->email, $data['designer']->name);
                $message->subject = "Followup to {$data['followup']->lead->customer->name}";
                $message->to("frugalk@frugalkitchens.com", "Frugal Admin");
            });

        } catch (Exception $e)
        {
            Log::info("Message failed: " . $e->getMessage());
        }


        if ($change)
        {
            StatusManager::setFollowup($followup, Input::get('status_id'));
            // Email.

        }

        return ['status' => 'success', 'action' => 'reload', 'url' => "/lead/$id/followups"];
    }

    /**
     * Close a followup
     * @param $id
     * @param $fid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function closeFollowup($id, $fid)
    {

        $followup = Followup::find($fid);
        $followup->closed = 1;
        $followup->save();
        NotificationEngine::removeOldNotifications($followup);
        return Redirect::to("/lead/$id/followups");

    }


    private function renderLead(Lead $lead)
    {
        $i = 0;
        $dlist = User::whereLevelId(4)->get();
        $designers = [];
        foreach ($dlist AS $list)
        {
            $designers[] = ['value' => $list->id, 'text' => $list->name];
        }
        $designers[] = ['value' => 5, 'text' => 'Rich Bishop'];
        $measurers = [];
        $mlist = User::whereLevelId(7)->get();
        foreach ($mlist AS $list)
        {
            $measurers[] = ['value' => $list->id, 'text' => $list->name];
        }
        $measurers[] = ['value' => 5, 'text' => 'Rich Bishop'];


        // Lead Warning Checker here.
        $leadColor = null;
        switch ($lead->warning)
        {
            case 'R' :
                $leadColor = 'text-danger';
                break;
            case 'Y' :
                $leadColor = 'text-warning';
                break;
            default :
                $leadColor = null;
        }
        $leadIcon = $leadColor ? "<a class='mjax' data-target='#workModal' href='/lead/$lead->id/notes'><i class='fa fa-warning {$leadColor}'></i></a> " : null;

        $follow = ($lead->followups()->count() > 0) ? "<a href='/lead/$lead->id/followups'><i class='fa fa-phone'></i></a>" : null;
        $createQuote = "<span class='pull-right'>
                  <a class='mjax tooltiped' data-original-title='Spawn Quote from Lead' data-target='#newQuote' href='/lead/$lead->id/quote'><i class='fa fa-arrow-right'></i></a>
                  $follow
                  </span>";
        $color = (isset($lead->status->name) && $lead->status->name == 'Sold') ? 'color-success' : null;
        $color = (!$color && $lead->quotes->count() > 0) ? 'color-info' : $color;
        $color = $this->getNoteStatus($lead, $color);
        $showroom = ($lead->showroom && $lead->showroom->scheduled->timestamp > 0) ?
            $lead->showroom->scheduled->format('m/d/y h:i a') : "No Showroom Scheduled";
        $showroomSet = $lead->showroom && $lead->showroom->user ? " <i>(".$lead->showroom->user->name.")</i>" : null;
        $closing = ($lead->closing) ? $lead->closing->scheduled->format('m/d/y h:i a') : "No Closing Date";
        $closingSet = ($lead->closing && $lead->closing->user) ? " <i>(".$lead->closing->user->name.")</i>" : null;
        $measure = ($lead->measure) ? $lead->measure->scheduled->format('m/d/y h:i a') : "No Digital Measure";
        $measureSet = ($lead->measure && $lead->measure->lastuser) ? " <i>(".$lead->measure->lastuser->name.")</i>" : null;
        $designer = (isset($lead->user->name)) ? $lead->user->name : "No Designer Assigned";
        $where = ($lead->showroom) ? $lead->showroom->location : "Need Location";
        $where = Editable::init()->id("idLo_$lead->id")->placement('bottom')->pk($lead->id)->type('select')->title("Location")
                         ->linkText($where)
                         ->source("/ajax/locations")->url("/lead/$lead->id/location/update")->render();

        $status = ($lead->status) ? $lead->status->name : "New";
        $statusSet = $lead->laststatus && $lead->laststatus->name ? " <i>(".$lead->laststatus->name.")</i>" : null;
        if ( ($status != 'Quote Provided') || Auth::user()->id == 5 || Auth::user()->id == 51)
            $status = Editable::init()->id("idS_$lead->id")->placement('bottom')->pk($lead->id)->type('select')->title("Status")->linkText($status)
                              ->source("/ajax/status")->url("/lead/$lead->id/status/update")->render();

        $showroom = "<a class='mjax' data-target='#workModal' href='/lead/$lead->id/showroom/update'>$showroom</a>";

        $closing = "<a class='mjax' data-target='#workModal' href='/lead/$lead->id/closing/update'>$closing</a>";
        $measure = "<a class='mjax' data-target='#workModal' href='/lead/$lead->id/measure/update'>$measure</a>";
        $designer = Editable::init()->id("idDe_$lead->id")->placement('bottom')->pk($lead->id)->type('select')->title("Select Designer")->linkText(($lead->user) ? $lead->user->name : "No Designer Assigned")
                            ->source($designers)->url("/lead/$lead->id/designer/update")->render();
        $showroom .= " in $where";
        $icon = "<span class='pull-right'><a class='get tooltiped' data-original-title='Archive' href='/lead/$lead->id/archive'><i class='fa fa-eraser'></i></a>";
        $icon .= " &nbsp;&nbsp; <a class='mjax' data-target='#workModal' href='/lead/$lead->id/notes'><i class='fa fa-edit'></i></a></span>";
        $measurer = ($lead->measure && $lead->measure->measurer_id) ? $lead->measure->user->name : "No Measurer";
        $measurer = Editable::init()->id("idDe_$lead->id")->placement('bottom')->pk($lead->id)->type('select')->title("Select Measurer")->linkText($measurer)
                            ->source($measurers)->url("/lead/$lead->id/measurer/update")->render();
/*
        if (Input::has('followups'))
        {
            if ($color != 'color-danger') return false;
        }
*/
        return
            [
            "{$leadIcon}<a href='/profile/{$lead->customer->id}/view'>{$lead->customer->name}</a>$icon",
            $lead->customer_id,
            $lead->created_at->diffInDays(),
            $status . $statusSet . $createQuote,
            $lead->source ? $lead->source->type : "No Source",
            $showroom . $showroomSet,
            $closing . $closingSet,
            $measure . " ($measurer)" . $measureSet,
            $designer
        ];

    }
    private function getNoteStatus($lead, $color)
    {
        $now = Carbon::now();
        if ($lead->status && $lead->status->followup_expiration)
        {
            $days = $lead->status->followup_expiration;
            $lastNote = $lead->notes()->orderBy('created_at', 'DESC')->first();
            if (!$lastNote)
            {
                // There is no note
                $lastUpdated = $lead->created_at->addDays($days);
                if ($lastUpdated < $now)
                    return "color-danger";
            }
            else
            {
                // There is a note
                $lastUpdated = $lastNote->created_at->addDays($days);
                if ($lastUpdated < $now)
                    return "color-danger";
            }

        }
        return $color;
    }


}
