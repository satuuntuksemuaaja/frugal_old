<?php
echo "<h3>The following is a message from Frugal Kitchens and Cabinets:</h3>";
if (isset($noNL))
    echo $content;
else
    echo nl2br($content);