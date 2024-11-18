<?php

    use Coco\commandRunner\PhpLauncher;

    require '../../vendor/autoload.php';

    $script = '../test.php';

    $launcher = new PhpLauncher($script);

    //sudo和nohup不能同时使用，否则会启动一个脚本两次
    $launcher->setUseNohup(true);

    $launcher->setOutput('./log.txt');

    $launcher->setStandardLogger('test');
    $launcher->addStdoutHandler(callback: PhpLauncher::getStandardFormatter());
    $launcher->addRedisHandler(callback: PhpLauncher::getStandardFormatter());
