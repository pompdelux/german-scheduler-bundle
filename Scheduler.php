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

use Pompdelux\RedisBundle\Client\Redis;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class Scheduler
 * @package Pompdelux\GearmanSchedulerBundle
 */
class Scheduler
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Redis $redis
     * @param LoggerInterface $logger
     */
    public function __construct(Redis $redis, LoggerInterface $logger = null)
    {
        $this->redis  = $redis;
        $this->logger = $logger;
    }

    /**
     * Schedule a job to be executed in x seconds
     *
     * @param int $seconds
     * @param Job $job
     *
     * @return Scheduler
     */
    public function enqueueIn($seconds, Job $job)
    {
        return $this->enqueueAt(new \DateTime('+ '.(int) $seconds.' seconds'), $job);
    }

    /**
     * Schedule a job to be executed at a specific time
     *
     * @param \DateTime $timestamp
     * @param Job       $job
     *
     * @return Scheduler
     * @throws \OutOfBoundsException
     */
    public function enqueueAt(\DateTime $timestamp, Job $job)
    {
        $ts = $timestamp->getTimestamp();
        $this->redis->rPush('scheduler:job-'.$ts, $job);
        $this->redis->zAdd('scheduler:queue', $ts, $ts);

        if (!is_null($this->logger)) {
            $this->logger->debug('German scheduler, enqueue job "'.$job->getHandler().'" at '.$timestamp->format('Y-m-d H:i:s'));
        }

        return $this;
    }
}
