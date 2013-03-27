<?php

namespace JJs\Bundle\ToolchainBundle\Command;

use JJs\Bundle\ToolchainBundle\Toolchain\ToolchainInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Toolchain Server Command
 *
 * Runs every server in the toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class ToolchainServerCommand extends Command
{
    /**
     * Toolchain
     * 
     * @var ToolchainInterface
     */
    protected $toolchain;

    /**
     * @param ServerInterface $toolchain Toolchain to run servers from
     */
    public function __construct(ToolchainInterface $toolchain)
    {
        $this->toolchain = $toolchain;

        parent::__construct("toolchain:server");
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setDescription("Runs all servers from the toolchain")
        ;
    }

    /**
     * Runs the server command
     *
     * This command will use the plovr build manager to build all configured
     * google closure resources.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Toolchain processes
        $processes = [];

        // Start all the toolchain servers
        foreach ($this->toolchain->getServers() as $tool) {
            $process = $tool->server();
            $process->setTimeout(PHP_INT_MAX);
            $process->start();
            $processes[$tool->getAlias()] = $process;
            $output->writeLn("<comment>{$tool->getName()}</comment> {$process->getCommandLine()}");
        }

        $lastTool = null;
        while (true) {
            sleep(1);

            foreach ($processes as $tool => $process) {
                $errorOutput = $process->getIncrementalErrorOutput();
                $standardOutput = $process->getIncrementalOutput();
                if (($errorOutput || $standardOutput) && $lastTool !== $tool) {
                    $output->writeLn("<comment>$tool</comment>");
                }
                if ($errorOutput) $output->writeLn($errorOutput, false, OutputInterface::OUTPUT_RAW);
                if ($standardOutput) $output->writeLn($standardOutput, false, OutputInterface::OUTPUT_RAW);

                if (!$process->isRunning()) break(2);
            }
        }

        foreach ($process as $process) $process->stop();
    }
}