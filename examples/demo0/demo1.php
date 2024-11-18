<?php

    use Coco\commandRunner\ServiceLauncher;

    require '../../vendor/autoload.php';

    $launcher = new ServiceLauncher('nginx');

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: ServiceLauncher::getStandardFormatter());

    $launcher->setIsSudo(true);
    $launcher->restart();
//    $launcher->stop();
//    $launcher->disable();

//    var_export($launcher->hasService());
//    echo PHP_EOL;

//    var_export($launcher->isStarted());
//    echo PHP_EOL;

    var_export($launcher->isAutoStarted());


