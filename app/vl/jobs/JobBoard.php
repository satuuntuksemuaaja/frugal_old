<?php
namespace vl\jobs;

use Auth;
use Authorization;
use BS;
use Button;
use Carbon\Carbon;
use Editable;
use Job;
use JobItem;
use Po;
use Table;
use User;

class JobBoard
{
    static public function init($jobs)
    {
        $headers = ["Client", 'Designer', 'Closed', 'Cabinets', 'Hardware', 'Accessories', 'Items', 'Starts', 'Schedule'];
        $rows = [];
        $designers = [];
        $dlist = User::whereLevelId(4)->get();
        foreach ($dlist AS $list)
            $designers[] = ['value' => $list->id, 'text' => $list->name];

        foreach ($jobs AS $job)
        {
            if (!$job->quote) continue;
            $icons = self::getIcons($job);
            self::verifyCabinets($job);
            $auth = Authorization::whereJobId($job->id)->first();
            if (!$auth)
            {
                $authIcon = "<i class='fa fa-comment text-warning'></i>";
            }
            else
            {
                if ($auth->signature)
                    $authIcon = "<i class='fa fa-comment text-success'></i>";
                else
                    $authIcon = "<i class='fa fa-comment text-danger'></i>";
            }
            $designer = Editable::init()->id("idDe_{$job->quote->id}")->placement('bottom')->pk(1)->type('select')
                                ->title("Select Designer")->linkText(($job->quote->lead->user) ? $job->quote->lead->user->name : "No Designer Assigned")
                                ->source($designers)->url("/lead/{$job->quote->lead->id}/designer/update")->render();
            $starts = ($job->start_date != '0000-00-00' && $job->start_date != '1969-12-31') ?
                Carbon::parse($job->start_date)->format('m/d/y') : "No Start Date";
            $starts = "<a class='mjax' data-target='#workModal' href='/job/$job->id/starts'>$starts</a>";
            $color = self::getJobStatus($job);
            $checklist = "<span class='pull-right'><a href='/job/$job->id/checklist'><i class='fa fa-download'></i></a> 
            <a class='tooltiped' data-placement='left' data-original-title='Customer Authorizations' href='/job/$job->id/auth'>$authIcon</a></span>";
            $rows[] = [$color . "<a href='/profile/{$job->quote->lead->customer->id}/view'>" . $job->quote->lead->customer->name . "</a>" . $icons .
                       "<br/><small>{$job->quote->type} - {$job->quote->lead->customer->id}</small>",
                $designer,
                Carbon::parse($job->closed_on)->format("m/d/y"),
                self::getStatusIcon($job, 'cabinet'),
                self::getStatusIcon($job, 'hardware'),
                self::getStatusIcon($job, 'accessory'),
                self::getJobItemStatus($job) .  $checklist,
                $starts,
                self::getScheduleBlock($job)
            ];
        } // end of jobs
        $table = Table::init()->headers($headers)->rows($rows)->dataTables()->responsive()->render();
        $table .= Button::init()->text("Export Jobs")->color('primary')->icon('arrow-right')->url("/jobs/export")->render();
        $table .= "<b>Color Codes:</b> RED - Not Ordered, ORANGE - Shipped (Confirmed), BLUE - Needs to be confirmed, GREEN
                - RECEIVED";
        return BS::span(12, $table);
    } // init

    /**
     * Returns red if the job is incomplete and the date is after the end date
     *
     * @param  Job $job [description]
     * @return null|string [type]      [description]
     */
    static public function getJobStatus(Job $job)
    {
        foreach ($job->schedules AS $schedule)
        {
            if (Carbon::now() > $schedule->end && !$schedule->complete)
            {
                return 'color-danger ';
            }
        }
        return null;
    }

    static public function checkSchedulesForClosing(Job $job)
    {
        $job = Job::find($job->id); // re-pull schedules.
        $close = true;
        foreach ($job->schedules AS $schedule)
            if (!$schedule->complete)
            {
                $close = false;
            }
        if ($close)
        {
            $job->closed = 1;
            $job->closed_on = Carbon::now();
            $job->save();
        }

    }


    /**
     * Make sure that references for cabinets match what we have the in quote.
     *
     * @param  Job $job [description]
     */
    static public function verifyCabinets(Job $job)
    {

        $ids = [];
        foreach ($job->quote->cabinets AS $cabinet)
            $ids[] = $cabinet->id; // This is a quote_cabinet id reference. So keep it.
        $ids = implode(',', $ids);
        if ($ids)
        {
            \DB::statement("DELETE from job_items WHERE (job_id='$job->id' AND instanceof='Cabinet') AND reference NOT IN($ids)");
        }
    }

    static public function getScheduleBlock(Job $job)
    {
        // Make a popover with the schedules with a modal
        if ($job->locked) return "Locked";
        if (($job->quote->picking_slab == 'Yes' || $job->quote->picking_slab == 'Undecided') && !$job->quote->picked_slab)
            return "Locked - No Slab Picked <br/><a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
                data-original-title='Customer has picked slab' href='/job/$job->id/picked'><i class='fa fa-eject'>
                </i></a>";
        if (!$job->reviewed) return "Not Reviewed";
        $count = $job->schedules->count();
        if ($count == 0)
        {
            return Button::init()->text("No Schedule")->color("warning btn-xs ")->url("/job/$job->id/schedules")->icon('exclamation')->render();
        }
        $data = null;
        $headers = ['Designation', 'Contractor', 'When'];
        $rows = [];
        $ok = true;
        $count = 0;
        foreach ($job->schedules AS $schedule)
        {
            $color = null;
            if (Carbon::parse($schedule->start) < Carbon::now())
            {
                $color = 'color-danger ';
                $ok = false;
            }
            if (!$schedule->sent && $schedule->start)
            {
                $color = 'color-info ';
            }
            if ($schedule->complete)
            {
                $color = 'color-success ';
            }
            $notes = ($schedule->notes) ? "<br/><span style='color:#ff0000;'>" . str_replace('"', null, $schedule->notes) . "</span>" : null;
            if ($schedule->notes) $count++;
            if ($schedule->designation)
            {
                $rows[] = [$color . $schedule->designation->name, $schedule->user->name, Carbon::parse($schedule->start)->format('m/d/y h:i a')
                    . " - " . Carbon::parse($schedule->end)->format('h:i a') . $notes];
            }
        }

        $table = Table::init()->headers($headers)->rows($rows)->clearStyles()->render();
        $buttonColor = ($ok && !isset($color)) ? 'success' : 'danger';
        $count = ($count > 0) ? " ($count)" : null;
        $extras = "<br/>";
        $extras .= (!$job->schedules_sent) ? "
      <a class='tooltiped' data-toggle='tooltip' data-placement='left'P
                data-original-title='Customer has not been sent schedule'
                href='#'><i class='fa fa-user'></i></a>" : null;
        $extras .= (!$job->schedules_confirmed) ? "
    <a class='tooltiped' data-toggle='tooltip' data-placement='left'
                data-original-title='Customer has not confirmed schedule'
                href='#'><i class='fa fa-exclamation'></i></a>" : null;

        return Button::init()->text("Schedules{$count}")->color("$buttonColor btn-xs")->popover("Job Schedules", $table, 'left')
                     ->url("/job/$job->id/schedules")->icon('clock-o')->render() . $extras;
    }


    static public function getIcons(Job $job)
    {
        $data = "<span class='pull-right'>";
        // View Quote, Drawings, Notes, Delete, Alerts (if any)
        $data .= "<a class='tooltiped' data-toggle='tooltip' data-placement='right' data-original-title='View Quote'
                href='/quote/{$job->quote->id}/view'><i class='fa fa-search'></i></a> ";
        $data .= "&nbsp;&nbsp;";
        $data .= "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#workModal'
                href='/quote/{$job->quote->id}/files'><i class='fa fa-image'></i></a>";
        $data .= "&nbsp;&nbsp;";
        $color = $job->notes->count() > 0 ? "text-danger" : null;
        $data .= "<a class='tooltiped mjax {$color}' data-toggle='tooltip' data-placement='right'
                data-original-title='Job Notes' data-toggle='modal' data-target='#workModal'
                href='/job/{$job->id}/notes'><i class='fa fa-edit'></i></a>";
        $data .= "&nbsp;&nbsp;";
        if (Auth::user()->superuser)
        {

            $data .= "<a data-toggle='confirmation' data-btnOkLabel='Delete Job' data-title='Are you sure you want to delete this job?' 
                         data-href='/job/$job->id/delete' data-placement='right'><i class='fa fa-trash-o'></i></a>";
            $data .= "&nbsp;&nbsp;";
            if ($job->locked)
            {
                $data .= "<a class='tooltiped' data-toggle='tooltip' data-placement='right' data-original-title='Unlock Schedule'
                href='/job/$job->id/unlock'><i class='fa fa-unlock'></i></a>";
                $data .= "&nbsp;&nbsp;";
            } // if locked
        }
        $color = ($job->tasks()->count() > 0) ? "text-danger" : null;
        $data .= "<a class='tooltiped mjax {$color}' data-toggle='tooltip' data-placement='right'
                data-original-title='Add Task' data-toggle='modal' data-target='#workModal'
                href='/task/customer/0/job/{$job->id}/quick'><i class='fa fa-openid'></i></a>";
        $data .= "&nbsp;&nbsp;";

        $data .= ($job->has_money) ? "<a class='tooltiped mjax text-success data-toggle='tooltip' data-placement='right'
                data-original-title='Money Received!' href='#'><i class='fa fa-money'></i></a>" :
            "<a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
                data-original-title='Money Not Received!' href='/job/$job->id/money'><i class='fa fa-exclamation-circle'>
                </i></a>";
        $data .= "&nbsp;&nbsp;";
        $data .= "<a class='tooltiped mjax text-warning' data-toggle='tooltip' data-placement='right'
                data-original-title='Override XML' data-toggle='modal' data-target='#workModal'
                href='/job/{$job->id}/xml'><i class='fa fa-recycle'></i></a>";
        $data .= "&nbsp;&nbsp;";
        $data .= "<a class='tooltiped text-info' data-toggle='tooltip' data-placement='right'
                data-original-title='Job Summary'
                href='/quote/{$job->quote->id}/summary'><i class='fa fa-arrow-down'></i></a>";
        $data .= "&nbsp;&nbsp;";

        $color = ($job->construction) ? "text-success" : "text-danger";
        $icon = ($job->construction) ? "check" : "exclamation";
        $data .= "<a class='tooltiped get {$color}' data-toggle='tooltip' data-placement='right'
                data-original-title='Construction Verification'
                href='/job/{$job->id}/construction'><i class='fa fa-{$icon}'></i></a>";
        $data .= "&nbsp;&nbsp;";
        $data .= ($job->reviewed) ? "<a class='tooltiped mjax text-success' data-toggle='tooltip' data-placement='right'
                data-original-title='Job Reviewed!' href='#'><i class='fa fa-eye'></i></a>" :
            "<a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
                data-original-title='Job not Reviewed!' href='/job/$job->id/review'><i class='fa fa-spin fa-eye'>
                </i></a>";
        $data .= "&nbsp;&nbsp;";
        $data .= ($job->sent_cabinet_arrival) ? "<a class='tooltiped get text-success' data-toggle='tooltip' data-placement='right'
                data-original-title='Cabinet Arrival Email Sent' href='/job/$job->id/arrival'><i class='fa fa-calendar'></i></a>" :
            "<a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
                data-original-title='Cabinet Arrival Email Unsent' href='/job/$job->id/arrival'><i class='fa fa-calendar'>
                </i></a>";
        if ($job->quote)
        {
            $meta = unserialize($job->quote->meta);
            if (!empty($meta['meta']['quote_appliances']))
            {
                $data .= "&nbsp;&nbsp;";
                $data .= "<a class='tooltiped mjax " . $job->quote->getApplianceClass() . "' data-toggle='tooltip' data-placement='right'
                data-original-title='Appliance Settings' data-toggle='modal' data-target='#workModal'
                href='/quote/{$job->quote->id}/appsettings'><i class='fa fa-wrench'></i></a> &nbsp; &nbsp;";
            }
        }
        $data .= '</span>';
        return $data;
    }

    /**
     * This is an old routine from the old version. Send in unix timestamps
     *
     * @param  [type] $date_start [description]
     * @param  [type] $day        [description]
     * @return [type]             [description]
     */
    static public function getDateForDay($date_start, $day)
    {
        $day = $day - 1;
        $start = Carbon::createFromTimestamp($date_start);
        $target = $start->addWeekdays($day);
        return $target->timestamp;
    }

    /**
     * Get the status of job items
     *
     * @param  Job $job [description]
     * @return string [type]      [description]
     */
    static public function getJobItemStatus(Job $job)
    {
        // We only care about $item->verified here.
        self::checkJobItems($job);
        $items = JobItem::whereJobId($job->id)->whereInstanceof('Item')->get();
        $confirmed = true; // Assume we're good
        $found = 0;
        foreach ($items AS $item)
            if ($item->verified == '0000-00-00')
            {
                $found++;
                $confirmed = false;
            }
        if ($confirmed)
        {
            return "<a class='mjax label label-success' data-target='#workModal' href='/job/$job->id/items'>
              <i class='fa fa-check text-success'></i></a>";
        }
        else
        {
            return "<a class='mjax label label-danger' data-target='#workModal' href='/job/$job->id/items'>({$found})</a>";
        }
    }

    static public function getPOStatus(Job $job, $statusType)
    {
        $data = null;

        switch ($statusType)
        {
            case 'cabinet' :
                $pos = Po::whereJobId($job->id)->whereType('Cabinets')->get();
                foreach ($pos AS $po)
                {
                    if ($po->status == 'draft')
                    {
                        $color = 'danger';
                    }
                    elseif ($po->status == 'ordered') $color = 'info';
                    elseif ($po->status == 'complete') $color = 'success';
                    else $color = 'warning';
                    $data .= "<a class='label label-$color' href='/po/{$po->id}'>#{$po->number}</a><br/>";
                    if ($po->projected_ship) $data .= "<small>$po->projected_ship</small><br/>";
                }
                break;
            case 'hardware' :
                $po = Po::whereJobId($job->id)->whereType('Hardware')->first();
                if (!$po) continue;
                if ($po->status == 'draft')
                {
                    $color = 'danger';
                }
                elseif ($po->status == 'ordered') $color = 'info';
                elseif ($po->status == 'complete') $color = 'success';
                else $color = 'warning';
                $data .= "<a class='label label-$color' href='/po/{$po->id}'>#{$po->number}</a><br/>";
                if ($po->projected_ship) $data .= "<small>$po->projected_ship</small><br/>";

                break;
            case 'accessory' :
                $po = Po::whereJobId($job->id)->whereType('Accessories')->first();
                if (!$po) continue;
                if ($po->status == 'draft')
                {
                    $color = 'danger';
                }
                elseif ($po->status == 'ordered') $color = 'info';
                elseif ($po->status == 'complete') $color = 'success';
                else $color = 'warning';
                $data .= "<a class='label label-$color' href='/po/{$po->id}'>#{$po->number}</a><br/>";
                if ($po->projected_ship) $data .= "<small>$po->projected_ship</small><br/>";

                break;

        }
        return $data;


    }

    /**
     * Obtain the icons for each of the statuses based on the item
     *
     * @param  Job $job [description]
     * @param  [type] $type [description]
     * @return null|string [type]       [description]
     */
    static public function getStatusIcon(Job $job, $statusType)
    {
        if ($job->pos->count() > 0)
        {
            return self::getPOStatus($job, $statusType);
        }
        $data = null;
        switch ($statusType)
        {
            case 'cabinet' :
                $item = JobItem::whereJobId($job->id)->whereInstanceof('Cabinet')->where(function ($q)
                {
                    $q->where('ordered', '0000-00-00');
                    $q->orWhere('confirmed', '0000-00-00');
                    $q->orWhere('received', '0000-00-00');
                    $q->orWhere('verified', '0000-00-00');
                })->first();
                if (!$item)
                {
                    $item = JobItem::whereJobId($job->id)->whereInstanceof('Cabinet')->first();
                }
                // Create Icon
                if (!$item)
                {
                    foreach ($job->quote->cabinets AS $cabinet)
                    {
                        $item = new JobItem;
                        $item->instanceof = 'Cabinet';
                        $item->reference = $cabinet->id;
                        $item->job_id = $job->id;
                        $item->save();
                    }
                }
                $type = 'fa-bars';
                $word = "Cabinets";
                break;

            case 'hardware' :
                $item = JobItem::whereJobId($job->id)->whereInstanceof('Hardware')->whereReference(1)->first();
                // Create Icon
                if (!$item) return "<a class='get' href='/job/$job->id/create/hardware'><b>No Hardware Required</b></a>";
                $type = 'fa-bars';
                $word = "Hardware";
                break;

            case 'accessory' :
                $item = JobItem::whereJobId($job->id)->whereInstanceof('Accessory')->whereReference(1)->first();
                // Create Icon
                if (!$item) return "<a class='get' href='/job/$job->id/create/accessory'><b>No Accessories Required</b></a>";
                $type = 'fa-bars';
                $word = "Accessories";
                break;

        }
        if (!isset($item)) return null;
        if ($item->ordered != '0000-00-00')
        {
            $title = "{$word} ordered on " . Carbon::parse($item->ordered)->format('m/d/y');
            $color = 'success';
            $type = 'fa-cloud-upload';

        }
        else
        {
            $title = "{$word} not ordered.";
            $color = 'danger';
            $type = 'fa-cloud-upload';
        }
        $data .= "<a class='mjax label label-$color tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='$title' href='/job/{$job->id}/track/$statusType/reference/$item->reference'>
                  <i class='text-{$color} fa {$type}'></i></a>";

        if ($item->confirmed != '0000-00-00')
        {
            $title = "{$word} confirmed on " . Carbon::parse($item->confirmed)->format('m/d/y');
            $color = 'success';
            $type = 'fa-check-square-o';
        }
        else
        {
            $title = "{$word} not confirmed";
            $color = 'danger';
            $type = 'fa-check-square-o';
        }
        $data .= "<a class='label label-$color mjax tooltiped' data-toggle='tooltip' data-target='#workModal'
                data-original-title='$title' href='/job/{$job->id}/track/$statusType/reference/$item->reference'>
                <i class='text-{$color} fa {$type}'></i></a>";

        if ($item->received != '0000-00-00')
        {
            $title = "{$word} received on " . Carbon::parse($item->received)->format('m/d/y');
            $color = 'success';
            $type = 'fa-arrow-down';
        }
        else
        {
            $title = "{$word} not received";
            $color = 'danger';
            $type = 'fa-arrow-down';
        }
        $data .= "<a class='label label-$color mjax tooltiped' data-toggle='tooltip' data-target='#workModal'
                data-original-title='$title' href='/job/{$job->id}/track/$statusType/reference/$item->reference'>
              <i class='text-{$color} fa {$type}'></i></a>";


        if ($item->verified != '0000-00-00')
        {
            $title = "{$word} verified on " . Carbon::parse($item->verified)->format('m/d/y');
            $color = 'success';
            $type = 'fa-check';
        }
        else
        {
            $title = "{$word} not verified";
            $color = 'danger';
            $type = 'fa-check';

        }
        $data .= "<a class='label label-$color mjax tooltiped' data-toggle='tooltip' data-target='#workModal'
                data-original-title='$title' href='/job/{$job->id}/track/$statusType/reference/$item->reference'>
                <i class='text-{$color} fa {$type}'></i></a>";

        return $data;
    }

    static public function checkJobItems(Job $job)
    {
        $meta = unserialize($job->quote->meta)['meta'];
        $electricals = (isset($meta['quote_electrical_extras'])) ? explode("\n", $meta['quote_electrical_extras']) : [];
        $plumbings = (isset($meta['quote_plumbing_extras'])) ? explode("\n", $meta['quote_plumbing_extras']) : [];
        $additionals = (isset($meta['quote_misc'])) ? explode("\n", $meta['quote_misc']) : [];
        $installers = (isset($meta['quote_installer_extras'])) ? explode("\n", $meta['quote_installer_extras']) : [];

        if (isset($meta['quote_led_feet']))
        {
            array_push($electricals, "Install $meta[quote_led_feet] ft. of LED Lighting");
        }
        // #90 - Add Tile to Verification Items.
        if (isset($meta['quote_tile_feet']) && $meta['quote_tile_feet'] > 0)
        {
            array_push($electricals, "Install " . $meta['quote_tile_feet'] . " linear feet of " . $meta['quote_tile_pattern'] . " tile.");
        }
        // ------------------ Add items if not found ---------------------- //
        foreach ($electricals AS $electrical)
        {
            if ($electrical)
            {
                $item = JobItem::whereInstanceof('Item')->whereJobId($job->id)->whereReference($electrical)->first();

                if (!$item)
                {
                    $item = new JobItem;
                    $item->job_id = $job->id;
                    $item->instanceof = 'Item';
                    $item->reference = $electrical;
                    $item->save();

                } // !item
            } // if valid
        } // foreach

        foreach ($plumbings AS $electrical) // just rename the source.
        {
            if ($electrical)
            {
                $item = JobItem::whereInstanceof('Item')->whereJobId($job->id)->whereReference($electrical)->first();
                if (!$item)
                {
                    $item = new JobItem;
                    $item->job_id = $job->id;
                    $item->instanceof = 'Item';
                    $item->reference = $electrical;
                    $item->save();
                } // !item
            } // if valid
        } // foreach

        foreach ($additionals AS $electrical) // just rename the source.
        {
            if ($electrical)
            {
                $item = JobItem::whereInstanceof('Item')->whereJobId($job->id)->whereReference($electrical)->first();
                if (!$item)
                {
                    $item = new JobItem;
                    $item->job_id = $job->id;
                    $item->instanceof = 'Item';
                    $item->reference = $electrical;
                    $item->save();
                } // !item
            } // if valid
        } // foreach

        foreach ($installers AS $electrical) // just rename the source.
        {
            if ($electrical)
            {
                $item = JobItem::whereInstanceof('Item')->whereJobId($job->id)->whereReference($electrical)->first();
                if (!$item)
                {
                    $item = new JobItem;
                    $item->job_id = $job->id;
                    $item->instanceof = 'Item';
                    $item->reference = $electrical;
                    $item->save();
                } // !item
            } // if valid
        } // foreach
    } // fn

}