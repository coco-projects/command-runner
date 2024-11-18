<?php

    namespace Coco\commandRunner;

    use Coco\commandBuilder\command\Sudo;
    use Coco\commandBuilder\command\Systemctl;

class ServiceLauncher extends LauncherAbstract
{
    public function launch(): void
    {
        $command = $this->getLanuchCommand();
        $this->exec($command);
    }

    public function restart(): void
    {
        $command = $this->getRestartCommand();
        $this->exec($command);
    }

    public function stop()
    {
        $command = $this->getStopCommand();
        $this->exec($command);
    }

    public function enable()
    {
        $command = $this->getEnableCommand();
        $this->exec($command);
    }

    public function disable()
    {
        $command = $this->getDisableCommand();
        $this->exec($command);
    }

    public function isStarted(): bool
    {
        $list        = $this->getListunits();
        $serviceName = $this->command . '.service';

        if (!isset($list[$serviceName])) {
            return false;
        }

        $item = $list[$serviceName];

        return (strtolower($item[3]) === 'active');
    }

    public function isAutoStarted(): bool
    {
        $list        = $this->getlistUnitFiles();
        $serviceName = $this->command . '.service';

        if (!isset($list[$serviceName])) {
            return false;
        }

        $item = $list[$serviceName];

        return (strtolower($item[2]) === 'enabled');
    }

    public function hasService(): bool
    {
        $list        = $this->getlistUnitFiles();
        $serviceName = $this->command . '.service';

        return isset($list[$serviceName]);
    }

    public function getListunits(): array
    {
        $command = $this->getListunitsCommand();
        $result  = $this->exec($command);

        $list = $result['output'];

        array_shift($list);
        array_pop($list);
        array_pop($list);
        array_pop($list);
        array_pop($list);
        array_pop($list);
        array_pop($list);

        $output = implode(PHP_EOL, $list);
        preg_match_all('/^[\s●]*(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+([^\r\n]+)/sm', $output, $matchs, PREG_SET_ORDER);

        $data = [];
        foreach ($matchs as $match) {
            $data[$match[1]] = $match;
        }

        return $data;
    }

    public function getlistUnitFiles(): array
    {
        $command = $this->getlistUnitFilesCommand();
        $result  = $this->exec($command);

        $list = $result['output'];

        array_shift($list);
        array_pop($list);
        array_pop($list);

        $output = implode(PHP_EOL, $list);
        preg_match_all('/^(\S+)\s+(\S+)\s+(\S+)/sm', $output, $matchs, PREG_SET_ORDER);

        $data = [];
        foreach ($matchs as $match) {
            $data[$match[1]] = $match;
        }

        return $data;
    }

    public function getListunitsCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('list-units')->all()->type('service')->noPager();

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getlistUnitFilesCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('list-unit-files')->type('service')->noPager();

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getLanuchCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('start')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getRestartCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('restart')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getStopCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('stop')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getStatusCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('status')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getEnableCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('enable')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }

    public function getDisableCommand(): string
    {
        $command = Systemctl::getIns();
        $command->action('disable')->unit($this->command);

        if ($this->isSudo) {
            $sudo = Sudo::getIns();
            $sudo->setSubCommand($command);
            $command = $sudo;
        }

        return (string)$command;
    }
}
