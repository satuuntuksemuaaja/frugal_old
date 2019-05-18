<?php
namespace vl\leads;

use Carbon\Carbon;
use Followup;
use Illuminate\Support\Facades\Auth;
use Lead;
use LeadNote;
use LeadUpdate;
use Notification;
use Status;
use vl\core\Google;
use vl\core\NotificationEngine;

class StatusManager
{

    const CUSTOMER = 7;
    const EMPLOYEE = 8;

    static public function setFollowup(Followup $followup, $status, $removeOld = true)
    {
        $followup->status_id = $status;
        $followup->save();
        if ($removeOld)
        {
            NotificationEngine::removeOldNotifications($followup->lead);
        }
        NotificationEngine::createNotification($followup->lead, null, $status, true);

    }

    /**
     * What needs to be done when a status is changed?
     *
     * @param Lead $lead [description]
     * @param [type] $status [description]
     */
    static public function setLead(Lead $lead, $status)
    {
        if (($lead->status && $lead->status->name == 'Sold') && (Auth::user()->id != 5)) return;
        $note = new LeadNote;
        $newStatus = Status::getStatusById($status);
        $u = new LeadUpdate;
        $u->lead_id = $lead->id;
        $u->old_status = $lead->status_id;
        $u->status = $status;
        $u->user_id = $lead->user_id;
        $u->save();
        if ($lead->status)
        {
            $note->note = "Status changed from <b>{$lead->status->name}</b> to <b>{$newStatus}</b>";
        }
        else
        {
            $note->note = "Status changed to <b>{$newStatus}</b>";
        }
        $note->lead_id = $lead->id;
        $note->user_id = Auth::user()->id;
        $note->save();
        $lead->status_id = $status;
        $lead->last_note = Carbon::now();
        $lead->last_status_by = Auth::user()->id;
        $lead->save();

        NotificationEngine::removeOldNotifications($lead);
        NotificationEngine::createNotification($lead);
        // Check to see if there is an onset for this status.
        // Google Integration - Check to see if this new lead status is Showroom, Closing, or Digital Measure
        switch ($status)
        {
            case 4 : //Showroom
                $contact = $lead->customer->contacts()->first();
                $params = [];
                $params['title'] = "Showroom Visit with {$lead->customer->name} in {$lead->showroom->location}";
                $params['location'] = "{$lead->showroom->location} Location";
                $params['description'] = "Client Information:
                {$lead->customer->name}
                {$lead->customer->address}
                {$lead->customer->city}, {$lead->customer->state}, {$lead->customer->zip}

                Phone: {$contact->mobile} or {$contact->home} (Home)
                E-mail: {$contact->email}";
                $params['start'] = Carbon::parse($lead->showroom->scheduled);
                $params['end'] = Carbon::parse($lead->showroom->scheduled)->addMinutes(120);
                Google::event($lead->user, $params);
                self::generateFollowups($lead, true);
                break;
            case 50 : // Walkin
                self::generateFollowups($lead, true);
                break;
            case 29 : // Cancelled
                Notification::whereFollowupId($lead->id)->delete();
                break;
            case 49 : // Did not show up
                Notification::whereFollowupId($lead->id)->delete();
                break;
            case 36 : // Digital Measure
                $contact = $lead->customer->contacts()->first();
                $params = [];
                $params['title'] = "Digital Measure for {$lead->customer->name}";
                $params['location'] = "Customer Location";
                $params['description'] = "Client Information:
                {$lead->customer->name}
                {$lead->customer->address}
                {$lead->customer->city}, {$lead->customer->state}, {$lead->customer->zip}

                Phone: {$contact->mobile} or {$contact->home} (Home)
                E-mail: {$contact->email}";
                $params['start'] = Carbon::parse($lead->measure->scheduled);
                $params['end'] = Carbon::parse($lead->measure->scheduled)->addMinutes(120);
                Google::event($lead->measure->user, $params);
                break;
            case 35: // Closing Date
                $contact = $lead->customer->contacts()->first();
                $params = [];
                $params['title'] = "Closing for {$lead->customer->name}";
                $params['location'] = "Customer Location";
                $params['description'] = "Client Information:
                {$lead->customer->name}
                {$lead->customer->address}
                {$lead->customer->city}, {$lead->customer->state}, {$lead->customer->zip}

                Phone: {$contact->mobile} or {$contact->home} (Home)
                E-mail: {$contact->email}";
                $params['start'] = Carbon::parse($lead->closing->scheduled);
                $params['end'] = Carbon::parse($lead->closing->scheduled)->addMinutes(120);
                Google::event($lead->user, $params);
                break;
            case 10: // Quote Provided
                $lead->provided = 1;
                $lead->save();
                break;

        } // sw status
    }

    static public function generateFollowups($lead, $force = false)
    {
        \Log::info("Generating Followups for Lead: $lead->id");
        $presets = [
            0 => 51,
            1 => 53,
            2 => 56,
            3 => 1,
        ];

        if ($lead->followups()->count() == 0 || $force)
        {
            Followup::whereLeadId($lead->id)->delete();
            Notification::whereFollowupId($lead->id)->delete();
            foreach (range(1, 13) AS $item)
            {
                $followup = new Followup;
                $followup->lead_id = $lead->id;
                $followup->user_id = 0;
                $followup->stage = $item;
                $followup->status_id = !empty($presets[$item]) ? $presets[$item] : $presets[0];
                $followup->save();
                if ($followup->status_id != 51)
                {
                    StatusManager::setFollowup($followup, $followup->status_id, false);
                }
            }
        }
    }
}