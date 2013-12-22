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
        $output->writeln("setting acl permissions..");

        $aclProvider = $this->getContainer()->get('security.acl.provider');
         
        $anonymousRoleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
        $loggedinRoleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_REMEMBERED');
        $adminRoleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');
        
        // Settting class level permissions for Post
        $postclassIdentity = new ObjectIdentity('class', 'Cpt\\BlogBundle\\Entity\\Post');
        $aclProvider->deleteAcl($postclassIdentity);
        $aclpost = $aclProvider->createAcl($postclassIdentity);
        $aclpost->insertClassAce($anonymousRoleIdentity, MaskBuilder::MASK_VIEW);
        $aclpost->insertClassAce($loggedinRoleIdentity, MaskBuilder::MASK_COMMENT);
        $aclpost->insertClassAce($adminRoleIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($aclpost);

        // Settting class level permissions for Comment
        $commentclassIdentity = new ObjectIdentity('class', 'Cpt\\PublicationBundle\\Entity\\Comment');
        $aclProvider->deleteAcl($commentclassIdentity);
        $aclcomment = $aclProvider->createAcl($commentclassIdentity);
        $aclcomment->insertClassAce($anonymousRoleIdentity, MaskBuilder::MASK_VIEW);
        $aclcomment->insertClassAce($adminRoleIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($aclcomment);

        $output->writeln(" done!");
        
    }
}
