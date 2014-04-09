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

        $manager->persist($adminuser);

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
