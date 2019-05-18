<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/23/17
 * Time: 11:34 AM
 */


echo BS::title("Appliance Configuration", "Thanks!");

echo BS::row(BS::span(12, "<div class='alert alert-success'>Thank you for supplying your appliance information. You can close this window."));
echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");

