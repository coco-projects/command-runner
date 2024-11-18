<?php

    use Coco\commandRunner\Launcher;
    use Coco\commandBuilder\Builder;

    require '../../vendor/autoload.php';

    $command = new Builder('php');
    $command->setSubCommand('../test.php');

    $launcher = new Launcher($command);
    $launcher->setUseNohup(false);
    $launcher->setOutput('./out.txt');

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: Launcher::getStandardFormatter());
    $launcher->addRedisHandler(callback: Launcher::getStandardFormatter());

//    $launcher->killByKeyword('test.php');

    $launcher->launch();


