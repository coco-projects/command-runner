<?php

    namespace Coco\commandRunner;

class PhpLauncher extends InterpreterLauncher
{
    public function __construct(string $scriptPath, string $interpreterBin = 'php')
    {
        parent::__construct($scriptPath, $interpreterBin);
    }
}
