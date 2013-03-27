<?php

namespace JJs\Bundle\ToolchainBundle\Toolchain;

/**
 * Tool
 *
 * A tool is a component which can be used a part of the toolchain. A tool by
 * itself isn't useful and must provide more specific execution information in
 * order to be used in the toolchain.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
interface ToolInterface
{
    /**
     * Gets the alias used to identify this tool in the toolchain
     * 
     * @return string
     */
    function getAlias();

    /**
     * Gets the name used to refer to this tool in the toolchain
     * 
     * @return string
     */
    function getName();
}