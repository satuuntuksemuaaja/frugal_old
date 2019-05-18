<?php
class FrugalSeeder extends Seeder
{
  public function decode($string)
  {
      return unserialize(@gzuncompress(stripslashes(base64_decode(strtr($string, '-_,', '+/=')))));
  }



  public function run()
  {
      Eloquent::unguard();
      DB::table('sources')->truncate();
      DB::table('designations')->truncate();
      DB::table('users')->truncate();
      DB::table('levels')->truncate();
      DB::table('customers')->truncate();
      DB::table('contacts')->truncate();
      DB::table('statuses')->truncate();
      DB::table('leads')->truncate();
      DB::table('stages')->truncate();
      DB::table('status_expirations')->truncate();
      DB::table('status_expiration_actions')->truncate();
      DB::table('showrooms')->truncate();
      DB::table('closings')->truncate();
      DB::table('measures')->truncate();
      DB::table('vendors')->truncate();
      DB::table('cabinets')->truncate();
      DB::table('granites')->truncate();
      DB::table('accessories')->truncate();
      DB::table('hardwares')->truncate();
      DB::table('sinks')->truncate();
      DB::table('extras')->truncate();
      DB::table('appliances')->truncate();
      DB::table('quotes')->truncate();
      DB::table('questions')->truncate();
      DB::table('quote_questions')->truncate();
      DB::table('conditions')->truncate();
      DB::table('settings')->truncate();
      DB::table('files')->truncate();
      DB::table('jobs')->truncate();
      DB::table('job_items')->truncate();
      DB::table('job_schedules')->truncate();
      DB::table('ffts')->truncate();
      DB::table('punches')->truncate();
      DB::table('punch_answers')->truncate();
      DB::table('quote_cabinets')->truncate();
    $this->command->info("Building Sources..");
    $this->sourceMigration();
     $this->command->info("Building Settings..");
     $this->settingsMigration();
     $this->command->info("Building Designations...");
     $this->designationMigration();
     $this->command->info("Building Users...");
     $this->userMigration();
     $this->command->info("Building Vendors...");
     $this->vendorMigration();
     $this->command->info("Building Cabinets...");
     $this->cabinetMigration();
     $this->command->info("Building Granite...");
     $this->graniteMigration();
     $this->command->info("Building Accessories...");
     $this->accessoryMigration();
     $this->command->info("Building Hardware...");
     $this->hardwareMigration();
     $this->command->info("Building Sinks...");
     $this->sinkMigration();
     $this->command->info("Building Extras...");
     $this->extrasMigration();
     $this->command->info("Building Appliances...");
     $this->applianceMigration();
     $this->command->info("Building Questions...");
     $this->questionsMigration();
     $this->command->info("Building Clients...");
     $this->clientMigration();
     $this->command->info("Building Stages...");
     $this->stageMigration();
     $this->command->info("Building Statuses...");
     $this->statusMigration();
     $this->command->info("Building Leads...");
     $this->leadsMigration();
     $this->command->info("Building Quotes...");
     $this->quoteMigration();
     $this->command->info("Breaking down Questions to Answer table..");
     $this->quoteQuestionaireMigration();
     $this->command->info("Conditional Seeder Running..");
     $this->conditionalSeeder();
     $this->command->info('Migrating Files...');
     $this->filesMigration();
      $this->command->info("Running Job Migrator..");
      $this->jobMigration();
      $this->command->info("Building Final Touch..");
    $this->fftMigration();
    $this->command->info("Migrating FFT/Warranty Punches..");
    $this->punchMigration();
    $this->command->info("Adding Punchlists for Mobile users");
    $this->mobileMigration();
  }

  public function sourceMigration()
  {
    $oldSources = DB::table('frugal.sources')->get();
    foreach ($oldSources AS $oldSource)
    {
      $source = new LeadSource;
      $source->id = $oldSource->id;
      $source->type = $oldSource->source_type;
      $source->save();
    }
  }

  public function applianceMigration()
  {
    $oldApps = DB::table('frugal.appliances')->get();
    foreach ($oldApps as $oldApp)
    {
      $app = new Appliance;
      $app->id = $oldApp->id;
      $app->name = $oldApp->appliance_name;
      $app->price = $oldApp->appliance_price;
      $app->designation_id = $oldApp->designation_id ?: 0;
      $app->countas = $oldApp->appliance_countas;
      $app->save();
    }
  }

  public function questionsMigration()
  {
    $oldQs = DB::table('frugal.questions')->get();
    foreach ($oldQs AS $oldQ)
    {
      $q = new Question;
      $q->id = $oldQ->id;
      $q->question = $oldQ->question_question;
      $q->response_type = $oldQ->question_responsetype;
      $q->stage = $oldQ->question_stage;
      $q->designation_id = $oldQ->question_update_designation_id;
      $q->contract = $oldQ->question_contract ? 1 : 0;
      $q->contract_format = $oldQ->question_contractformat ?: '';
      $q->save();
    }




  }

  public function extrasMigration()
  {
    $oldExtras = DB::table('frugal.extras')->get();
    foreach ($oldExtras AS $oldExtra)
    {
      $extra = new Extra;
      $extra->id = $oldExtra->id;
      $extra->name = $oldExtra->extra_line;
      $extra->price = $oldExtra->extra_price;
      $extra->save();
    }
  }

  public function sinkMigration()
  {
    $oldSinks = DB::table('frugal.sinks')->get();
    foreach ($oldSinks AS $oldSink)
    {
      $sink = new Sink;
      $sink->id = $oldSink->id;
      $sink->name = $oldSink->sink_name;
      $sink->price = $oldSink->sink_price;
      $sink->material = $oldSink->sink_material;
      $sink->save();
    }
  }

  public function hardwareMigration()
  {
    $oldHards = DB::table('frugal.hardwares')->get();
    foreach ($oldHards AS $oldHard)
    {
      $hard = new Hardware;
      $hard->id = $oldHard->id;
      $hard->sku = $oldHard->hardware_sku;
      $hard->description = $oldHard->hardware_desc;
      $hard->vendor_id = $oldHard->vendor_id;
      $hard->price = $oldHard->hardware_price;
      $hard->save();
    }
  }


  public function cabinetMigration()
  {
    $oldCabs = DB::table('frugal.cabinets')->get();
    foreach ($oldCabs AS $oldCab)
    {
      $cabinet = new Cabinet;
      $cabinet->id = $oldCab->id;
      $cabinet->frugal_name = $oldCab->cabinet_fname;
      $cabinet->name = $oldCab->cabinet_name;
      $cabinet->price = $oldCab->cabinet_price ?: 0;
      $cabinet->vendor_id = $oldCab->vendor_id;
      $cabinet->save();
    }

  }

  public function accessoryMigration()
  {
    $oldAccs = DB::table('frugal.accessories')->get();
    foreach ($oldAccs AS $oldAcc)
    {
      $acc = new Accessory;
      $acc->id = $oldAcc->id;
      $acc->sku = $oldAcc->accessory_sku;
      $acc->description = $oldAcc->accessory_desc;
      $acc->name = $oldAcc->accessory_name;
      $acc->price = $oldAcc->accessory_price;
      $acc->vendor_id = $oldAcc->vendor_id;
      $acc->on_site = $oldAcc->accessory_onsite ? 1 : 0;
      $acc->save();
    }
  }

  public function graniteMigration()
  {
    $oldGranites = DB::table('frugal.granites')->get();
    foreach ($oldGranites AS $oldGranite)
    {
      $granite = new Granite;
      $granite->id = $oldGranite->id;
      $granite->name = $oldGranite->granite_name;
      $granite->price = $oldGranite->granite_price;
      $granite->removal_price = $oldGranite->granite_removal_price ?: 0;
      $granite->save();
    }
  }



  public function vendorMigration()
  {
    $oldVendors = DB::table('frugal.vendors')->get();
    foreach ($oldVendors AS $oldVendor)
    {
      $vendor = new Vendor;
      $vendor->id = $oldVendor->id;
      $vendor->name = $oldVendor->vendor_name;
      $vendor->tts = $oldVendor->vendor_tts;
      $vendor->multiplier = $oldVendor->vendor_multiplier;
      $vendor->freight = $oldVendor->vendor_freight;
      $vendor->buildup = ($oldVendor->vendor_buildup) ?: '';
      $vendor->colors = $oldVendor->vendor_colors ?: '';
      $vendor->save();
    }
  }

  public function quoteMigration()
  {
    $oldQuotes = DB::table('frugal.quotes')->get();
    foreach ($oldQuotes AS $oldQuote)
    {
      $quote = new Quote;
      $oldLead = Lead::find($oldQuote->lead_id);
      if (!$oldLead)
      {
        $this->command->error("Lead not found for Quote: $oldQuote->id");
        continue;
      }
      $quote->id = $oldQuote->id;
      $quote->lead_id = $oldLead->id;
      $quote->created_at = date("Y-m-d H:i:s", $oldQuote->quote_ts);
      $quote->accepted = ($oldQuote->quote_accepted) ? 1 : 0;
      $quote->final = ($oldQuote->quote_final) ? 1 : 0;
      $meta = [];
      $meta = $this->decode($oldQuote->quote_meta);

      $cabdata = $this->decode($oldQuote->quote_cabdata);
      $cabdata2 = $this->decode($oldQuote->quote_cabdata2);
      $meta['meta'] = $meta;
      $meta['settings'] = $this->decode($oldQuote->quote_settings);

      // Convert sink_id and sink2_id
      $meta['meta']['sinks'] = [];
      if (isset($meta['meta']['sink_id']))
      {
        $meta['meta']['sinks'][] = $meta['meta']['sink_id'];
        unset($meta['meta']['sink_id']);
      }
      if (isset($meta['meta']['sink2_id']))
      {
        $meta['meta']['sinks'][] = $meta['meta']['sink2_id'];
        unset($meta['meta']['sink2_id']);
      }

      $quote->meta = serialize($meta);
      $quote->type = $oldQuote->quote_type;
      $quote->title = ($oldQuote->quote_title) ?: '';
      $quote->suspended = ($oldQuote->quote_final) ? 1 : 0;
      $quote->save();
      // Add Cabinets If exist
      if ($cabdata)
      {
        $cabinet = new QuoteCabinet;
        $cabinet->quote_id = $quote->id;
        $cabinet->data = serialize($cabdata);
        $cabinet->cabinet_id = (isset($meta['meta']['cabinet_id'])) ? $meta['meta']['cabinet_id'] : 0;
        $cabinet->color =  (isset($meta['meta']['cabinet_color'])) ? $meta['meta']['cabinet_color'] : '';
        $cabinet->inches = (isset($meta['meta']['quote_cabinet_inches_floor'])) ?
          $meta['meta']['quote_cabinet_inches_floor'] : 0;
        $cabinet->price = (isset($meta['meta']['cabinet_price'])) ? $meta['meta']['cabinet_price'] : 0;
        $cabinet->save();
      }
      if ($cabdata2)
      {
        $cabinet = new QuoteCabinet;
        $cabinet->quote_id = $quote->id;
        $cabinet->data = serialize($cabdata);
        $cabinet->cabinet_id = (isset($meta['meta']['cabinet_id2'])) ? $meta['meta']['cabinet_id2'] : 0;
        $cabinet->color =  (isset($meta['meta']['cabinet_color2'])) ? $meta['meta']['cabinet_color2'] : '';
        $cabinet->inches = (isset($meta['meta']['quote_cabinet_inches_floor2'])) ?
          $meta['meta']['quote_cabinet_inches_floor2'] : 0;
        $cabinet->price = (isset($meta['meta']['cabinet_price2'])) ? $meta['meta']['cabinet_price2'] : 0;
        $cabinet->save();
      }

     }
  }


  public function designationMigration()
  {
    $oldDesignations = DB::table('frugal.designations')->get();
    foreach ($oldDesignations as $oldDes)
      Designation::create(['name' => $oldDes->designation_name, 'id' => $oldDes->id]);
    Designation::create(['name' => 'Customer']);
    Designation::create(['name' => 'Employee']);
    Designation::create(['name' => 'Shipping']);
  }


  public function stageMigration()
  {
    Stage::create(['name' => 'Lead']);
    Stage::create(['name' => 'Job']);
  }

  public function statusMigration()
  {
    $oldStatuses = DB::table('frugal.statuses')->get();
    foreach ($oldStatuses AS $oldStatus)
    {
      $status = new Status;
      $status->id = $oldStatus->id;
      $status->name = $oldStatus->status_name;
      $status->stage_id = ($oldStatus->status_islead) ? 1 : 2;
      $status->save();

      // Need to create an action for on-set with a expiration of 0.
       if ($oldStatus->status_customer_notification) // Email Customer on set
        {
          $expiration = new Expiration;
          $expiration->status_id = $status->id;
          $expiration->expires = 0;
          $expiration->name = 'On Set Action';
          $expiration->save();
          $template = $this->getTemplate($oldStatus->status_customer_notification);
          $action = new Action;
          $action->status_expiration_id = $expiration->id;
          $action->description = $template->template_name;
          $action->sms = ($oldStatus->status_smscustomer) ? 1 : 0;
          $action->email = ($template->template_subject) ? 1 : 0;
          $action->email_content = $template->template_customer;
          $action->email_subject = $template->template_subject;
          $action->sms_content = $template->template_customer;
          $action->designation_id = 7; // Send to customer.
          $action->save();
        }// customer

        if ($oldStatus->status_admin_notification) // Email Lead Owner (designer probably)
        {
          $expiration = new Expiration;
          $expiration->status_id = $status->id;
          $expiration->expires = 0;
          $expiration->name = 'On Set Action';
          $expiration->save();
          $template = $this->getTemplate($oldStatus->status_admin_notification);
          $action = new Action;
          $action->status_expiration_id = $expiration->id;
          $action->description = $template->template_name;
          $action->sms = ($oldStatus->status_smsadmin) ? 1 : 0;
          $action->email = ($template->template_subject) ? 1 : 0;
          $action->email_content = $template->template_admin;
          $action->email_subject = $template->template_subject;
          $action->sms_content = $template->template_admin;
          $action->designation_id = 8; // Send to employee.
          $action->save();
        }// designer

      // Get Expirations and build
      $expirations = DB::table('frugal.status_times')->whereStatusId($oldStatus->id)->get();
      foreach ($expirations AS $exp)
      {
        $time = $exp->status_time_expires * 86400;
        $expiration = new Expiration;
        $expiration->status_id = $status->id;
        $expiration->expires = $time;
        $expiration->name = 'Expires in ' . $exp->status_time_expires . ' days';
        $expiration->save();
        // Now we build what to do on this expire
        if ($exp->status_time_customer_expires_notification) // Email Customer on set
        {
          $template = $this->getTemplate($exp->status_time_customer_expires_notification);
          $action = new Action;
          $action->status_expiration_id = $expiration->id;
          $action->description = $template->template_name;
          $action->sms = ($exp->status_smscustomer) ? 1 : 0;
          $action->email = ($template->template_subject) ? 1 : 0;
          $action->email_content = $template->template_customer;
          $action->email_subject = $template->template_subject;
          $action->sms_content = $template->template_customer;
          $action->designation_id = 7; // Send to customer.
          $action->save();
        }// customer

        if ($exp->status_time_admin_expires_notification) // Email Lead Owner (designer probably)
        {
          $template = $this->getTemplate($exp->status_time_admin_expires_notification);
          $action = new Action;
          $action->status_expiration_id = $expiration->id;
          $action->description = $template->template_name;
          $action->sms = ($exp->status_smsadmin) ? 1 : 0;
          $action->email = ($template->template_subject) ? 1 : 0;
          $action->email_content = $template->template_admin;
          $action->email_subject = $template->template_subject;
          $action->sms_content = $template->template_admin;
          $action->designation_id = 8; // Send to employee.
          $action->save();
        }// designer

      } // if time expirations set.
    }

  }

  public function getTemplate($id)
  {
    return DB::table('frugal.templates')->find($id);
  }

  public function userMigration()
  {
    $oldUsers = DB::table('frugal.users')->get();
    foreach ($oldUsers AS $oldUser)
    {
      $user = new User;
      $user->id = $oldUser->id;
      $user->google = $oldUser->user_google_token;
      $user->name = $oldUser->user_name;
      $user->password = Hash::make('frugal');
      $user->bypass = $oldUser->user_password;
      $user->email = $oldUser->user_email;
      $designation = $oldUser->user_designation ? : 0;
      $user->level_id = $oldUser->level_id;
      $user->designation_id = $designation;
      $user->mobile = preg_replace('/\D/', '', $oldUser->user_cell);
      $user->save();
    }

    $oldLevels = DB::table('frugal.levels')->get();
    foreach ($oldLevels as $oldLevel)
    {
      $level = new Level;
      $level->id = $oldLevel->id;
      $level->name = $oldLevel->level_name;
      $level->save();
    }
  }

  public function clientMigration()
  {
    $oldClients = DB::table('frugal.clients')->get();
    foreach ($oldClients AS $oldClient)
    {
      $customer = new Customer;
      $customer->id = $oldClient->id;
      $customer->name = $oldClient->client_name;
      $customer->address = $oldClient->client_address;
      $customer->city = $oldClient->client_city;
      $customer->state = $oldClient->client_state;
      $customer->zip = $oldClient->client_zip;
      $customer->save();

      $contact = new Contact;
      $contact->name = $oldClient->client_name;
      $contact->email = $oldClient->client_email;
      $contact->mobile = (int) trim(preg_replace('/\D/', '', $oldClient->client_cell));
      $contact->home = preg_replace('/\D/', '', $oldClient->client_phone);
      $contact->alternate = preg_replace('/\D/', '', $oldClient->client_altnumber);
      $contact->primary = 1;
      $contact->customer_id = $customer->id;
      $contact->save();
    }

  }

  public function leadsMigration()
  {
    $oldLeads = DB::table('frugal.leads')->get();
    foreach ($oldLeads AS $oldLead)
    {
      $lead = new Lead;
      $lead->id = $oldLead->id;
      $lead->created_at = $oldLead->lead_created;
      $lead->source_id = $oldLead->lead_source;
      $lead->title = $oldLead->lead_title;
      $lead->user_id = ($oldLead->lead_designer) ? $oldLead->lead_designer : $oldLead->user_id;
      $lead->closed = ($oldLead->lead_closed) ? 1 : 0;
      $lead->status_id = $oldLead->status_id;
      $lead->customer_id = $oldLead->client_id;
      $lead->notes = ($oldLead->lead_notes) ? $oldLead->lead_notes : '';
      $lead->save();

      // Pull in schedules for showroom/closing/measure
      if ($oldLead->lead_showroom)
          Showroom::create(['lead_id' => $lead->id, 'scheduled' => date("Y-m-d H:i:s", $oldLead->lead_showroom),
            'location' => $oldLead->lead_location]);
      if ($oldLead->lead_closing)
          Closing::create(['lead_id' => $lead->id, 'scheduled' => date("Y-m-d H:i:s", $oldLead->lead_closing),
            'location' => $oldLead->lead_location]);
      if ($oldLead->lead_field)
          Measure::create(['lead_id' => $lead->id, 'scheduled' => date("Y-m-d H:i:s", $oldLead->lead_field),
            'location' => $oldLead->lead_location]);
    }

  }

  public function quoteQuestionaireMigration()
  {
    $quotes = Quote::all();
    foreach ($quotes AS $quote)
    {
      $meta = unserialize($quote->meta);
      if (isset($meta['meta']['quote_questionaire']))
      {
        foreach ($meta['meta']['quote_questionaire'] AS $q => $ans)
        {
          $answer = new QuoteAnswer;
          $answer->question_id = $q;
          $answer->answer = $ans;
          $answer->quote_id = $quote->id;
          $answer->save();
        }  // fe q
      } // if quotes
      if (isset($meta['meta']['quote_questionaire']))
        unset($meta['meta']['quote_questionaire']);
      $quote->meta = serialize($meta);
      $quote->save();

    } // fe quote
  } // for all quotes.

  public function conditionalSeeder()
  {
    $oldConditions = DB::table('frugal.conditions')->get();
    foreach ($oldConditions AS $oldCondition)
    {
      $condition = new Condition;
      $condition->id = $oldCondition->id;
      $condition->question_id = $oldCondition->question_id;
      $condition->answer = $oldCondition->condition_answer;
      $condition->operand = $oldCondition->condition_operand;
      $condition->amount = $oldCondition->condition_amount;
      $condition->once = ($oldCondition->condition_once) ? 1 : 0;
      $condition->save();
    }

  }  // conditionalseeder

  public function settingsMigration()
  {
    $oldSettings = DB::table('frugal.settings')->get();
    foreach ($oldSettings AS $oldSetting)
    {
      $setting = new Setting;
      $setting->name = $oldSetting->setting_name;
      $setting->val = $oldSetting->setting_val;
      $setting->save();
    }

  }

  public function filesMigration()
  {
    $oldFiles = DB::table('frugal.files')->get();
    foreach ($oldFiles AS $oldFile)
    {
      $file = new FrugalFile;
      $file->id = $oldFile->id;
      $file->location = $oldFile->file_loc;
      $file->description = $oldFile->file_desc;
      $file->user_id = $oldFile->user_id;
      $file->quote_id = ($oldFile->quote_id) ?: 0;
      $file->save();
      // Copy the file from the frugal dir
      @copy("/web/sites/vocalogic/frugalk.com/live/files/$oldFile->file_loc /web/sites/vocalogic/fk2/public/files/$oldFile->quote_id/$oldFile->file_loc");
    }
  }

  public function jobMigration()
  {
    $oldJobs = DB::table('frugal.jobs')->get();
    foreach ($oldJobs AS $oldJob)
    {
      $job = new Job;
      $job->id = $oldJob->id;
      $job->created_at = date("Y-m-d H:i:s", $oldJob->job_ts);
      $job->contract_date = date("Y-m-d", $oldJob->job_contract_date);
      $job->start_date = date("Y-m-d", $oldJob->job_start_date);
      $job->quote_id = $oldJob->quote_id;
      $job->closed = ($oldJob->job_closed) ? 1 : 0;
      $job->closed_on = ($oldJob->job_closed_ts) ? date("Y-m-d H:i:s", $oldJob->job_closed_ts) : 0;
      $job->paid = ($oldJob->isPaid) ? 1 : 0;
      $job->locked = ($oldJob->locked) ? 1 : 0;
      $job->notes = ($oldJob->job_notes) ?: '';
      $job->save();

      // Create Job Schedules
      $oldSchedules = DB::table('frugal.schedules')->whereJobId($job->id)->get();
      foreach ($oldSchedules AS $oldSchedule)
      {
        if ($oldSchedule->schedule_date)
        {
          $start = date("Y-m-d", $oldSchedule->schedule_date);
          $start .= " " . date("H:i:s", $oldSchedule->schedule_time_start);
          $end = date("Y-m-d", $oldSchedule->schedule_date);
          $end .= " " . date("H:i:s", $oldSchedule->schedule_time_end);
          $schedule = new JobSchedule;
          $schedule->start = $start;
          $schedule->end = $end;
          $schedule->designation_id = $oldSchedule->designation_id;
          $schedule->user_id = $oldSchedule->user_id;
          $schedule->job_id = $oldSchedule->job_id;
          $schedule->notes = ($oldSchedule->schedule_notes) ?: '';
          $schedule->save();
        }

      if ($oldSchedule->schedule_date2)
        {
          $start = date("Y-m-d", $oldSchedule->schedule_date2);
          $start .= " " . date("H:i:s", $oldSchedule->schedule_time_start2);
          $end = date("Y-m-d", $oldSchedule->schedule_date2);
          $end .= " " . date("H:i:s", $oldSchedule->schedule_time_end2);
          $schedule = new JobSchedule;
          $schedule->start = $start;
          $schedule->end = $end;
          $schedule->designation_id = $oldSchedule->designation_id;
          $schedule->user_id = $oldSchedule->user_id;
          $schedule->job_id = $oldSchedule->job_id;
          $schedule->notes = ($oldSchedule->schedule_notes2) ?: '';
          $schedule->save();
        }
    } // fe schedule

    // Create Job Items from old Cabinet/Hardware entries in job table
    $job = Job::find($job->id);
    if ($job->quote)
    {
      foreach ($job->quote->cabinets AS $cabinet)
      {
        $item = new JobItem;
        $item->job_id = $job->id;
        $item->instanceof = 'Cabinet';
        $item->reference = $cabinet->id;
        $item->save();
      }
    } // only if a quote object exists.
      $item = new JobItem;
      $item->job_id = $job->id;
      $item->instanceof = 'Hardware';
      $item->reference = 1;
       if ($oldJob->job_hardware_order_date)
        {
          $item->ordered = ($oldJob->job_hardware_order_date) ? date("Y-m-d", $oldJob->job_hardware_order_date) : 0;
          $item->confirmed = ($oldJob->job_hardware_ship_date) ? date("Y-m-d", $oldJob->job_hardware_ship_date) : 0;
          $item->received = ($oldJob->job_hardware_received_date) ? date("Y-m-d", $oldJob->job_hardware_received_date) : 0;
          $item->verified = ($oldJob->job_hardware_verified) ? $item->ordered : 0;
        }
     $item->save();


      $item = new JobItem;
      $item->job_id = $job->id;
      $item->instanceof = 'Accessory';
      $item->reference = 1;
      if ($oldJob->job_acc_order_date)
      {
        $item->ordered = ($oldJob->job_acc_order_date) ? date("Y-m-d", $oldJob->job_acc_order_date) : 0;
        $item->confirmed = ($oldJob->job_acc_ship_date) ? date("Y-m-d", $oldJob->job_acc_ship_date) : 0;
        $item->received = ($oldJob->job_acc_received_date) ? date("Y-m-d", $oldJob->job_acc_received_date) : 0;
        $item->verified = ($oldJob->job_acc_verified) ? $item->ordered : 0;
      }
      $item->save();


  } // fe job
}  // jobmigrator

  public function fftMigration()
  {
    $oldffts = DB::table('frugal.ffts')->get();
    foreach ($oldffts AS $oldfft)
    {
      $fft = new FFT;
      $fft->job_id = $oldfft->job_id;
      $fft->warranty = 0;
      $fft->created_at = date("Y-m-d H:i:s", $oldfft->fft_opents);
      $fft->user_id = ($oldfft->fft_assigned) ?: 0;
      $fft->notes = ($oldfft->fft_notes) ?: '';
      $fft->closed = ($oldfft->fft_closed) ? 1 : 0;
      if ($oldfft->fft_scheduled)
        $fft->schedule_start = date("Y-m-d H:i:s", $oldfft->fft_scheduled);
      $fft->schedule_end = date("Y-m-d H:i:s", $oldfft->fft_scheduled_end);
      if ($oldfft->fft_pre_schedule)
        $fft->pre_schedule_start = date("Y-m-d H:i:s", $oldfft->fft_pre_schedule);
      $fft->pre_schedule_end = date("Y-m-d H:i:s", $oldfft->fft_pre_schedule_end);
      $fft->pre_assigned = ($oldfft->fft_pre_assign) ?: 0;
      $fft->save();
    }
    // warranties as well.
    $oldffts = DB::table('frugal.warranties')->get();
    foreach ($oldffts AS $oldfft)
    {
      $fft = new FFT;
      $fft->job_id = $oldfft->job_id;
      $fft->warranty = 1;
      $fft->created_at = date("Y-m-d H:i:s", $oldfft->warranty_opents);
      $fft->user_id = ($oldfft->warranty_assigned) ?: 0;
      $fft->notes = ($oldfft->warranty_notes) ?: '';
      $fft->closed = ($oldfft->warranty_closed) ? 1 : 0;
      $fft->schedule_start = date("Y-m-d H:i:s", $oldfft->warranty_scheduled);
      $fft->schedule_end = date("Y-m-d H:i:s", $oldfft->warranty_scheduled_end);
      $fft->pre_schedule_start = date("Y-m-d H:i:s", $oldfft->warranty_predate);
      $fft->pre_schedule_end = date("Y-m-d H:i:s", $oldfft->warranty_predate_end);
      $fft->pre_assigned = ($oldfft->warranty_preassigned) ?: 0;
      $fft->save();
    }


  }

  public function punchMigration()
  {
    $oldPunches = DB::table('frugal.punches')->get();
    foreach ($oldPunches AS $oldPunch)
    {
      $item = new JobItem;
      $item->instanceof = ($oldPunch->punch_isfft) ? "FFT" : "Warranty";
      $item->job_id = $oldPunch->job_id;
      $item->reference = $oldPunch->punch_title ?: '';
      $item->save();
    }
  }

  public function mobileMigration()
  {
    $oldList = DB::table('frugal.punchlists')->get();
    foreach ($oldList AS $list)
    {
      $punch = new Punch;
      $punch->designation_id = $list->designation_id;
      $punch->question = $list->question;
      $punch->save();
    }
  }

}