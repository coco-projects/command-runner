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
    protected string $output   = '/dev/null';
    protected bool   $useNohup = true;

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

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        if ($this->useNohup) {
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

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getKillByKeywordCommand(string $keyword): string
    {
        $command = Pkill::getIns();
        $command->matchFullProcessName()->pattern('"' . $keyword . '"');

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
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
        $command = $this->getLanuchCommand();
        $this->exec($command);
    }

    public function getProcessListByKeyword(string $keyword): array
    {
        $ps = Ps::getIns();
        $ps->aux();

        $grep = Grep::getIns();
        $grep->ignoreCase()->usePerlRegex()->pattern('"' . $keyword . '"');

        $command = BuilderRegistry::getIns();
        $command->command($ps);
        $command->pipe();
        $command->command($grep);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        exec($command, $output, $status);

        $result = [];
        foreach ($output as $k => $line) {
            preg_match('/^(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*(\S+)\s*([^\r\n]+)/sm', $line, $match);

            if (!str_contains($match[11], 'ps -aux') and !str_starts_with($match[11], "grep ")) {
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
}
