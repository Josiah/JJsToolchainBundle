<?php

namespace JJs\Bundle\ToolchainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Toolchain Extension
 *
 * Symfony2 kernel extension which integrates configuration artifacts from your
 * symfony2 application into your javascript toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class JJsToolchainExtension extends Extension
{
    /**
     * Container Extension Alias
     *
     * @var string
     */
    const Alias = "toolchain";

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $config, ContainerBuilder $container)
    {
        // Resolve the config
        $config = (new Processor())->processConfiguration(new Configuration(), $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load("toolchain.xml");

        // Load the plovr configuration
        if (array_key_exists('plovr', $config)) {
            $loader->load('plovr.xml');
            $this->loadPlovrConfig($config['plovr'], $container);
        }

        // Load the sass configuration
        if (array_key_exists('sass', $config)) {
            $loader->load('sass.xml');
            $this->loadSassConfig($config['sass'], $container);
        }
    }

    /**
     * Loads the sass configuration
     * 
     * @param array            $config    Sass configuration
     * @param ContainerBuilder $container Container builder
     */
    public function loadSassConfig(array $config, ContainerBuilder $container)
    {
        $sass = $container->getDefinition('toolchain.sass.build_configuration');

        // Map values from the configuration into the service container
        $container->setParameter("toolchain.sass.bin", $config['bin']);

        // Map values from the configuration to method calls
        $sass->addMethodCall('setCompassEnabled', [$config['compass']]);

        // Add the input and output files
        foreach ($config['files'] as $file) {
            $sass->addMethodCall('addFile', [$file['input'], $file['output']]);
        }

        // Add the load paths
        foreach ($config['paths'] as $path) {
            $sass->addMethodCall('addLoadPath', [$path]);
        }
    }

    /**
     * Loads the plovr configuration
     * 
     * @param array            $config    Plovr configuration
     * @param ContainerBuilder $container Container builder
     */
    public function loadPlovrConfig(array $config, ContainerBuilder $container)
    {
        // Map values from the configuration into the service container
        $container->setParameter("toolchain.plovr.bin", $config['bin']);

        // Configure each bundle in turn
        if (array_key_exists('builds', $config)) {
            foreach ($config['builds'] as $id => $options) {
                $options['id'] = $id;
                $this->loadBuildConfig($options, $container);
            }
        }
    }

    /**
     * Loads the plovr build configuration
     * 
     * @param array            $config    Build configuration
     * @param ContainerBuilder $container Service container
     */
    public function loadBuildConfig(array $config, ContainerBuilder $container)
    {
        // Transform the build configuration
        foreach (['output'] as $prefix) {
            if (!array_key_exists($prefix, $config)) continue;

            foreach ($config[$prefix] as $key => $value) {
                $config["{$prefix}-{$key}"] = $value;
            }

            unset($config[$prefix]);
        }

        $compiler = $container->getDefinition('toolchain.plovr.build_manager');
        $compiler->addMethodCall('addBuild', [$config['id'], $config]);
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return static::Alias;
    }
}