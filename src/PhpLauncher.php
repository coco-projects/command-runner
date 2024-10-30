<?php

    namespace Coco\commandRunner;

class PhpLauncher extends Launcher
{
    public string $command;
    public string $scriptName;
    public int    $times = 1;

    public function __construct(public string $scriptPath, public string $phpBin = 'php')
    {
        if (!is_file($scriptPath)) {
            throw new \Exception($scriptPath . ' 不存在');
        }

        if (!is_executable($scriptPath)) {
            throw new \Exception($scriptPath . ' 不可执行');
        }

        $this->scriptPath = realpath($scriptPath);

        $this->scriptName = pathinfo($this->scriptPath, PATHINFO_FILENAME);

        $this->chdir();

        $arr = [
            $this->phpBin,
            $this->scriptPath,
        ];

        $command = implode(' ', $arr);

        parent::__construct($command);
    }

    public function getStopCommand(): string
    {
        return static::getKillByKeywordCommand($this->scriptPath);
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
        return static::getProcessListByKeyword($this->scriptPath);
    }

    protected function chdir(): void
    {
        chdir(dirname($this->scriptPath));
    }

    protected static function formatMemorySize($size): string
    {
        // 定义单位
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        // 选择合适的单位
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        // 格式化输出，保留两位小数
        return round($size, 2) . ' ' . $units[$i];
    }
}
