<?php
class UniSeeder extends Seeder
{
  public function run()
  {
    foreach (Accessory::all() AS $accessory)
    {
      $accessory->description = mb_convert_encoding ($accessory->description, 'US-ASCII', 'UTF-8');
      $accessory->save();
    }



  }


}