<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\MainBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends BaseUserManager
{
    protected $objectManager;
    protected $class;
    protected $repository;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface  $usernameCanonicalizer
     * @param CanonicalizerInterface  $emailCanonicalizer
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);

        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

     public function searchUser($search_string)
    {
        // Only search strings with 3 or more carachteres are accepted
        if (strlen($search_string) <= 3)
            return Array();
            
        return $this->objectManager->getRepository($this->class)
            ->createQueryBuilder('u')
                ->Where('u.firstname LIKE :search_string OR u.lastname LIKE :search_string OR u.usernameCanonical LIKE :search_string')
                ->AndWhere('u.locked = 0 AND u.enabled = 1')
                ->orderBy('u.usernameCanonical', 'DESC')  
                ->setParameter('search_string', '%'.$search_string.'%')
            ->getQuery()
            ->getResult();
        
    }   
     /**
     * Finds a user by email
     *
     * @param string $email
     *
     * @return UserInterface
     */
    public function findUserByRole($role)
    {
        $qb = $this->objectManager->createQueryBuilder();
        $qb->select('u')
                ->from($this->class, 'u')
                ->where('u.roles LIKE :roles')
                ->setParameter('roles', '%' . $role . '%');
        return $qb->getQuery()->getResult();
    }

    
    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findUsers()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function reloadUser(UserInterface $user)
    {
        $this->objectManager->refresh($user);
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
    
    public function findNewsLetterRecipients()
    {
        $qb = $this->objectManager->createQueryBuilder();
        $qb->select('u')
                ->from($this->class, 'u')
                ->where('u.locked = :locked')
                ->AndWhere('u.expired = :expired')
                ->AndWhere('u.enabled = :enabled')
                ->AndWhere('u.option_newsletter= :option_newsletter')
                ->setParameter('locked', false)
                ->setParameter('expired', false)
                ->setParameter('enabled', true)
                ->setParameter('option_newsletter', true);
                
        return $qb->getQuery()->getResult();
    }
}
