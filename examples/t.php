<?php

    $cmd = 'php test.php > ./aa.log 2>&1';
    chdir('.');
    exec($cmd);