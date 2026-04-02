<?php

    namespace Coco\commandRunner;

    class InterpreterLauncher extends Launcher
    {
        protected string $scriptName;
        public string    $scriptPath;

        // console queuedtracking:lock-status --unlock=QueuedTrackingLock0
        public function __construct(string $scriptPath, public string $interpreterBin)
        {
            $t = preg_split('/\s+/sim', $scriptPath, -1, PREG_SPLIT_NO_EMPTY);

            $binPath = $t[0];
            if (!is_file($binPath))
            {
                throw new \Exception($binPath . ' 不存在');
            }

            $this->scriptPath = realpath($binPath);
            $this->scriptName = pathinfo($this->scriptPath, PATHINFO_FILENAME);

            $this->chdir();

            $arr = [
                $this->interpreterBin,
                $scriptPath,
            ];

            $command = implode(' ', $arr);

            $this->setKeyword($this->scriptPath);
            parent::__construct($command);
        }

        protected function chdir(): void
        {
            chdir(dirname($this->scriptPath));
        }
    }
