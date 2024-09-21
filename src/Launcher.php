<?php

    namespace Coco\commandRunner;

class Launcher
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
    }

    public function setTimes(string $times): static
    {
        $this->times = $times;

        return $this;
    }

    public function getLanuchCommand(): string
    {
        $this->chdir();

        $arr = [
            'nohup',
            $this->phpBin,
            $this->scriptPath,
            '> /dev/null 2>&1 &',
        ];

        return implode(' ', $arr);
    }

    public function getStopCommand(): string
    {
        $arr = [
            'pkill',
            '-f',
            '"' . $this->scriptPath . '"',
        ];

        return implode(' ', $arr);
    }


    public function getKillByPidCommand(int $pid): string
    {
        $arr = [
            'kill',
            '-9',
            $pid,
        ];

        return implode(' ', $arr);
    }

    public function killByPid(int $pid): void
    {
        $command = $this->getKillByPidCommand($pid);
        exec($command, $output, $status);

        if ($status === 0) {
            $msg = "执行成功: " . $command . PHP_EOL;
        } else {
            $msg = "执行失败: " . $command . PHP_EOL;
            $msg .= json_encode($output, 256) . PHP_EOL;
        }

        echo $msg;
    }

    public function launch(): void
    {
        for ($i = 0; $i < $this->times; $i++) {
            $command = $this->getLanuchCommand();
            exec($command, $output, $status);

            if ($status === 0) {
                $msg = "执行成功: " . $command . PHP_EOL;
            } else {
                $msg = "执行失败: " . $command . PHP_EOL;
                $msg .= json_encode($output, 256) . PHP_EOL;
            }

            echo $msg;
        }

        echo '启动完成,当前启动:' . $this->times . ',一共启动:' . $this->getCount();
    }

    public function stop(): void
    {
        $count = $this->getCount();
        if ($count) {
            $command = $this->getStopCommand();

            exec($command, $output, $status);

            if ($status === 0) {
                $msg = "执行成功: " . $command . PHP_EOL;
                $msg .= '共:' . $count . PHP_EOL;
            } else {
                $msg = "执行失败: " . $command . PHP_EOL;
                $msg .= json_encode($output, 256) . PHP_EOL;
            }

            echo $msg;
        } else {
            echo '没有启动的任务';
        }
    }

    public function getCount(): ?int
    {
        return count($this->getProcessList());
    }

    public function getProcessList(): array
    {
        $arr = [
            'ps aux | grep',
            '"' . $this->scriptPath . '"',
        ];

        $command = implode(' ', $arr);

        exec($command, $output, $status);

        $result = [];
        foreach ($output as $k => $line) {
            preg_match('/^(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*([^\r\n]+)/sm', $line, $match);

            if (!str_contains($match[11], 'ps aux') and !str_starts_with($match[11], "grep ")) {
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
