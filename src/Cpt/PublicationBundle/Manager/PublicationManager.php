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
    
    /**
     * @param string $date  Date in format YYYY-MM-DD
     * @param string $step  Interval step: year|month|day
     * @param string $alias Table alias for the publicationDateStart column
     *
     * @return array
     */
    protected function getPublicationDateQueryParts($date, $step, $alias = 'p') {
        return array(
            'query' => sprintf('%s.publicationDateStart >= :startDate AND %s.publicationDateStart < :endDate', $alias, $alias),
            'params' => array(
                'startDate' => new \DateTime($date),
                'endDate' => new \DateTime($date . '+1 ' . $step)
            )
        );
    }
    
}
