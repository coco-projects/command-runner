<?php

    use Coco\commandRunner\Launcher;

    require '../vendor/autoload.php';

    $script = 'php test.php';

    $launcher = new Launcher($script);

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: Launcher::getStandardFormatter());

    $launcher->launch();


