<?php

    use Coco\commandRunner\Launcher;

    require '../vendor/autoload.php';

    $script = 'php test.php';

    $launcher = new Launcher($script);

    $launcher->launch();


