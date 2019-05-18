<?php
class MobileSeeder extends Seeder
{
  public function run()
  {
    Eloquent::unguard();
    DB::table('punches')->truncate();
    DB::table('punch_answers')->truncate();
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