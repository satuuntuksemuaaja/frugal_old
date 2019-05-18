<?php
namespace vl\core;

use Action;
use Carbon\Carbon;
use Designation;
use Exception;
use Expiration;
use Lead;
use Log;
use Mail;
use Notification;
use Showroom;
use Status;
use User;

class NotificationEngine
{

    /**
     * Normally run from a cron, this will check all notifications to see if they need to be
     * sent and if so, send texts/emails. It will also check to see if a notification is no
     * longer required. Like, if there is a status change and the notification is no longer
     * required it will delete it.
     */
    static public function run()
    {
        $notifications = Notification::all();
        foreach ($notifications AS $notification)
        {
            $lead = Lead::find($notification->reference);
            if ($lead->archived) // Do not run notifications on an archived lead.
            {
                $notification->delete();
                continue;
            }
            self::checkNoteNotifications($notification, $lead);
            if ($notification->expires <= Carbon::now()) // Expires before right now
            {
                self::fireNotification($notification);
            }
        }
    }

    /**
     * Makes the determination of either an SMS or email being sent. This will be used
     * for standard time-based notifications when a status has not changed.
     *
     * @param Notification $notification
     * @throws Exception
     */
    static public function fireNotification(Notification $notification)
    {
        $lead = Lead::find($notification->reference); // Lead::find(1);
        if ($lead)
        {
            if ($lead->status_id == 2 || $lead->status_id == 58)
            {
                return; // This is a lost lead, don't do anything.
            }
        }
        $expiration = Expiration::find($notification->expiration_id);

        foreach ($expiration->actions AS $action)
        {
            if ($action->sms)
            {
                self::sendSMS($action, $notification);
            }
            if ($action->email)
            {
                self::sendEmail($action, $notification);
            }
        }
        $notification->delete();
    }

    /**
     * This function will serve as the lead note timeline vs. Carbon::now
     *
     * @param \Notification $notification
     * @param \Lead $lead
     */
    static public function checkNoteNotifications(Notification $notification, Lead $lead)
    {
        $last = $lead->last_note;
        $expiration = Expiration::find($notification->expiration_id);
        // Log::info("Checking Expiration.. (Last: $lead->last_note) Notification exp: $notification->expires");
        if ($last < $notification->expires) return; // Don't fire this. Expiration is in future.
        //  Log::info("Passed Expiration Test..");
        if ($expiration->type != 'Last Note') return; // Not an expiration we care about.
        //  Log::info("Passed Last Note Test..");

        // Start the Actionable Items.
        $lead->warning = $expiration->warning;
        Log::info("Setting Lead warning to $expiration->warning");
        $lead->save();
        return;

    } // checkNoteNotifications

    /**
     * Send SMS based on action. If this notification goes to the customer
     * then send it to the first contact in the customer table.
     *
     * @param Action $action
     * @param Notification $notification
     * @return bool
     */
    static public function sendSMS(Action $action, Notification $notification)
    {
        switch ($action->designation_id) // who is this going to? We need an object to get info from.
        {

            case 7 : // Goes to a customer
                $object = Lead::find($notification->reference);
                if ($object)
                {
                    $number = @$object->customer->contacts()->first()->mobile;
                }
                else
                {
                    return false;
                }
                break;

            default : // Goes to an employee, probably designer.. whoever is assigned to the original lead.
                $object = Lead::find($notification->reference); // Lead::find(1);
                if ($object)
                {
                    if ($object->status_id == 35 || $object->status_id == 36)
                    {
                        Log::info("Sending to Digital Measurer Override..");
                        $o = User::find($object->measure->measurer_id);
                        $number = $o->mobile;
                    }
                    else
                    {
                        $designation = Designation::find($action->designation_id);
                        if ($designation->override_sms)
                        {
                            $number = $designation->override_sms;
                            Log::info("Designation override set to $designation->override_sms for Lead $object->id..");
                        }
                        else
                        {
                            if (empty($object->user)) continue;
                            $number = $object->user->mobile;
                        }
                    }
                }
                else
                {
                    return false;
                }
                break;

        } // sw

        if (isset($number) && $number)
        {
            SMS::command('directory.send',
                [
                    'target'  => $number,
                    'message' => self::parseMessage($notification, $action->sms_content)
                ]);
            Log::info("Fired SMS Message to $number ($action->sms_content)");
        }
        return;
    }

    /**
     * Send an email to the designation listed. 7 is Customer, 8 is Employee Designation.
     *
     * @param  Action $action [description]
     * @param Notification $notification
     * @return bool [type]         [description]
     */
    static public function sendEmail(Action $action, Notification $notification)
    {
        switch ($action->designation_id) // who is this going to? We need an object to get info from.
        {


            case 7 : // Goes to a customer
                $object = Lead::find($notification->reference); // Lead::find(1);
                if ($object)
                {
                    $email = @$object->customer->contacts()->first()->email;
                    $to = @$object->customer->contacts()->first()->name;
                }
                else
                {
                    return false;
                }
                break;

            default : // Goes to an employee, probably designer.. whoever is assigned to the original lead.
                $object = Lead::find($notification->reference); // Lead::find(1);
                if ($object)
                {
                    if ($object->status_id == 35 || $object->status_id == 36)
                    {
                        Log::info("Sending to Digital Measurer Override..");
                        $o = User::find($object->measure->measurer_id);
                        $email = $o->email;
                        $to = $o->name;
                    }
                    else
                    {
                        $designation = Designation::find($action->designation_id);
                        if ($designation->override_email)
                        {
                            $email = $designation->override_email;
                            $to = $designation->override_email;
                            Log::info("Designation override set to $designation->override_email for Lead $object->id..");
                        }
                        else
                        {
                            if (!$object->user)
                            {
                                \Log::info("There is no user assigned to lead $object->id");
                            }
                            $email = $object->user->email;
                            $to = $object->user->name;
                        }
                    }
                }
                else
                {
                    return false;
                }
                break;
        }

        $subject = (self::parseMessage($notification,
            $action->email_subject)) ?: "You have a new notification from Frugal Kitchens";
        $out['content'] = self::parseMessage($notification, $action->email_content);
        $attachment = ($action->attachment) ? public_path() . "/statuses/$action->id/$action->attachment" : null;
        try
        {
            Mail::send('emails.notification', $out, function ($message) use ($email, $to, $subject, $attachment)
            {
                Log::info("Firing email to $email with $subject");
                $message->to($email, $to)->subject($subject);
                if ($attachment)
                {
                    str_replace(" ", '\ ', $attachment);
                    $message->attach($attachment);
                }
            });

        } catch (Exception $e)
        {
            Log::info("Message failed: " . $e->getMessage());
        }
    }

    /**
     * Creates a notification based on the status found
     *
     * @param  [type] $object [description]
     * @param Carbon $overrideExpiration
     * @param bool $status Status override for followups.
     * @return bool [type]         [description]
     */
    static public function createNotification($object, Carbon $overrideExpiration = null, $status = false, $followup = false)
    {
        if (!$status)
        {
            if (get_class($object) == 'Lead' || get_class($object) == 'Followup') // Setting a lead status notification
            {
                $status = Status::find($object->status_id);
            }
        }
        else $status = Status::find($status);

        $for = 'Lead';
        Log::info("Entrance into Create Notification shows an object (lead id) of $object->id");

        if ($overrideExpiration)
        {
            $notification = new Notification;
            $notification->isfor = $for;
            $notification->reference = $object->id;
            $notification->status_id = $status->id;
            $notification->set = Carbon::now();
            $notification->expires = $overrideExpiration;
            $notification->save();
            return true;
        }

        foreach ($status->expirations AS $expiration)
        {
            $now = time();
            $expires = 0;
            if ($expiration->expires)
            {
                $expires = $now + $expiration->expires;
                $expires = Carbon::createFromTimeStamp($expires);
            }
            if ($expiration->expires_before)
            {
                // Hours before Showroom Scheduled.
                $showroom = Showroom::whereLeadId($object->id)->first();
                if ($showroom)
                {
                    $expires = $showroom->scheduled->subHours($expiration->expires_before / 60 / 60);
                    Log::info("Creating notification for lead $object->id to expire on $expires. The original date was $showroom->scheduled for schedule id $showroom->id");
                }

                else
                {
                    // For #234 - we are going to assume this date is TODAY.
                    $expires = Carbon::now()->subHours($expiration->expires_before / 60 / 60);

                }
            }
            if ($expiration->expires_after)
            {
                // Hours before Showroom Scheduled.
                $showroom = Showroom::whereLeadId($object->id)->first();
                if ($showroom)
                {
                    $expires = $showroom->scheduled->addHours($expiration->expires_after / 60 / 60);
                    Log::info("[AFTER] [EXPID: $expiration->id] Creating notification for lead $object->id to expire on $expires. Showroom Schedule was $showroom->scheduled - we added $expiration->expires_after hours to the date");
                }
                else
                {
                    $expires = Carbon::now()->addHours($expiration->expires_after / 60 / 60);
                }
            }

            $notification = new Notification;
            $notification->isfor = $for;
            $notification->expiration_id = $expiration->id;
            $notification->reference = get_class($object) == 'Lead' ? $object->id : $object->lead_id;
            $notification->followup_id = $followup ? $object->id : 0;
            $notification->status_id = $status->id;
            $notification->set = Carbon::now();
            $notification->expires = $expires;
            $notification->save();
            \Log::info("Created an expiration for $notification->expires for Status $status->id");
        } // fe expriation

        // If any were 0 we should fire them now. So just run for fun.
        self::run();
    }

    /**
     * This will be called generally before a create notification.
     *
     * @param  [type] $object [description]
     * @return bool [type]         [description]
     */
    static public function removeOldNotifications($object)
    {
        if ($object instanceof Lead)
        {
            Notification::whereReference($object->id)->whereIsfor('Lead')->where('followup_id',0)->delete();
        }
        if ($object instanceof Followup)
        {
            Notification::whereFollowupId($object->id)->delete();
        }
        return true;
    }

    /**
     * Parses the message for keys.
     * @param Notification $notification
     * @param              $message
     * @return mixed
     */
    static public function parseMessage(Notification $notification, $message)
    {
        try
        {
            switch ($notification->isFor)
            {
                case 'Lead' :
                    $lead = Lead::find($notification->reference);
                    $message = str_replace("[client_name]", $lead->customer->name, $message);
                    $message = str_replace("[client_address]", $lead->customer->address, $message);
                    $message = str_replace("[client_city]", $lead->customer->city, $message);
                    $message = str_replace("[client_state]", $lead->customer->state, $message);
                    $message = str_replace("[client_zip]", $lead->customer->zip, $message);
                    $message = str_replace("[client_phone]", $lead->customer->contacts()->first()->home, $message);
                    $message = str_replace("[client_email]", $lead->customer->contacts()->first()->email, $message);
                    $message = str_replace("[client_mobile]", $lead->customer->contacts()->first()->mobile, $message);
                    $message = str_replace("[designer_name]", $lead->user->name, $message);
                    $message = str_replace("[user_name]", $lead->user->name, $message);
                    $message = str_replace("[designer_phone]", $lead->user->mobile, $message);
                    $message = str_replace("[designer_email]", $lead->user->email, $message);
                    if ($lead->measure && $lead->measure->user)
                    {
                        $message = str_replace("[digital]",
                            Carbon::parse($lead->measure->scheduled)->format("m/d/y h:i a"),
                            $message);
                        $message = str_replace("[digital_name]", $lead->measure->user->name, $message);
                        $message = str_replace("[digital_phone]", $lead->measure->user->mobile, $message);
                        $message = str_replace("[digital_email]", $lead->measure->user->email, $message);
                    }

                    if (preg_match('/\[showroom\]/', $message))
                    {
                        $message = str_replace("[showroom]",
                            Carbon::parse($lead->showroom->scheduled)->format("m/d/y h:i a"), $message);
                        $message = str_replace("[lead_location]", $lead->showroom->location, $message);
                    }
                    if (preg_match('/\[closing\]/', $message))
                    {
                        $message = str_replace("[closing]",
                            Carbon::parse($lead->closing->scheduled)->format("m/d/y h:i a"), $message);
                    }

                    break;
            }
        } catch (\Exception $e)
        {
            \Log::info("ERROR: " . $e->getMessage());
        }

        return $message;
    }


}