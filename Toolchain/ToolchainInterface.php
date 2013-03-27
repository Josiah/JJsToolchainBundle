<?php

namespace JJs\Bundle\ToolchainBundle\Toolchain;

/**
 * Toolchain Interface
 *
 * @author Josiah <josiah@jjs.id.au>
 */
interface ToolchainInterface
{
    /**
     * Indicates whether there are tools in this toolchain
     * 
     * @return boolean
     */
    function hasTools();

    /**
     * Gets the tools in the toolchain
     * 
     * @return Traversable
     */
    function getTools();

    /**
     * Indicates whether there are builds in this toolchain
     * 
     * @return boolean
     */
    function hasBuilds();

    /**
     * Gets the builds in the toolchain
     * 
     * @return Traversable
     */
    function getBuilds();

    /**
     * Indicates whether there are servers in this toolchain
     * 
     * @return boolean
     */
    function hasServers();

    /**
     * Gets the servers in the toolchain
     * 
     * @return Traversable
     */
    function getServers();
}