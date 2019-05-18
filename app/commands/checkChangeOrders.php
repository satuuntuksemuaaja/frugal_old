<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class checkChangeOrders extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'changeOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'E-mail all customers that have sent change orders and not closed.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        foreach (ChangeOrder::whereClosed(false)->get() AS $order)
        {
            try
            {
                $data['order'] = $order;
                $customer = $order->job->quote->lead->customer->contacts()->first();
                Mail::send('emails.changerequest', $data, function ($message) use ($customer)
                {
                    $message->to([$customer->email]);
                    $message->subject("You have a change order from Frugal Kitchens requiring your attention.");

                });
            } catch (Exception $e)
            {

            }
        }
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
