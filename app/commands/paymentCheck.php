<?php

use Carbon\Carbon;
use Illuminate\Console\Command;

class paymentCheck extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'paymentCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FFT Payment Check (Change Order and Schedule Notifications)';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        Mail::send('emails.payment', [], function ($message)
        {
            $now = Carbon::now()->format('m/d/y');
            $message->to("kimw@frugalkitchens.com", "Frugal Reports")->subject("[$now] Daily Final Touch Payment Report");
        });
        Log::info("Report Fired Off.");

        // Next we want to run the changeOrder E-mail Notification, as well as the ScheduleNotifications.
        ChangeController::dailyCheck();

        // Now check Job Schedules
        JobController::dailyCheck();

        // Now Check POS
        PurchaseController::dailyCheck();

        // Checks for duplicate leads
        ReportController::duplicateCheck();

        // If we are after a punch scheuduled and there is no signature, then let punch@frugal know.
        PunchController::checkSignature();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
