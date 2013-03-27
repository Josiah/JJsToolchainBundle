<?php

namespace JJs\Bundle\ToolchainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Toolchain Configuration
 *
 * Defines the semantic configuration used in the toolchain bundle.
 * 
 * @author Josiah <josiah@jjs.id.au>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();

        $rootNode = $tree->root('toolchain');
        $rootNode
            ->children()
                ->scalarNode('working_dir')
                    ->info("Compilation working directory. All input files are written relative to this path.")
                    ->defaultValue('%kernel.root_dir%')
                ->end()
                ->scalarNode('output_dir')
                    ->info("All output files are written relative to this path.")
                    ->defaultValue('%kernel.root_dir%/../web')
                ->end()
                ->append($this->getSassConfig())
                ->append($this->getPlovrConfig())
            ->end()
        ;

        return $tree;
    }

    /**
     * Gets the sass configuration tree
     * 
     * @return NodeInterface
     */
    public function getSassConfig()
    {
        return (new TreeBuilder())->root('sass')
            ->children()
                ->scalarNode('bin')
                    ->info("Absolute path to the sass executable")
                    ->defaultValue((new ExecutableFinder())->find('sass', "/usr/bin/sass"))
                ->end()
                ->booleanNode('compass')
                    ->info("Make Compass imports available and load project configuration.")
                    ->defaultFalse()
                ->end()
                ->scalarNode('style')
                    ->info("Output style. Can be nested (default), compact, compressed, or expanded.")
                    ->validate()
                        ->ifNotInArray(['nested', 'compact', 'compressed', 'expanded'])
                        ->thenInvalid("Invalid sass output style '%s'")
                    ->end()
                ->end()
                ->integerNode('precision')
                    ->info("How many digits of precision to use when outputting decimal numbers. Defaults to 3.")
                    ->defaultValue(3)
                ->end()
                ->arrayNode('paths')
                    ->info("Additional sass import paths")
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('files')
                    ->info("Files to process with sass, add as many as required")
                    ->fixXmlConfig('file')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('input')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('output')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Gets the plover configuration tree
     * 
     * @return NodeInterface
     */
    public function getPlovrConfig()
    {
        return (new TreeBuilder())->root('plovr')
            ->children()
                ->scalarNode('bin')
                    ->info("Absolute path to the plovr executable")
                    ->defaultValue((new ExecutableFinder())->find('plovr', "/usr/bin/plovr"))
                ->end()
                ->arrayNode('builds')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('inputs')
                                ->info(
                                    "Input files to be compiled. Each input file and its transitive dependencies will\n".
                                    "be included in the compiled output.\n".
                                    "\n".
                                    " - You can define external bundles like @ExampleBundle\n".
                                    " - Absolute paths will be absolute\n".
                                    " - Relative paths will be relative to the compile path\n".
                                    "\n".
                                    "See http://goo.gl/sw08t"
                                )
                                ->example([
                                    'example.js',
                                    '@AnotherBundle/foo.js',
                                    '/absolute/path/somewhere.js'
                                ])
                                ->fixXmlConfig('input')
                                ->defaultValue(['main.js'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('paths')
                                ->info(
                                    "Files or directories where the transitive dependencies of the inputs can be found.\n".
                                    "\n".
                                    " - You can define external bundles like @ExampleBundle\n".
                                    " - Absolute paths will be absolute\n".
                                    " - Relative paths will be relative to the working directory\n".
                                    "\n".
                                    "See http://goo.gl/C3M9L"
                                )
                                ->example([
                                    'example.js',
                                    '@AnotherBundle/foo.js',
                                    '/absolute/path/somewhere.js'
                                ])
                                ->fixXmlConfig('path')
                                ->defaultValue(['.'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('externs')
                                ->info(
                                    "Files that contain externs that should be included in the compilation. By default,\n".
                                    "these will be used in addition to the default externs bundled with the Closure\n".
                                    "Compiler.\n".
                                    "\n".
                                    "There are also externs for third party libraries, such as jQuery and Google Maps,\n".
                                    "that are bundled with the Closure Compiler but are not enabled by default. These\n".
                                    "additional extern files can be seen in the Closure Compiler's contrib/externs\n".
                                    "directory. Such externs can be included with a // prefix.\n".
                                    "\n".
                                    " - You can define external bundles like @ExampleBundle\n".
                                    " - Absolute paths will be absolute\n".
                                    " - Relative paths will be relative to the compile path\n".
                                    " - Google closure includes some extern definitions by default such as jQuery\n".
                                    "\n".
                                    "See http://goo.gl/dVO0R"
                                )
                                ->example([
                                    'example.js',
                                    '@AnotherBundle/foo.js',
                                    '/absolute/path/somewhere.js',
                                    '//jquery-1.5.js'
                                ])
                                ->defaultValue([])
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('mode')
                                ->defaultValue('SIMPLE')
                                ->validate()
                                ->ifNotInArray(['RAW', 'WHITESPACE', 'SIMPLE', 'ADVANCED'])
                                    ->thenInvalid("Invalid closure compilation mode '%s'")
                                ->end()
                            ->end()
                            ->arrayNode('output')
                                ->children()
                                    ->scalarNode('file')
                                        ->info("Plovr will write the compiled output to this file. Paths are relative to the web directory.")
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('level')
                                ->defaultValue('DEFAULT')
                                ->validate()
                                ->ifNotInArray(["QUIET", "DEFAULT", "VERBOSE"])
                                    ->thenInvalid("Invalid warning level '%s'")
                                ->end()
                            ->end()
                            ->booleanNode('debug')
                                ->defaultFalse()
                            ->end()
                            ->booleanNode('pretty-print')
                                ->defaultValue("%kernel.debug%")
                            ->end()
                            ->booleanNode('print-input-delimiter')
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}