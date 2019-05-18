<?php
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/1/15
 * Time: 8:41 AM
 */
class PunchController extends BaseController
{

    public $layout = "layouts.main";

    public function index($job)
    {
        $view = View::make('punches.index');
        $view->fft = FFT::find($job);
        $this->layout->title = "Job Punch";
        $this->layout->content = $view;
    }

    /**
     * Run from the daily check, this will see we are past the
     * punch scheduled date.
     */
    static public function checkSignature()
    {
        $now = Carbon::now();
        // Checking schedule_start and warranty = false.
        foreach (FFT::whereWarranty(false)->whereClosed(false)->get() as $fft)
        {
            if ($fft->schedule_start->timestamp > 0) // We have an assigned date.
            {
                if ($now > $fft->schedule_start && !$fft->signature && !$fft->punch_reminder_emailed) // Today is greater than this date and no sig
                {
                    $custname = $fft->job->quote->lead->customer->name;
                    $data['content'] = "A punch was scheduled for {$fft->schedule_start->format("m/d/y")} and no signature has been found.";
                    Mail::send('emails.notification', $data, function ($message) use ($custname, $fft)
                    {
                        $message->to(['punch@frugalkitchens.com']);
                        //$message->to(['chris@vocalogic.com']);
                        $message->subject("[$custname] Punch was assigned on {$fft->schedule_start->format("m/d/y")} and no Signature found");
                    });
                    $fft->punch_reminder_emailed = 1;
                    $fft->save();

                }
            }
        }

    }
}