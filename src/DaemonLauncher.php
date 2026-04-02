<?php

    namespace Coco\commandRunner;

    ///bin/php --a=aa -b bb -c cc
    class DaemonLauncher extends Launcher
    {
        protected string $bin;
        protected bool   $allowMultiLaunch = false;

        public function __construct(string $command)
        {
            $t         = explode(' ', $command);
            $this->bin = $t[0];

            $this->setKeyword($this->bin);
            parent::__construct($command);
        }


        public function chdir(string $dir): static
        {
            chdir($dir);

            return $this;
        }
    }
