<?php

    namespace Coco\commandRunner;

    class InterpreterLauncher extends Launcher
    {
        protected string $scriptName;
        protected bool   $allowMultiLaunch = false;
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

        public function getStopCommand(): string
        {
            return $this->getKillByKeywordCommand($this->scriptPath);
        }

        public function stop(): void
        {
            $count = $this->getCount();
            if ($count)
            {
                $command = $this->getStopCommand();
                $this->exec($command);
            }
            else
            {
                $this->logInfo('没有启动的任务');
            }
        }

        public function getCount(): ?int
        {
            return count($this->getProcessList());
        }

        public function getProcessList(): array
        {
            return $this->getProcessListByKeyword($this->scriptPath);
        }

        protected function chdir(): void
        {
            chdir(dirname($this->scriptPath));
        }

        public function setAllowMultiLaunch(bool $allowMultiLaunch): static
        {
            $this->allowMultiLaunch = $allowMultiLaunch;

            return $this;
        }

        public function launch(): void
        {
            if ($this->allowMultiLaunch)
            {
                parent::launch();
            }
            else
            {
                if (!$this->getCount())
                {
                    parent::launch();
                }
            }
        }
    }
