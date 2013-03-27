<?php

namespace JJs\Bundle\ToolchainBundle\Command;

use JJs\Bundle\ToolchainBundle\Toolchain\ServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Tool Server Command
 *
 * Runs the server provided by a tool from the toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class ToolServerCommand extends Command
{
    /**
     * Server tool
     * 
     * @var ServerInterface
     */
    protected $server;

    /**
     * @param ServerInterface $build Tool to build with
     */
    public function __construct(ServerInterface $server)
    {
        $this->server = $server;

        parent::__construct("toolchain:server:".$server->getAlias());
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setDescription("Serves resources in the application using {$this->server->getName()}")
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
         $process = $this->server->server();
         $process->setTimeout(PHP_INT_MAX);
         $process->run(function ($type, $text) use ($output) { $output->write($text, false, OutputInterface::OUTPUT_RAW); });
    }
}