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

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Cpt\MainBundle\Security\MaskBuilder as MaskBuilder;
use Cpt\MainBundle\Security\PermissionMap as PermissionMap;

class InitializeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('cpt:initialize');
        $this->setDescription('Initialize Cpt application');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        InitACL($input,$output);
        LoadFixtures($input,$output);
        
    }
    
    protected function LoadFixtures(InputInterface $input, OutputInterface $output)
    {
            $output->write("Loading fixtures..");

            $command = $this->getApplication()->find('doctrine:fixtures:load');
            
            $arguments = array(
                '--force' => true                
            );
            
            $input = new ArrayInput($arguments);
            
            $returnCode = $command->run($input, $output);
            
            if($returnCode == 0) {
                $output->writeln("OK");
            } else {
                $output->writeln("FAILED return code=".$returnCode);
            }
    }
    
    protected function InitACL(InputInterface $input, OutputInterface $output)
    {
        $output->write("setting acl permissions..");

        $aclProvider = $this->getContainer()->get('security.acl.provider');
         
        $anonymousRoleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
        $loggedinRoleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_REMEMBERED');
        $adminRoleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');
        
        // Settting class level permissions for Publication
        $publicationclassIdentity = $this->getContainer()->get('cpt.permission.manager')->getPublicationClassIdentity();
        // $postclassIdentity = new ObjectIdentity('class', 'Cpt\\PublicationBundle\\Entity\\Publication');
        $aclProvider->deleteAcl($publicationclassIdentity);
        $aclpublication = $aclProvider->createAcl($publicationclassIdentity);
        $aclpublication->insertClassAce($anonymousRoleIdentity, MaskBuilder::MASK_VIEW);
        
        
        $builder = new MaskBuilder();
        $builder
            ->add('VIEW')
            ->add('CREATE')
            ->add('COMMENT');
        $mask = $builder->get();
        $aclpublication->insertClassAce($loggedinRoleIdentity, $mask);
        $aclpublication->insertClassAce($adminRoleIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($aclpublication);

        // Settting class level permissions for Comment
        $commentclassIdentity = $this->getContainer()->get('cpt.permission.manager')->getCommentClassIdentity();
        $aclProvider->deleteAcl($commentclassIdentity);
        $aclcomment = $aclProvider->createAcl($commentclassIdentity);
        $aclcomment->insertClassAce($anonymousRoleIdentity, MaskBuilder::MASK_VIEW);
        $builder
            ->add('VIEW') 
            ->add('CREATE'); // Logged in user can CREATE comment class. However not EDIT unless it is its own comment!
        $mask = $builder->get();
        $aclcomment->insertClassAce($loggedinRoleIdentity, $mask);
        $aclcomment->insertClassAce($adminRoleIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($aclcomment);

        $output->writeln("OK");
    }
}
