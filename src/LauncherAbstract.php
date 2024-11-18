<?php

    namespace Coco\commandRunner;

    use Coco\logger\Logger;

abstract class LauncherAbstract
{
    use Logger;

    protected bool $isSudo = false;

    public function __construct(public string $command)
    {
    }

    abstract public function launch(): void;

    abstract public function getLanuchCommand(): string;

    public function setIsSudo(bool $isSudo): static
    {
        $this->isSudo = $isSudo;

        return $this;
    }

    protected function exec($command): array
    {
        exec($command, $output, $status);

        if ($status === 0) {
            $msg = "执行成功: " . $command;
            $this->logInfo($msg);

            return [
                "output" => $output,
                "status" => $status,
            ];
        } else {
            $msg = "执行失败: " . $command;
            $msg .= json_encode($output, 256);
            $this->logInfo($msg);

            return [
                "output" => $output,
                "status" => $status,
            ];
        }
    }

    protected static function formatMemorySize($size): string
    {
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        // 格式化输出，保留两位小数
        return round($size, 2) . ' ' . $units[$i];
    }
}
