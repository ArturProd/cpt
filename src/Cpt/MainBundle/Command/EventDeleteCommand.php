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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class EventDeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('cpt:event:delete');
        $this->setDescription('Delete an event');
        $this->addArgument('id',InputArgument::REQUIRED,'event id');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        
        $id = $input->getArgument('id');
        
        if (($id == null)||(!is_numeric($id)))
        {
            $output->writeln("Usage cpt:event:delete id");
            exit;
        }
        
        $output->writeln("Deleting event ".$id);

        $eventmanager = $this->getContainer()->get('cpt.event.manager');
        $event = $eventmanager->getEventById($id);
        if (!$event)
        {
            $output->writeln("Event not found");
        }
        
        $eventmanager->cancelEvent($event);
        
        $output->writeln("Done.");
    }
}
