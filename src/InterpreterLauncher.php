<?php

    namespace Coco\commandRunner;

class InterpreterLauncher extends Launcher
{
    public string $command;
    public string $scriptName;

    public function __construct(public string $scriptPath, public string $interpreterBin)
    {
        if (!is_file($scriptPath)) {
            throw new \Exception($scriptPath . ' 不存在');
        }

        $this->scriptPath = realpath($scriptPath);

        $this->scriptName = pathinfo($this->scriptPath, PATHINFO_FILENAME);

        $this->chdir();

        $arr = [
            $this->interpreterBin,
            $this->scriptPath,
        ];

        $command = implode(' ', $arr);

        parent::__construct($command);
    }

    public function getStopCommand(): string
    {
        return $this->getKillByKeywordCommand($this->scriptPath);
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
        return $this->getProcessListByKeyword($this->scriptPath);
    }

    protected function chdir(): void
    {
        chdir(dirname($this->scriptPath));
    }
}
