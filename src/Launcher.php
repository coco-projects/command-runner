<?php

    namespace Coco\commandRunner;

    use Coco\commandBuilder\BuilderRegistry;
    use Coco\commandBuilder\command\Grep;
    use Coco\commandBuilder\command\Kill;
    use Coco\commandBuilder\command\Nohup;
    use Coco\commandBuilder\command\Pkill;
    use Coco\commandBuilder\command\Ps;
    use Coco\commandBuilder\command\Sudo;

    class Launcher extends LauncherAbstract
    {
        protected string $output           = '/dev/null';
        protected bool   $useNohup         = true;
        protected bool   $allowMultiLaunch = false;

        public function setUseNohup(bool $useNohup): static
        {
            $this->useNohup = $useNohup;

            return $this;
        }

        public function setOutput(string $output): static
        {
            $this->output = $output;

            return $this;
        }

        public function getLanuchCommand(): string
        {
            $command = $this->command . ' > ' . $this->output . ' 2>&1';

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            if ($this->useNohup)
            {
                $nohup = Nohup::getIns()->runBackend();
                $nohup->setSubCommand($command);
                $command = $nohup;
            }

            return (string)$command;
        }

        public function getKillByPidCommand(int $pid): string
        {
            $command = Kill::getIns();
            $command->signal(Kill::SIGN_9_KILL)->sendToPid($pid);

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            return (string)$command;
        }

        public function getTERMByPidCommand(int $pid): string
        {
            $command = Kill::getIns();
            $command->signal(Kill::SIGN_15_TERM)->sendToPid($pid);

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            return (string)$command;
        }

        public function getKillByKeywordCommand(): string
        {
            $command = Pkill::getIns();
            $command->matchFullProcessName()->pattern('"' . $this->keyword . '"');

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            return (string)$command;
        }

        public function getTERMByKeywordCommand(): string
        {
            $command = Pkill::getIns();
            $command->matchFullProcessName()->signal(Pkill::SIGN_15_TERM)->pattern('"' . $this->keyword . '"');

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            return (string)$command;
        }

        public function termByKeyword(): void
        {
            $command = $this->getTERMByKeywordCommand();

            $this->exec($command);
        }

        public function termByPid(int $pid): void
        {
            $command = $this->getTERMByPidCommand($pid);

            $this->exec($command);
        }

        public function killByKeyword(): void
        {
            $command = $this->getKillByKeywordCommand();

            $this->exec($command);
        }

        public function killByPid(int $pid): void
        {
            $command = $this->getKillByPidCommand($pid);

            $this->exec($command);
        }

        public function launch(): void
        {
            $command = $this->getLanuchCommand();
            if ($this->allowMultiLaunch)
            {
                $this->exec($command);
            }
            else
            {
                if (!$this->getCount())
                {
                    $this->exec($command);
                }
            }
        }

        public function getProcessListByKeyword(): array
        {
            $ps = Ps::getIns();
            $ps->aux();

            $grep = Grep::getIns();
            $grep->ignoreCase()->usePerlRegex()->pattern('"' . $this->keyword . '"');

            $command = BuilderRegistry::getIns();
            $command->command($ps);
            $command->pipe();
            $command->command($grep);

            if ($this->isSudo)
            {
                $sudo = Sudo::getIns();
                $sudo->setSubCommand($command);
                $command = $sudo;
            }

            exec($command, $output, $status);

            $result = [];
            foreach ($output as $k => $line)
            {
                preg_match('/^(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*([^\r\n]+)/sm', $line, $match);

                if (!str_contains($match[11], 'ps -aux') and !str_starts_with($match[11], "grep "))
                {
                    $result[] = [
                        "user"    => $match[1],
                        "pid"     => $match[2],
                        "cpu"     => $match[3] . '%',
                        "mem"     => $match[4] . '%',
                        "vsz"     => static::formatMemorySize($match[5]),
                        "rss"     => static::formatMemorySize($match[6]),
                        "tty"     => $match[7],
                        "stat"    => $match[8],
                        "start"   => $match[8],
                        "time"    => $match[10],
                        "command" => $match[11],
                    ];
                }
            }

            return $result;
        }

        public function getStopCommand(): string
        {
            return $this->getKillByKeywordCommand();
        }

        public function getTermCommand(): string
        {
            return $this->getTERMByKeywordCommand();
        }

        public function term(): void
        {
            $count = $this->getCount();
            if ($count)
            {
                $command = $this->getTermCommand();
                $this->exec($command);
            }
            else
            {
                $this->logInfo('没有启动的任务');
            }
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
            return $this->getProcessListByKeyword();
        }

        public function setAllowMultiLaunch(bool $allowMultiLaunch): static
        {
            $this->allowMultiLaunch = $allowMultiLaunch;

            return $this;
        }

    }
