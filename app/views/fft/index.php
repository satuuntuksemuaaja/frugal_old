<?php
use vl\core\FFTEngine;
if (empty($service)) $service = false;
$type = ($warranty) ? "Warranties" : "Frugal Final Touch";
if ($service) $type = "Service Work";
echo BS::title($type);
echo FFTEngine::init($warranty, $service);
echo "
<span class='badge bg-received' style='color: #000; font-size: 14px;'>Items Received</span> 
<span class='badge bg-walkthrough' style='color: #000; font-size: 14px;'>Needs Walkthrough</span> 
<span class='badge bg-walkthroughschedule' style='color: #fff; font-size: 14px;'>Walkthrough Scheduled</span> 
<span class='badge bg-notsigned' style='color: #fff; font-size: 14px;'>Punch Not Signed</span> 
<span class='badge bg-ordered' style='color: #fff; font-size: 14px;'>All items ordered</span> 
<span class='badge bg-notordered' style='color: #fff; font-size: 14px;'>Items not ordered</span> 

<span class='badge bg-pscheduled' style='color: #fff; font-size: 14px;'>Punch Scheduled</span> 




";
echo Modal::init()->id('workModal')->onlyConstruct()->render();