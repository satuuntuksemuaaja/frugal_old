<?php

class AjaxController extends BaseController
{

    public function status($stage = 1)
    {
        $out = [];
        $status = Status::orderBy('name', 'ASC')->whereStageId($stage)->whereFollowupStatus(false)->get();
        foreach ($status AS $status)
        {
            $out[] = ['value' => $status->id, 'text' => $status->name];
        }
        return Response::json($out);
    }


    public function locations()
    {
        $out = [];
        $out[] = ['value' => 'Fayetteville', 'text' => "Fayetteville"];
        $out[] = ['value' => 'Roswell', 'text' => "Roswell"];
        $out[] = ['value' => 'Toco Hills', 'text' => "Toco Hills"];
        $out[] = ['value' => 'Acworth', 'text' => "Acworth"];
        $out[] = ['value' => 'Peachtree City', 'text' => 'Peachtree City'];

        return Response::json($out);
    }

    public function designers()
    {
        $designers = User::whereLevelId(2)->get();
        $out = [];
        foreach ($designers AS $designer)
        {
            $out[] = ['value' => $designer->id, 'text' => $designer->name];
        }
        return Response::json($out);
    }


}