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

use Mmoreram\GearmanBundle\Service\GearmanClient;
use Pompdelux\RedisBundle\Client\Redis;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Consumer
 * @package Pompdelux\GearmanSchedulerBundle
 */
class Consumer
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var GearmanClient
     */
    private $gearmanClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $shutdown = false;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param Redis $redis
     * @param GearmanClient $gearmanClient
     * @param LoggerInterface $logger
     */
    public function __construct(Redis $redis, GearmanClient $gearmanClient, $logger = null)
    {
        $this->redis         = $redis;
        $this->gearmanClient = $gearmanClient;
        $this->logger        = $logger;
    }

    /**
     * Start cycle or execute single rotation
     *
     * @return bool
     */
    public function run()
    {
        $interval = $this->input->getOption('interval');

        if ($interval === 0) {
            return $this->tick();
        }

        $this->bind();
        $this->log('Gearman starting schedule consumer. Interval set to: '.$interval.' seconds.', ['interval='.$interval]);

        while ($this->tick()) {
            sleep($interval);

            // handle term signals
            pcntl_signal_dispatch();
        }
    }

    /**
     * Set output
     *
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Set input
     *
     * @param InputInterface $input
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Shutdown running process - if in demon mode.
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * Bind proc signals, so shutdowns will be executed correctly
     */
    private function bind()
    {
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT, array($this, 'shutdown'));
    }

    /**
     * Run job cycle
     *
     * @return bool
     */
    private function tick()
    {
        if ($this->shutdown) {
            return false;
        }

        $this->log('Gearman tick - process queue schedule');
        $items = $this->redis->zrangebyscore('scheduler:queue', '-inf', time(), ['limit' => [0, 1]]);

        foreach ($items as $timestamp) {
            $this->produce($timestamp);
        }

        unset ($items, $key, $timestamp);

        return true;
    }

    /**
     * Produce message and queue and send to German
     *
     * @param $timestamp
     */
    private function produce($timestamp)
    {
        $key   = 'scheduler:job-'.$timestamp;
        $count = $this->redis->lLen($key);

        if (0 == $count) {
            return;
        }

        for ($i=0; $i<$count; $i++) {
            /** @var Job $job */
            $job = $this->redis->lPop($key);
            $this->gearmanClient->doBackgroundJob($job->getHandler(), json_encode($job->getMessage()));

            $this->log('Gearman job send to queue: '.$job->getHandler());
        }

        $this->cleanup($timestamp);
    }

    /**
     * Cleanup in redis schedule queue
     *
     * @param $timestamp
     */
    private function cleanup($timestamp)
    {
        $key = 'scheduler:job-'.$timestamp;
        if (0 == $this->redis->lLen($key)) {
            $this->redis->del($key);
            $this->redis->zRem('scheduler:queue', $timestamp);

            $this->log('Gearman cleaning up scheduler:queue', [$key]);
        }
    }

    /**
     * Logger wrapper
     *
     * @param string $message Message to log
     * @param array  $context Context parameters
     * @param string $level   Defaults to "debug"
     */
    private function log($message, array $context = [], $level = 'info')
    {
        if (!$this->input->getOption('quiet')) {
            $this->output->writeln(sprintf(
                '<comment>[%s]</comment> <info>%s</info>',
                date('Y-m-d H:i:s'),
                $message
            ));
        }

        if (is_null($this->logger)) {
            return;
        }

        $this->logger->log($level, $message, $context);
    }
}
