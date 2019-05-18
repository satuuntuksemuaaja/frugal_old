<?php
namespace vl\core;
class Formatter
{

  static public function numberFormat($number)
  {
    return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '$1.$2.$3', $number);
  }

  static public function getTimes($set = null)
  {
    $opts = [];
    if ($set)
    {
      $opts[] = ['val' => $set, 'value' => date("H:i a", strtotime($set))];
    }
    $opts[] = ['val' => '09:00', 'text' => '09:00 AM'];
    $opts[] = ['val' => '09:30', 'text' => '09:30 AM'];
    $opts[] = ['val' => '10:00', 'text' => '10:00 AM'];
    $opts[] = ['val' => '10:30', 'text' => '10:30 AM'];
    $opts[] = ['val' => '11:00', 'text' => '11:00 AM'];
    $opts[] = ['val' => '11:30', 'text' => '11:30 AM'];
    $opts[] = ['val' => '12:00', 'text' => '12:00 AM'];
    $opts[] = ['val' => '12:30', 'text' => '12:30 AM'];
    $opts[] = ['val' => '13:00', 'text' => '01:00 PM'];
    $opts[] = ['val' => '13:30', 'text' => '01:30 PM'];
    $opts[] = ['val' => '14:00', 'text' => '02:00 PM'];
    $opts[] = ['val' => '14:30', 'text' => '02:30 PM'];
    $opts[] = ['val' => '15:00', 'text' => '03:00 PM'];
    $opts[] = ['val' => '15:30', 'text' => '03:30 PM'];
    $opts[] = ['val' => '16:00', 'text' => '04:00 PM'];
    $opts[] = ['val' => '16:30', 'text' => '04:30 PM'];
    $opts[] = ['val' => '17:00', 'text' => '05:00 PM'];
    $opts[] = ['val' => '17:30', 'text' => '05:30 PM'];
    $opts[] = ['val' => '18:00', 'text' => '06:00 PM'];
    $opts[] = ['val' => '18:30', 'text' => '06:30 PM'];
    $opts[] = ['val' => '19:00', 'text' => '07:00 PM'];
    $opts[] = ['val' => '19:30', 'text' => '07:30 PM'];
    return $opts;
  }

}