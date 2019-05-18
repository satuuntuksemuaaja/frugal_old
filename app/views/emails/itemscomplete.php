<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 3/25/15
 * Time: 3:58 PM
 */
echo "The items listed on the job board for <b>{$job->quote->lead->customer->contacts()->first()->name}</b> have all been
marked completed.";
