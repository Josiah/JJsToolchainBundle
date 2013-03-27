<?php

namespace JJs\Bundle\ToolchainBundle\Toolchain;

use SplPriorityQueue;

/**
 * Toolchain
 *
 * @author Josiah <josiah@jjs.id.au>
 */
class Toolchain implements ToolchainInterface
{
    /**
     * Tools
     *
     * Proiritized set of tools in this toolchain
     * 
     * @var SplPriorityQueue
     */
    protected $tools;

    /**
     * Builds
     *
     * Proiritized set of builds in this toolchain
     * 
     * @var SplPriorityQueue
     */
    protected $builds;

    /**
     * Servers
     *
     * Proiritized set of servers in this toolchain
     * 
     * @var SplPriorityQueue
     */
    protected $servers;

    /** {@inheritdoc} */
    public function __construct()
    {
        $this->tools   = new SplPriorityQueue();
        $this->builds  = new SplPriorityQueue();
        $this->servers = new SplPriorityQueue();
    }

    /**
     * Adds a tool to this toolchain
     * 
     * @param ToolInterface $tool     Tool
     * @param integer       $priority Priority
     */
    public function addTool(ToolInterface $tool, $priority = 0)
    {
        $this->tools->insert($tool, $priority);

        if ($tool instanceof BuildInterface) $this->builds->insert($tool, $priority);
        if ($tool instanceof ServerInterface) $this->servers->insert($tool, $priority);

        return $this;
    }

    /**
     * Indicates whether there are tools in this toolchain
     * 
     * @return boolean
     */
    public function hasTools()
    {
        return !$this->tools->isEmpty();
    }

    /**
     * Gets the tools in the toolchain
     * 
     * @return Traversable
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * Indicates whether there are builds in this toolchain
     * 
     * @return boolean
     */
    public function hasBuilds()
    {
        return !$this->builds->isEmpty();
    }

    /**
     * Gets the builds in the toolchain
     * 
     * @return Traversable
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * Indicates whether there are servers in this toolchain
     * 
     * @return boolean
     */
    public function hasServers()
    {
        return !$this->servers->isEmpty();
    }

    /**
     * Gets the servers in the toolchain
     * 
     * @return Traversable
     */
    public function getServers()
    {
        return $this->servers;
    }
}