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
 * Toolchain Build Command
 *
 * Runs every build in the toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class ToolchainBuildCommand extends Command
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

        parent::__construct("toolchain:build");
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setDescription("Runs all builds in the toolchain")
        ;
    }

    /**
     * Builds using all tools in the toolchain
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->toolchain->getBuilds() as $tool) {
            $process = $tool->build();
            $process->setTimeout(PHP_INT_MAX);
            $process->run(function ($type, $text) use ($tool, $output) {
                $output->write($text, true, OutputInterface::OUTPUT_RAW);
            });

            if (!$process->isSuccessful()) {
                $output->writeLn(sprintf("<error>%s</error>", $process->getCommandLine()));
            }
        }
    }
}