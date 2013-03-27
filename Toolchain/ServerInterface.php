<?php

namespace JJs\Bundle\ToolchainBundle\Toolchain;

/**
 * Server
 *
 * Server tools will process the set of defined inputs and produce the desired
 * outputs continuiously. This is achieved by either running a server which
 * produces the results or by watching the input files for changes. Servers are
 * maintained by long running commands.
 *
 * @author Josiah <josiah@jjs.id.au>
 */
interface ServerInterface extends ToolInterface
{
    /**
     * Generates the server command as a process instance
     *
     * @return Process
     */
    function server();
}