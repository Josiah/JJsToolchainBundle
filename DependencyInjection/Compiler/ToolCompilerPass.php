<?php

namespace JJs\Bundle\ToolchainBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Tool Compiler Pass
 *
 * Picks up services which have been tagged as toolchain tools and adds them to
 * the toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class ToolCompilerPass implements CompilerPassInterface
{
    /**
     * Processes the toolchain tools which have been tagged in the service
     * container.
     * 
     * @param ContainerBuilder $container Container builder
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('toolchain')) return;

        $toolchain = $container->getDefinition('toolchain');

        // Iterate over the tagged tools
        foreach ($container->findTaggedServiceIds('toolchain.tool') as $id => $tags) {
            foreach ($tags as $tag) {
                $params = [new Reference($id)];
                if (array_key_exists('priority', $tag)) $params[] = (int) $tag['priority'];

                $toolchain->addMethodCall('addTool', $params);
            }
        }
    }
}