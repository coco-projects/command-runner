<?php

    namespace Coco\commandRunner;

class Launcher
{
    use \Coco\logger\Logger;

    public int $times = 1;

    public function __construct(public string $command)
    {
    }

    public function setTimes(string $times): static
    {
        $this->times = $times;

        return $this;
    }

    public function getLanuchCommand(): string
    {
        $arr = [
            'nohup',
            $this->command,
            '> /dev/null 2>&1 &',
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

    public function getKillByKeywordCommand(string $keyword): string
    {
        $arr = [
            'pkill',
            '-f',
            '"' . $keyword . '"',
        ];

        return implode(' ', $arr);
    }

    public function killByKeyword(string $keyword): void
    {
        $command = $this->getKillByKeywordCommand($keyword);

        $this->exec($command);
    }

    public function killByPid(int $pid): void
    {
        $command = $this->getKillByPidCommand($pid);

        $this->exec($command);
    }

    public function launch(): void
    {
        for ($i = 0; $i < $this->times; $i++) {
            $command = $this->getLanuchCommand();
            $this->exec($command);
        }
        $this->logInfo('启动完成,当前启动:' . $this->times);
    }

    protected function exec($command): bool
    {
        exec($command, $output, $status);

        if ($status === 0) {
            $msg = "执行成功: " . $command;
            $this->logInfo($msg);

            return true;
        } else {
            $msg = "执行失败: " . $command;
            $msg .= json_encode($output, 256);
            $this->logInfo($msg);

            return false;
        }
    }

    public function getProcessListByKeyword(string $keyword): array
    {
        $arr = [
            'ps aux | grep',
            '"' . $keyword . '"',
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
