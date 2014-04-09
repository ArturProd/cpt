<?php
namespace Cpt\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cpt\EventBundle\Entity\Country;

class LoadCountryData implements FixtureInterface, ContainerAwareInterface
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
        $row = 1;
        if (($handle = fopen(__DIR__ . "/../countries.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $country = new Country;
                $country->setCountryCode($data[0]);
                $country->setCountryName($data[1]);
                $manager->persist($country);
            }
            fclose($handle);
        }
        
        $manager->flush();
    }
}