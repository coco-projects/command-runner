<?php

    namespace Coco\commandRunner;

class DaemonLauncher extends Launcher
{
    public string $command;
    public string $bin;

    public function __construct(string $command)
    {
        $t         = explode(' ', $command);
        $this->bin = $t[0];

        parent::__construct($command);
    }

    public function getStopCommand(): string
    {
        return $this->getKillByKeywordCommand($this->bin);
    }

    public function stop(): void
    {
        $count = $this->getCount();
        if ($count) {
            $command = $this->getStopCommand();
            $this->exec($command);
        } else {
            $this->logInfo('没有启动的任务');
        }
    }

    public function getCount(): ?int
    {
        return count($this->getProcessList());
    }

    public function getProcessList(): array
    {
        return $this->getProcessListByKeyword($this->bin);
    }
}
