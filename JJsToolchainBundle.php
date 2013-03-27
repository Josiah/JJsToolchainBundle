<?php

namespace JJs\Bundle\ToolchainBundle;

use JJs\Bundle\ToolchainBundle\Command\ToolBuildCommand;
use JJs\Bundle\ToolchainBundle\Command\ToolchainBuildCommand;
use JJs\Bundle\ToolchainBundle\Command\ToolchainServerCommand;
use JJs\Bundle\ToolchainBundle\Command\ToolServerCommand;
use JJs\Bundle\ToolchainBundle\DependencyInjection\Compiler\ToolCompilerPass;
use JJs\Bundle\ToolchainBundle\DependencyInjection\JJsToolchainExtension;
use JJs\Bundle\ToolchainBundle\Toolchain\BuildInterface;
use JJs\Bundle\ToolchainBundle\Toolchain\ServerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Toolchain Bundle
 *
 * Integrates the features of non-symfony development tools into symfony as
 * toolchains. Rather than forcing toolchains into the symfony environment this
 * bundle exposes confguration from a symfony application to the tools and you
 * use and lets you keep the features they normally provide (such as watching
 * files for changes).
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class JJsToolchainBundle extends Bundle
{
    /**
     * Returns the container extension that should be implicitly loaded.
     *
     * @return ExtensionInterface|null The default extension or null if there is none
     */
    public function getContainerExtension()
    {
        return new JJsToolchainExtension();
    }

    /**
     * Adds the tool compiler pass to the container builder
     * 
     * @param ContainerBuilder $container Container builder
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ToolCompilerPass());
    }

    /**
     * Registers the toolchain console commands
     * 
     * @param Application $console Console application
     */
    public function registerCommands(Application $console)
    {
        $toolchain = $this->container->get('toolchain');

        // Don't add commands if there aren't any tools registered in the
        // toolchain
        if (!$toolchain->hasTools()) return;

        // Create the aggregate toolchain commands
        if ($toolchain->hasServers()) $console->add(new ToolchainBuildCommand($toolchain));
        if ($toolchain->hasTools())   $console->add(new ToolchainServerCommand($toolchain));

        // Create the individual tool commands
        foreach ($toolchain->getTools() as $tool) {
            if ($tool instanceof BuildInterface)  $console->add(new ToolBuildCommand($tool));
            if ($tool instanceof ServerInterface) $console->add(new ToolServerCommand($tool));
        }
    }
}