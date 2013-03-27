<?php

namespace JJs\Bundle\ToolchainBundle\Toolchain;

/**
 * Build
 *
 * Build tools will process the set of defined inputs and produce the desired
 * outputs each time they're run. Build tools are executed on the command line
 * and must be manually invoked each time the input resources change.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
interface BuildInterface extends ToolInterface
{
    /**
     * Generates the build command as a process instance
     *
     * @return Process
     */
    function build();
}