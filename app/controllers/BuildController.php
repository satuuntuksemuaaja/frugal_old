<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/24/15
 * Time: 9:02 AM
 */
class BuildController extends BaseController
{

    public $layout = "layouts.main";

    /**
     * Show all Build Status for jobs that have a start date
     */
    public function index()
    {
        $view = View::make('build.index');
        $this->layout->title = "Buildup";
        $this->layout->content = $view;
    }


    /**
     * Show that a job item has been built.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function build($id)
    {
        $job = Job::find($id);
        $job->built = 1;
        $job->save();
        // Now mail it to chris@vocalogic.com
        $customer = $job->quote->lead->customer;
        Mail::send('emails.built', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[BUILD] Build Complete for $customer->name");
        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Show that a job has been loaded into the truck
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function load($id)
    {
        $job = Job::find($id);
        $job->loaded = 1;
        $job->save();
        // Now mail it to chris@vocalogic.com
        $customer = $job->quote->lead->customer;
        Mail::send('emails.loaded', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[LOAD] Truck Loading Complete for $customer->name");
        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }

    /**
     * Truck Left The Station
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function left($id)
    {
        $job = Job::find($id);
        $job->truck_left = 1;
        $job->save();
        // Now mail it to chris@vocalogic.com
        $customer = $job->quote->lead->customer;
        Mail::send('emails.loaded', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[TRUCK LEFT] Truck has left for $customer->name");
        });
        return Response::json(['status' => 'success', 'action' => 'selfreload']);
    }
}