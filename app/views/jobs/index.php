<?php
echo vl\jobs\JobBoard::init($jobs);
echo Modal::init()->id('workModal')->onlyConstruct()->render(); // use for multiple ajax modals