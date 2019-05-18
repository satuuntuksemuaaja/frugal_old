<?php

namespace vl\core;

use Authorization;
use BS;
use Button;
use Carbon\Carbon;
use Customer;
use Editable;
use FFT;
use Input;
use Table;
use User;
use vl\jobs\JobBoard;

class FFTEngine
{
    static public $instanceType; // Have you never heard of instantiation ? Wtf was I thinking on these methods!
    static public function init($warranty = false, $service = false)
    {
        if ($warranty)
            self::$instanceType = "Warranty";
        if ($service)
            self::$instanceType = "Service";
        if (empty(self::$instanceType))
            self::$instanceType = "FFT";

        $fftcontractors = User::whereDesignationId(5)->get();
        $contractors = [];
        foreach ($fftcontractors AS $fftcontractor)
        {
            $contractors[] = ['value' => $fftcontractor->id, 'text' => $fftcontractor->name];
        }
        if (!Input::has('all'))
        {
            if ($service)
            {
                $ffts = FFT::whereService(true)->whereClosed(false)->get();
            }
            else
            {
                $ffts = FFT::whereWarranty($warranty)->whereClosed(false)->get();
            }
        }
        else
        {
            if ($service)
            {
                $ffts = FFT::whereService(true)->get();
            }
            else
            $ffts = FFT::whereWarranty($warranty)->get();
        }

        $headers = [
            'Customer',
            'Started',
            'Hours',
            'Visit Assigned',
            'Visit Scheduled',
            'Punch Assigned',
            'Punch Scheduled',
            'Punches',
            'Notes',
            'Schedules',
        ];

        if (!$warranty)
        {
            array_push($headers, 'Payment Recv');
        }
        $alls = User::orderBy('name', 'ASC')->get();
        $allusers = [];
        foreach ($alls AS $all)
        {
            $allusers[] = ['value' => $all->id, 'text' => $all->name];
        }

        $rows = [];
        foreach ($ffts AS $fft)
        {
            $auth = Authorization::whereJobId($fft->job_id)->first();
            if (!$auth)
            {
                $authIcon = "<i class='fa fa-comment text-warning'></i>";
            }
            else
            {
                if ($auth->signature)
                {
                    $authIcon = "<i class='fa fa-comment text-success'></i>";
                }
                else
                {
                    $authIcon = "<i class='fa fa-comment text-danger'></i>";
                }
            }
            $icons = null;
            if ($fft->job && $fft->job->quote)
            {
                $icons = "<span class='pull-right'><a class='tooltiped' data-toggle='tooltip' data-placement='right' data-original-title='View Quote'
                  href='/quote/{$fft->job->quote->id}/view'><i class='fa fa-search'></i></a> ";
            }
            $icons .= "&nbsp;&nbsp;";
            if ($fft->job && $fft->quote)
            {
                $icons .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                  data-original-title='Drawings' data-toggle='modal' data-target='#workModal'
                  href='/quote/{$fft->job->quote->id}/files'><i class='fa fa-image'></i></a>";
            }
            $color = ($fft->job && $fft->job->tasks->count() > 0) ? "text-danger" : null;
            $icons .= "&nbsp;&nbsp;";
            $icons .= "<a class='tooltiped get' data-toggle='tooltip' data-placement='right' data-original-title='Close FFT/Warranty'
                  href='/fft/{$fft->id}/close'><i class='fa fa-trash-o'></i></a> ";
            $icons .= "&nbsp;&nbsp;";
            if ($fft->job && $fft->job->quote)
            {
                $icons .= "<a class='tooltiped mjax {$color}' data-toggle='tooltip' data-placement='right'
                data-original-title='Add Task' data-toggle='modal' data-target='#workModal'
                href='/task/customer/0/job/{$fft->job->id}/quick'><i class='fa fa-openid'></i></a>";
            }
            $icons .= "&nbsp;&nbsp;";
            if ($fft->job && $fft->job->quote)
            {

                $icons .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                  data-original-title='Job Notes' data-toggle='modal' data-target='#workModal'
                  href='/job/{$fft->job->id}/notes'><i class='fa fa-check-square'></i></a>";
            }
            $icons .= "&nbsp;&nbsp;";
            $icons .= ($fft->signature) ? "<a class='tooltiped text-success' data-toggle='tooltip' data-placement='right'
                data-original-title='Signature Found!' href='/fft/$fft->id/signature'><i class='fa fa-edit'></i></a>" :
                "<a class='tooltiped text-danger' data-toggle='tooltip' data-placement='right'
                data-original-title='No Signature Found!' href='/fft/$fft->id/signature'><b><i class='fa fa-edit'></i></b></a></span>";
            $icons .= "&nbsp;&nbsp;";

            $icons .= "<a class='tooltiped' data-toggle='tooltip' data-placement='right'
                data-original-title='Create Shop Work' href='/fft/$fft->id/shop'><i class='fa fa-wrench'></i></a>";
            $icons .= "</span>";

            $scheduled = ($fft->schedule_start != '0000-00-00 00:00:00') ? Carbon::parse($fft->schedule_start)
                ->format('m/d/y h:i a') :
                "No Schedule Set";
            $prescheduled = ($fft->pre_schedule_start != '0000-00-00 00:00:00') ? Carbon::parse($fft->pre_schedule_start)
                ->format('m/d/y h:i a') :
                "No Schedule Set";
            $assigned = ($fft->assigned) ? $fft->assigned->name : "Unassigned";
            $preassigned = ($fft->preassigned) ? $fft->preassigned->name : "Unassigned";
            $scheduled = "<a class='mjax' data-target='#workModal' href='/fft/$fft->id/change/schedule'>$scheduled</a>";
            $prescheduled = "<a class='mjax' data-target='#workModal' href='/fft/$fft->id/change/preschedule'>$prescheduled</a>";
            $assigned = Editable::init()->id("idDe_$fft->id")->placement('bottom')->type('select')
                ->title("Select Contractor")->linkText($assigned)
                ->source($contractors)->url("/fft/$fft->id/assigned/liveupdate")->render();
            $preassigned = Editable::init()->id("idDe_$fft->id")->placement('bottom')->type('select')
                ->title("Select Contractor")->linkText($preassigned)
                ->source($allusers)->url("/fft/$fft->id/preassigned/liveupdate")->render();
            $n = $fft->warranty ? 'warranty_notes' : 'notes';
            if (!$fft->$n) $fft->$n = "None";
           $notes = "<a class='btn btn-xs btn-primary to`oltiped mjax' data-toggle='tooltip' data-placement='right'
                  data-original-title='Payment Notes' data-toggle='modal' data-target='#workModal'
                  href='/fft/{$fft->id}/notes'>Payment Notes (".$fft->thread_notes()->count().")</a>";
            $hours = Editable::init()->id("idDe_$fft->id")->placement('bottom')->type('text')
                ->title("Hours to Complete")->linkText(($fft->hours) ?: "0")
                ->url("/fft/$fft->id/hours")->render();
            $color = self::getRowColor($fft);
            if ($fft->signoff)
            {
                $signoff = "<br/><span class='label label-success'>Customer Signed Off</span>";
            }
            else
            {
                $signoff = null;
            }

            if (!$warranty)
            {

                try
                {

                    $rows[] = [
                        ($fft->job && $fft->job->quote) ? "$color<a href='/profile/{$fft->job->quote->lead->customer->id}/view'>{$fft->job->quote->lead->customer->name}</a> ({$fft->job->quote->lead->customer->id})" . $icons . $signoff :
                            "<a href='/profile/{$fft->customer->id}/view'>{$fft->customer->name} ({$fft->customer->id})</a>" . $icons . $signoff,
                        ($fft->job) ? Carbon::parse($fft->job->start_date)->format('m/d/y') : null,
                        $hours,
                        $preassigned,
                        $prescheduled,
                        $assigned,
                        $scheduled,
                        self::getPunchesIndex($fft,
                            $warranty) . "<span class='pull-right'><a class='tooltiped' data-placement='left' data-original-title='Customer Authorizations' href='/job/$fft->job_id/auth'>$authIcon</a></span>",
                        $notes,
                        ($fft->job) ? JobBoard::getScheduleBlock($fft->job) : null,
                        (!$warranty) ? ($fft->payment) ? "Received" :
                            Button::init()->text("Payment")->color('primary btn-sm get')->icon('money')
                                ->url("/fft/$fft->id/payment")->render() : null
                    ];
                } catch (\Exception $e)
                {

                };

            }

            else
            {
                try
                {
                    $rows[] = [
                        ($fft->job && $fft->job->quote) ? "$color<a href='/profile/{$fft->job->quote->lead->customer->id}/view'>{$fft->job->quote->lead->customer->name}</a>" . $icons :
                            "<a href='/profile/{$fft->customer->id}/view'>{$fft->customer->name} - ({$fft->customer->id})</a>" . $icons . $signoff,
                        ($fft->job) ? Carbon::parse($fft->job->start_date)->format('m/d/y') : null,
                        $hours,
                        $preassigned,
                        $prescheduled,
                        $assigned,
                        $scheduled,

                        self::getPunchesIndex($fft, $warranty),
                        $notes,
                        ($fft->job) ? JobBoard::getScheduleBlock($fft->job) : null

                    ];
                } catch (\Exception $e)
                {

                };

            }

        }
        $table = Table::init()->headers($headers)->rows($rows)->datatables()->responsive()->render();
        if ($warranty)
        {
            $table .= Button::init()->text("Create New Warranty")->color('info')->modal('newWarranty')->icon('plus')
                ->render();
            $table .= self::warrantyModal();
        }
        if ($service)
        {
            $table .= Button::init()->text("Create New Service Work")->color('info')->modal('newWarranty')->icon('plus')
                ->render();
            $table .= self::warrantyModal(true);
        }
        $all = Button::init()->text("Show All")->url("?all=yes")->color('primary')->icon('refresh')->render();
        $span = BS::span(12, $all . $table);
        return BS::row($span);

    }


    static public function warrantyModal($service = false)
    {
        $pre = null;
        $jobs = \Job::whereClosed(false)->get();
        $opts[] = ['val' => 0, 'text' => '-- Select Job --'];
        foreach ($jobs AS $job)
        {
            if ($job->quote && $job->quote->lead)
            {
                $opts[] = [
                    'val'  => $job->id,
                    'text' => $job->quote->lead->customer->name . " ({$job->quote->type} - $job->id)"
                ];
            }
        }
        $fields[] = [
            'type'  => 'select2',
            'var'   => 'job_id',
            'opts'  => $opts,
            'span'  => 6,
            'text'  => 'Job:',
            'width' => 400
        ];
        $opts = [];
        $opts[] = ['val' => 0, 'text' => '-- Select Customer --'];
        foreach (Customer::all() AS $customer)
        {
            $opts[] = ['val' => $customer->id, 'text' => $customer->name . " ({$customer->city}, {$customer->state})"];
        }

        $fields[] = [
            'type'  => 'select2',
            'var'   => 'customer_id',
            'opts'  => $opts,
            'span'  => 6,
            'text'  => 'or Select Customer:',
            'width' => 400
        ];
        $url = $service ? "/service/new" : "/warranty/new";
        $text = $service ? "Create Service Work" : "Create Warranty";
        $form = \Forms::init()->id('newWarrantyForm')->labelSpan(4)->url($url)->elements($fields)->render();
        $create = Button::init()->text($text)->icon('plus')->color('primary mpost')
            ->formid('newWarrantyForm')->render();
        return \Modal::init()->id('newWarranty')->header($text)->content($pre . $form)->footer($create)
            ->render();
    }

    static public function getPunchesIndex(FFT $fft, $warranty)
    {
        if (!$fft->job)
        {
            // This may be a warranty item so we need to look at the customer id and find out what job this was.
            foreach ($fft->customer->quotes AS $quote)
            {
                if ($quote->job)
                {
                    $fft->job_id = $quote->job->id;
                    $fft->save();
                }
            }
        }
        if (!$fft->job) return "<b>Cannot find job to link, Old frugalk?</b>";
        $type = self::$instanceType;
        $total = 0;
        foreach ($fft->job->items()->whereInstanceof($type)->get() AS $item)
        {
            if ($item->verified == '0000-00-00')
            {
                $total++;
            }
        }
        if ($total == 0)
        {
            $button = Button::init()->text(null)->icon('check')->color('success btn-xs')->url("/punches/{$fft->id}")
                ->render();
        }
        else
        {
            $button = Button::init()->text($total)->icon(null)->color('danger btn-xs')->url("/punches/{$fft->id}")
                ->render();
        }
        return $button;
    }

    /**
     * Determine row color.
     *
     * @issue https://github.com/vocalogic/fk2/issues/392
     * @param FFT $fft
     * @return null|string
     */
    static public function getRowColor(FFT $fft)
    {
        $color = null;
        $allRecv = false;
        if (Carbon::parse($fft->pre_schedule_start)->timestamp <= 0) // No schedule yet.
        {
            return 'color-walkthrough ';
        }
        else $color = 'color-walkthroughschedule '; // It's scheduled.

        if (Carbon::parse($fft->schedule_start)->timestamp > 0) // If punch scheduled.
        {
            $color = 'color-pscheduled ';
        }


        // If all items have been ordered. go blue.
        $allOrdered = true;
        if (!$fft->job) return;
        if ($fft->job->items()->where('instanceof', 'FFT')->count() > 0)
        {
            foreach ($fft->job->items()->where('instanceof', 'FFT')->get() as $item)
            {
                if ($item->ordered == '0000-00-00' && $item->orderable)
                {
                    $allOrdered = false;
                }
            }
            $allRecv = false;
            if ($allOrdered) // Everything was ordered, check to see if they have been received
            {
                $color = 'color-ordered '; // By default it's just ordered.
                $allRecv = true;
                foreach ($fft->job->items()->where('instanceof', 'FFT')->get() as $item)
                {
                    if ($item->received == '0000-00-00' && $item->orderable)
                    {
                        $allRecv = false;
                    }
                }
            }
            else $color = 'color-notordered ';
            if ($allRecv)
            {
                $color = 'color-received ';
            }
        } // count > 0
        if (Carbon::now()->timestamp > Carbon::parse($fft->schedule_start)->timestamp && Carbon::parse($fft->schedule_start)->timestamp > 0 && !$fft->signoff)
        {
            $color = 'color-notsigned ';
        }
        if (Carbon::parse($fft->schedule_start)->timestamp > 0 && $allRecv) // If punch scheduled.
        {
            $color = 'color-pscheduled ';
        }


        return $color;
    }


}