<?php
/*
 * This file is part of Gearman scheduler bundle.
 *
 * (c) Ulrik Nielsen <un@bellcom.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pompdelux\GearmanSchedulerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Consumer
 * @package Pompdelux\GearmanSchedulerBundle
 */
class ConsumeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gearman:scheduler:consume')
            ->setDescription('Trigger consumption of scheduled gearman jobs.')
            ->addOption('interval', 'i', InputOption::VALUE_OPTIONAL, 'Set to an interval you wish to run this with. Note this effectivly makes a deamon out of the consumer. (eg. cron is not needed) Always run this in prod mode to minimize memory leaks.', 0)
        ;
    }

    /**
     * executes the job
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('quiet')) {
            $output->writeln(sprintf(
                '<comment>[%s]</comment> <info>loaded. Ctrl+C to break</info>',
                date('Y-m-d H:i:s')
            ));
        }

        $this->getContainer()->get('pdl.gearman_scheduler.consumer')
            ->setInput($input)
            ->setOutput($output)
            ->run()
        ;
    }
}
