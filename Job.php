<?php
/*
 * This file is part of Gearman scheduler bundle.
 *
 * (c) Ulrik Nielsen <un@bellcom.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pompdelux\GearmanSchedulerBundle;

/**
 * Class Job
 * @package Pompdelux\GearmanSchedulerBundle
 */
class Job
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @var mixed
     */
    private $message;

    /**
     * Constructor
     *
     * @param string $handler
     * @param mixed $message
     */
    public function __construct($handler, $message)
    {
        $this->handler = $handler;
        $this->message = $message;
    }

    /**
     * Get the GearmanBundle handler for a job
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get the message part of the job
     *
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}
