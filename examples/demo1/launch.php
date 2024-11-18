<?php

    require "./common.php";

    $launcher->setIsSudo(!true);

//    echo $launcher->getLanuchCommand();
    $launcher->launch();