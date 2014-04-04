<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class EventDumpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('cpt:event:dump');
        $this->setDescription('Dump all events');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Dumping all events:");

        $eventmanager = $this->getContainer()->get('cpt.event.manager');
        $events  = $eventmanager->getAllEvents();
        
        $output->writeln("ID"."\t"."AUTHOR"."\t"."COUNTRY"."\t"."TOT"."\t"."QUEUED"."\t"."TITLE");
                
        foreach($events as $event)
        {
            $output->writeln($event->getId()."\t".$event->getAuthor()."\t".$event->getCountryCode()."\t".$event->getCountTotalAttendees()."\t".$event->getCountQueuedAttendees()."\t".$event->getTitle());
        }
        
        $output->writeln("Done.");
    }
}
