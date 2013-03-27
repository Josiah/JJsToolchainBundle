<?php

namespace JJs\Bundle\ToolchainBundle\Plovr;

use JJs\Bundle\ToolchainBundle\Toolchain\BuildInterface;
use JJs\Bundle\ToolchainBundle\Toolchain\ServerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Plovr Build Manager
 *
 * Manages a series of build configurations which plovr will be responsible for
 * building when compilation is required.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class BuildManager implements BuildInterface, ServerInterface
{
    /**
     * Absolute path to the plovr executable
     * 
     * @var string
     */
    protected $bin;

    /**
     * Application Kernel
     * 
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Build configurations
     * 
     * @var array
     */
    protected $builds = [];

    /**
     * Working directory
     * 
     * @var string
     */
    protected $workingDirectory;

    /**
     * Temporary Files
     *
     * Identifies the temporary files which have been created by this build
     * manager and must be removed when it is destroyed.
     * 
     * @var array
     */
    protected $temporaryFiles = [];

    /**
     * @param KernelInterface          $kernel   Symfony application kernel
     * @param string                   $bin      Plovr executable path
     * @param OptionsResolverInterface $resolver Options resolver
     */
    public function __construct(KernelInterface $kernel, $bin, OptionsResolverInterface $resolver = null)
    {
        $this->kernel = $kernel;
        $this->bin    = $bin;
        $this->resolver = $resolver ?: $this->createBuildOptionsResolver();
        $this->workingDirectory = $kernel->getRootDir();
    }

    /**
     * Removes the temporary files created by this build manager instance
     */
    public function __destruct()
    {
        foreach ($this->temporaryFiles as $file)  unlink($file);
    }

    public function getAlias()
    {
        return 'plovr';
    }

    public function getName()
    {
        return 'plovr';
    }

    /**
     * Creates the default build options resolver
     * 
     * @return OptionsResolverInterface
     */
    protected function createBuildOptionsResolver()
    {
        return (new OptionsResolver)
            ->setRequired(['id', 'inputs'])
            ->setDefaults([
                'mode'                  => "SIMPLE",
                'level'                 => "DEFAULT",
                'debug'                 => false,
                'pretty-print'          => $this->kernel->isDebug(),
                'print-input-delimiter' => false,
            ])
            ->setOptional([
                'paths',
                'inputs',
                'externs',
                'output-file',
            ])
            ->setNormalizers([
                'paths'   => function (Options $options, array $paths) { return $this->resolvePaths($paths); },
                'inputs'  => function (Options $options, array $paths) { return $this->resolvePaths($paths); },
                'externs' => function (Options $options, array $paths) { return $this->resolvePaths($paths); },
                'output-file' => function (Options $options, $path) {
                    return $this->resolvePath($path);
                }
            ])
        ;
    }

    /**
     * Adds a plovr build configuration
     *
     * @param string $id      Identifier
     * @param array  $options Options
     */
    public function addBuild($id, array $options)
    {
        $this->builds[$id] = $options;

        return $this;
    }

    /**
     * Gets all build otpions as resolved by the build options resolver
     * 
     * @return array
     */
    public function getBuildOptions()
    {
        return array_map([$this->resolver, 'resolve'], $this->builds);
    }

    /**
     * Asserts that a build configuration with the specified id exists
     * 
     * @param array $id Build identifier
     * @throws RuntimeException If the no build configuration exists matching
     *         the identifier.
     */
    public function assertBuildConfigurationExists($id)
    {
        if (!array_key_exists($id, $this->builds)) {
            throw new \RuntimeException("No plovr build configuration registered under id '{$id}'.");
        }
    }

    /**
     * Gets the specified build configuration
     * 
     * @param string $id Build identifier
     * @return array
     */
    public function getBuildConfiguration($id)
    {
        $this->assertBuildConfigurationExists($id);

        return $this->resolver->resolve($this->builds[$id]);
    }

    /**
     * Resolves kernel paths in an array of paths
     * 
     * @param array $paths Paths for resolution
     * @return array
     */
    protected function resolvePaths(array $paths)
    {
        return array_map([$this, 'resolvePath'], $paths);
    }

    /**
     * Resolves a kernel path
     * 
     * @param string $path Path for resolution
     * @return array
     */
    protected function resolvePath($path)
    {
        // Resolve the path, or pass it through
        if (substr($path, 0, 1) === '@') {
            return $this->kernel->locateResource($path);
        } else {
            return $path;
        }
    }

    /**
     * Creates a plovr process to build or serve a series of configuration files
     *
     * @param string $command Indicates whether to create a build or serve command
     * @return Process
     */
    private function createProcess($command)
    {
        // Generate the standard build command
        $builder = (new ProcessBuilder)
            ->add($this->bin)
            ->add($command)
            ->setWorkingDirectory($this->workingDirectory)
        ;

        // Generate temporary files with the individual build options
        foreach ($this->getBuildOptions() as $id => $options) {
            $file = tempnam($this->workingDirectory, "plovr_build_{$id}_");
            file_put_contents($file, json_encode($options));
            $builder->add($file);
            $this->temporaryFiles[] = $file;
        }

        return $builder->getProcess();
    }

    /**
     * Generates a build process
     *
     * The build process can be run to enact a plovr build of all registered 
     * builds.
     * 
     * @return Process
     */
    public function build()
    {
        return $this->createProcess('build');
    }

    /**
     * Generates a server process
     *
     * This server process can be used to serve a plovr build of all registered
     * bulids.
     *
     * @return Process
     */
    public function server()
    {
        return $this->createProcess("serve");
    }
}