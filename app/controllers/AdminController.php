<?php
use vl\core\Google;

class AdminController extends BaseController
{
    public $layout = "layouts.main";


    public function index()
    {
        $view = View::make('admin.index')->render();
        $nav = View::make('admin.navigation')->render();
        $this->layout->title = "Admin";
        $this->layout->content = $nav . $view;
    }

    public function googleAuthorize($id)
    {
        Session::put("authid", $id);
        Session::save();
        Log::info("Setting User Id for Google Authorization of $id");
        return Redirect::to(Google::authenticateUser(User::find($id)));
    }

    public function googleCallback()
    {
        $user = User::find(Session::get('authid'));
        Google::setAuthToken($user);
        return Redirect::to('/admin');
    }

    public function deleteAction($id, $eid)
    {
        $expiration = Expiration::find($eid);
        // Delete all actions.
        Notification::whereExpirationId($expiration->id)->delete();
        $expiration->actions()->delete();
        $expiration->delete();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function delete($type, $id)
    {
        switch ($type)
        {
            case 'users':
                $obj = User::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'levels':
                $obj = Level::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'designations':
                $obj = Designation::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'sources':
                $obj = LeadSource::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'questionaire':
                $obj = Question::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'vendors':
                $obj = Vendor::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'granite':
                $obj = Granite::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'sinks':
                $obj = Sink::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'appliances':
                $obj = Appliance::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'cabinets':
                $obj = Cabinet::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'hardware':
                $obj = Hardware::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'accessories':
                $obj = Accessory::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'pricing':
                $obj = Extra::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'punches':
                $obj = Punch::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'responsibilities' :
                $obj = Responsibility::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'addons' :
                $obj = Addon::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'authorizations' :
                $obj = AuthorizationList::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'faq' :
                $obj = Faq::find($id);
                $obj->active = 0;
                $obj->save();
                break;
            case 'promotions' :
                $obj = Promotion::find($id);
                $obj->delete();
                Quote::where('promotion_id', $id)->update(['promotion_id' => 0]);
                break;

        }


        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/admin/$type"]);
    }

    public function attachmentModal($id)
    {
        $view = View::make('admin.attachmentModal');
        $view->action = Action::find($id);
        return $view;
    }

    public function attachment($id)
    {
        $action = Action::find($id);

        if (Input::hasFile('attachment'))
        {
            Log::info("Has attachment");
            $origname = Input::file('attachment')->getClientOriginalName();
            $path = "statuses/$action->id/";
            Input::file('attachment')->move($path, $origname);
            $action->attachment = $origname;
            $action->save();
        }
        return Redirect::back();
    }

    public function editor($type, $id = null)
    {
        $nav = View::make('admin.navigation')->render();
        $view = View::make("admin.{$type}");
        if ($id)
        {
            $view->id = $id;
        }

        $view = $view->render();
        $this->layout->title = "Administration";
        $this->layout->content = $nav . $view;
    }

    public function sms($id)
    {
        $view = View::make('admin.sms');
        $view->user = User::find($id);
        return $view;

    }

    public function editorSave($type, $id = null)
    {
        switch ($type)
        {
            case 'leads':    // Lead Manager
                foreach (Input::all() AS $key => $val)
                {
                    $setting = Setting::whereName($key)->first();
                    if (!$setting)
                    {
                        continue;
                    }

                    $setting->val = $val;
                    $setting->save();
                }

                break;
            case 'payments':
                $details = Input::all();
                if (!isset($details['type']))
                {
                    $details['type'] = 'C';
                }    // C = Credit, A = ACH
                $details['phone'] = '';
                $details['email'] = '';
                $name = $details['type'] == 'C' ? explode(" ", Input::get('cc_name')) : explode(" ", Input::get('ach_name'));
                $details['first'] = $name[0];
                $details['last'] = $name[1];

                //$bill = vl\libraries\Bluepay::init("LIVE", "100155724209", "100155724210", "1HECUX9KVB/KUAMA6WRU/TKEUUH0UZE/");
                $bill = vl\libraries\Bluepay::init("LIVE", "100215808240", "100215808241", "ZEINCIUSPDPWFQRZMYK8VYAF75LEYMVY");

                try
                {
                    if ($details['type'] == 'C')
                    {
                        $result = $bill->setCustomer($details)
                                       ->isSale()
                                       ->setAmount(Input::get('amount'))
                                       ->setCard($details['cc_number'], $details['cc_cvv'], $details['cc_exp'])
                                       ->memo("Frugal Credit Card Payment")
                                       ->create();

                        switch ($details['cc_number'][0])
                        {
                            case 3:
                                $sub = "American Express";
                                break;
                            case 4:
                                $sub = "Visa";
                                break;
                            case 5:
                                $sub = "Mastercard";
                                break;
                            default:
                                $sub = "Unknown";
                                break;
                        }
                        $four = substr($details['cc_number'], -4);
                    }
                    else
                    {
                        $result = $bill->setCustomer($details)
                                       ->isSale()
                                       ->setAmount(Input::get('amount'))
                                       ->setACH($details['ach_route'], $details['ach_account'], $details['ach_type'])
                                       ->memo("Frugal ACH Payment")
                                       ->create();
                        $four = substr($details['ach_account'], -4);
                        $sub = $details['ach_type'] == 'C' ? 'Checking' : 'Savings';
                    }
                } catch (Exception $e)
                {
                    return Response::json(['status' => 'danger', 'gtitle' => 'Transaction Declined', 'gbody' => $e->getMessage()]);
                }
                return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/admin/payments?trans={$result->transId}"]);
                break;

            case 'users':
                $user = ($id) ? User::find($id) : new User;
                $user->name = Input::get('name');
                $user->email = Input::get('email');
                if (Input::has('password'))
                {
                    $user->password = Hash::make(Input::get('password'));
                }
                $user->level_id = Input::get('level_id');
                $user->designation_id = Input::get('designation_id');
                $user->color = Input::get('color');
                $user->mobile = str_replace(".", null, Input::get('mobile'));
                $user->frugal_number = str_replace(".", null, Input::get('frugal_number'));
                $user->save();
                break;
            case 'levels':
                $level = ($id) ? Level::find($id) : new Level;
                $level->name = Input::get('name');
                $level->save();
            case 'designations':
                $designation = ($id) ? Designation::find($id) : new Designation;
                $designation->name = Input::get('name');
                $designation->override_email = Input::get('override_email') ?: '';
                $designation->override_sms = preg_replace('/\D/', null, Input::get('override_sms'));
                $designation->save();
                break;

            case 'sources':
                $source = ($id) ? LeadSource::find($id) : new LeadSource;
                $source->type = Input::get('type');
                $source->save();
                break;
            case 'questionaire':
                $question = ($id) ? Question::find($id) : new Question;
                $question->question = Input::get('question');
                $question->response_type = Input::get('response_type');
                $question->stage = Input::get('stage');
                $question->designation_id = Input::get('designation_id');
                $question->contract = Input::has('contract') ? 1 : 0;
                $question->contract_format = Input::get('contract_format');
                $question->question_category_id = Input::get('category_id');
                $question->vendor_id = Input::get('vendor_id');
                $question->small_job = Input::has('small_job') ? 1 : 0;
                $question->on_checklist = Input::has('on_checklist') ? 1 : 0;
                $question->on_job_board = Input::has('on_job_board') ? 1 : 0;

                $question->save();
                if (!$question->condition)
                {
                    $condition = new Condition;
                    $condition->question_id = $question->id;
                    $condition->save();
                }
                break;

            case 'vendors':
                $vendor = ($id) ? Vendor::find($id) : new Vendor;
                $vendor->name = Input::get('name');
                $vendor->tts = Input::get('tts');
                $vendor->confirmation_days = Input::get('confirmation_days');
                $vendor->multiplier = Input::get('multiplier');
                $vendor->freight = Input::get('freight');
                $vendor->buildup = Input::get('buildup');
                $vendor->colors = Input::get('colors');
                $vendor->wood_products = Input::has('wood_products') ? 1 : 0;
                $vendor->save();
                break;
            case 'granite':
                $granite = ($id) ? Granite::find($id) : new Granite;
                $granite->name = Input::get('name');
                $granite->price = Input::get('price');
                $granite->removal_price = Input::get('removal_price');
                $granite->save();
                break;
            case 'sinks':
                $sink = ($id) ? Sink::find($id) : new Sink;
                $sink->name = Input::get('name');
                $sink->price = Input::get('price');
                $sink->material = Input::get('material');
                $sink->save();
                break;

            case 'appliances':
                $appliance = ($id) ? Appliance::find($id) : new Appliance;
                $appliance->name = Input::get('name');
                $appliance->price = Input::get('price');
                $appliance->countas = Input::get('countas');
                $appliance->designation_id = Input::get('designation_id');
                $appliance->save();
                break;

            case 'cabinets':
                $cabinet = ($id) ? Cabinet::find($id) : new Cabinet;
                $cabinet->name = Input::get('name');
                $cabinet->price = Input::get('price');
                $cabinet->frugal_name = Input::get('frugal_name');
                $cabinet->vendor_id = Input::get('vendor_id');
                $cabinet->description = Input::get('description');
                if (Input::hasFile('image'))
                {
                    $origname = Input::file('image')->getClientOriginalName();
                    $path = public_path() . "/cabinet_images/";
                    Input::file('image')->move($path, $origname);
                    $cabinet->image = $origname;
                }
                $cabinet->save();
                return Redirect::to("/admin/cabinets");
                break;
            case 'hardware':
                $hardware = ($id) ? Hardware::find($id) : new Hardware;
                $hardware->sku = Input::get('sku');
                $hardware->description = Input::get('description');
                $hardware->vendor_id = Input::get('vendor_id');
                $hardware->price = Input::get('price');
                if (Input::hasFile('image'))
                {
                    $origname = Input::file('image')->getClientOriginalName();
                    $path = public_path() . "/hardware_images/";
                    Input::file('image')->move($path, $origname);
                    $hardware->image = $origname;
                }
                $hardware->save();
                return Redirect::to("/admin/hardware");

                break;
            case 'accessories':
                $accessory = ($id) ? Accessory::find($id) : new Accessory;
                $accessory->sku = Input::get('sku');
                $accessory->name = Input::get('name');
                $accessory->description = Input::get('description');
                $accessory->vendor_id = Input::get('vendor_id');
                $accessory->price = Input::get('price');
                $accessory->on_site = Input::has('on_site') ? 1 : 0;
                if (Input::hasFile('image'))
                {
                    $origname = Input::file('image')->getClientOriginalName();
                    $path = public_path() . "/acc_images/";
                    Input::file('image')->move($path, $origname);
                    $accessory->image = $origname;
                    $accessory->save();
                    return Redirect::to("/admin/accessories");
                }
                $accessory->save();
                break;
            case 'pricing':
                $extra = ($id) ? Extra::find($id) : new Extra;
                $extra->name = Input::get('name');
                $extra->price = Input::get('price');
                $extra->designation_id = Input::get('designation_id');
                $extra->save();
                break;
            case 'punches':
                $punch = ($id) ? Punch::find($id) : new Punch;
                $punch->question = Input::get('question');
                $punch->designation_id = Input::get('designation_id');
                $punch->save();
                break;
            case 'responsibilities' :
                $responsibility = ($id) ? Responsibility::find($id) : new Responsibility;
                $responsibility->name = Input::get('name');
                $responsibility->save();
                break;
            case 'addons' :
                $addon = ($id) ? Addon::find($id) : new Addon;
                $addon->item = Input::get('item');
                $addon->price = Input::get('price');
                $addon->contract = Input::get('contract');
                $addon->active = 1;
                $addon->designation_id = Input::get('designation_id');
                $addon->save();
                break;
            case 'authorizations' :
                $authorization = ($id) ? AuthorizationList::find($id) : new AuthorizationList;
                $authorization->item = Input::get('item');
                $authorization->active = 1;
                $authorization->save();
                break;

            case 'dynamic':
                foreach (Input::all() AS $key => $val)
                {
                    $setting = Setting::whereName($key)->first();
                    if (!$setting)
                    {
                        continue;
                    }

                    $setting->val = $val;
                    $setting->save();
                }
                break;

            case 'promotions' :
                $promotion = $id ? Promotion::find($id) : new Promotion;
                $promotion->name = Input::get('name');
                $promotion->active = Input::get('active') ? 1 : 0;
                $promotion->modifier = Input::get('modifier');
                $promotion->condition = Input::get('condition');
                $promotion->discount_amount = Input::get('amount');
                $promotion->qualifier = Input::get('qualifier');
                $promotion->verbiage = Input::get('verbiage');
                $promotion->save();
                break;

            case 'statuses':
                $status = ($id) ? Status::find($id) : new Status;
                $status->name = Input::get('name');
                $status->stage_id = 1;
                $status->followup_status = Input::has('followup_status') ? 1 : 0;
               // $status->followup_expiration = Input::get('followup_expiration');
                $status->followup_lock = Input::has('followup_lock') ? 1 : 0;
                $status->save();
                break;

            case 'faq' :
                $faq = ($id) ? Faq::find($id) : new Faq;
                $faq->question = Input::get('question');
                $faq->answer = Input::get('answer');
                $faq->figure = Input::get('figure');
                $faq->type = Input::get('type');
                $faq->save();
                if (Input::hasFile('image'))
                {
                    $origname = Input::file('image')->getClientOriginalName();
                    $path = public_path() . "/faq/";
                    Input::file('image')->move($path, $origname);
                    $faq->image = $origname;
                    $faq->save();
                }
                    return Redirect::to("/admin/faq");

                break;

        } //sw
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function showExpiration($id, $eid, $aid = null)
    {
        $nav = View::make('admin.navigation')->render();
        $view = View::make('admin.statuses')->withId($id)->withEid($eid)->withAid($aid)->render();
        $this->layout->title = "Administration";
        $this->layout->content = $nav . $view;
    }

    public function saveExpiration($id, $eid = null)
    {
        $status = Status::find($id);
        $expiration = $eid ? Expiration::find($eid) : new Expiration;
        $expiration->status_id = $status->id;
        $expiration->name = Input::get('name');
        $expiration->expires = Input::get('expires') * 60 * 60;
        $expiration->expires_before = Input::get('expires_before') * 60 * 60;
        $expiration->expires_after  = Input::get('expires_after') * 60 * 60;
        $expiration->type = Input::get('type');
        $expiration->warning = Input::get('warning');
        $expiration->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function saveAction($id, $eid, $aid = null)
    {
        $expiration = Expiration::find($eid);
        $action = $aid ? Action::find($aid) : new Action;
        $action->description = Input::get('description');
        $action->status_expiration_id = $expiration->id;
        $action->sms = Input::has('sms') ? 1 : 0;
        $action->email = Input::has('email') ? 1 : 0;
        $action->email_subject = Input::get('email_subject');
        $action->email_content = Input::get('email_content');
        $action->sms_content = Input::get('sms_content');
        $action->designation_id = Input::get('designation_id');
        $action->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function updateCondition($qid)
    {
        $question = Question::find($qid);
        $question->condition->answer = Input::get('answer');
        $question->condition->operand = Input::get('operand');
        $question->condition->amount = Input::get('amount');
        $question->condition->once = Input::has('once') ? 1 : 0;
        $question->condition->save();
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    public function editorDelete($type, $id)
    {
        switch ($type)
        {
            case 'pricing':
                Extra::find($id)->delete();
                break;
            case 'punches':
                Punch::find($id)->delete();
                break;
        }
        return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/admin/$type"]);
    }

    public function setGranitePrice($id)
    {
        $price = trim(Input::get('value'));
        if (!is_numeric($price))
            return "You must enter a value.";
        $granite = Granite::find($id);
        $granite->price = $price;
        $granite->save();
        return Response::json(['success' => true]);
    }
}