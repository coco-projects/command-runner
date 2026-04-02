<?php

    use Coco\commandRunner\DaemonLauncher;
    use Coco\commandRunner\Launcher;

    require '../../vendor/autoload.php';

    $launcher = new DaemonLauncher('/bin/php --a=aa -b bb -c cc');

    $launcher->setUseNohup(true);
    $launcher->setOutput('./out.txt');

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: Launcher::getStandardFormatter());
    $launcher->addRedisHandler(callback: Launcher::getStandardFormatter());

//    $launcher->killByKeyword('test.php');

    echo $launcher->getKillByPidCommand(456);
    echo PHP_EOL;
    echo $launcher->getKillByKeywordCommand();
    echo PHP_EOL;

    echo $launcher->getTERMByPidCommand(55);
    echo PHP_EOL;
    echo $launcher->getTERMByKeywordCommand();
    echo PHP_EOL;

    //    $launcher->launch();


