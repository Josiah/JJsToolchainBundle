<?php

namespace JJs\Bundle\ToolchainBundle\Twig;

use Twig_Extension as Extension;
use Twig_SimpleFunction  as SimpleFunction;
use JJs\Bundle\ToolchainBundle\Plovr\BuildManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Plovr Twig Extension
 *
 * Twig extension which makes it easy to work with plovr builds in templates.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class PlovrTwigExtension extends Extension
{
    /**
     * Plovr Twig Extension
     *
     * @var string
     */
    const Name = "plovr";

    /**
     * Build manager
     * 
     * @var BuildManager
     */
    protected $buildManager;

    /**
     * @param BuildManager $buildManager Build manager
     */
    public function __construct(BuildManager $buildManager)
    {
        $this->buildManager = $buildManager;
    }

    /**
     * Gets the name assigned to the plovr twig extension
     * 
     * @return string
     */
    public function getName()
    {
        return self::Name;
    }

    /**
     * Gets the twig functions supported by this extension
     * 
     * @return array
     */
    public function getFunctions()
    {
        return [
            new SimpleFunction('plovr_build', function ($id, array $params = array()) { return $this->getBuildUrl($id, $params); }),
        ];
    }

    /**
     * Gets the url which references the specified build
     *
     * The precise url returned depends on the kernel environment and whether
     * the plovr server is currently running.
     * 
     * @param string $id     Build identifier
     * @param array  $params Url parameter array
     * @return string url
     */
    protected function getBuildUrl($id, array $params = array())
    {
        $buildManager = $this->buildManager;
        $build = $buildManager->getBuildConfiguration($id);

        // Determine the web url for the build
        $outputPath = $build['output-file'];

        // Get just the url after the web path
        $url = $outputPath;

        // Return the url
        return $url;
    }
}