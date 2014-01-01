<?php

namespace Cpt\PublicationBundle\Manager;

use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;
/**
 * Description of PublicationManager
 *
 * @author cyril
 */
class PublicationManager extends BaseManager {

    protected function getPublicOnlyQueryPart($qb, $alias)
    {
         $qb->andWhere("$alias.publicationDateStart <= :publicationdatestart")
            ->andWhere("$alias.enabled = :enabled")
            ->andWhere("$alias.desactivated = :desactivated")
            ->setParameter('enabled', true)
            ->setParameter('desactivated', false)
            ->setParameter('publicationdatestart', new \DateTime());
    }
}
