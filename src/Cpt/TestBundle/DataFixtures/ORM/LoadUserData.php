<?php
namespace Cpt\TestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\Sonata\UserBundle\Entity\User as User;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    { 
        $adminuser = $this->CreateUser('admin', 'admin@cpt.com', 'wxcvbn', true);
        $user1 = $this->CreateUser('user1', 'user1@cpt.com', 'wxcvbn');
        $user2 = $this->CreateUser('user2', 'user2@cpt.com', 'wxcvbn');
        $user3 = $this->CreateUser('user3', 'user3@cpt.com', 'wxcvbn');

        $manager->persist($adminuser);
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);

        $manager->flush();
        
    }
    
    protected function CreateUser($username, $email, $password, $admin=false)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);

        if ($admin)
            $user->setRoles (array(User::ROLE_SUPER_ADMIN));
        
        return $user;
    }
}
?>
