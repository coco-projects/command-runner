<?php

    use Coco\commandRunner\PhpLauncher;

    require '../vendor/autoload.php';

    $script = 'test.php';

    $launcher = new PhpLauncher($script);

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: PhpLauncher::getStandardFormatter());
    $launcher->addRedisHandler(callback: PhpLauncher::getStandardFormatter());
