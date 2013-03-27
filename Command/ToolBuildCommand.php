<?php

namespace JJs\Bundle\ToolchainBundle\Command;

use JJs\Bundle\ToolchainBundle\Toolchain\BuildInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Tool Build Command
 *
 * Executes an individual build and outputs the results.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class ToolBuildCommand extends Command
{
    /**
     * Build tool
     * 
     * @var BuildInterface
     */
    protected $build;

    /**
     * @param BuildInterface $build Tool to build with
     */
    public function __construct(BuildInterface $build)
    {
        $this->build = $build;

        parent::__construct("toolchain:build:".$build->getAlias());
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setDescription("Builds resources in the application using {$this->build->getName()}")
        ;
    }

    /**
     * Executes the build command
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
        $process = $this->build->build();
        $process->run(function ($type, $text) use ($output) { $output->write($text); });
        if (!$process->isSuccessful()) {
            $output->writeLn(sprintf("<error>%s</error>", $process->getCommandLine()));
        }
    }
}