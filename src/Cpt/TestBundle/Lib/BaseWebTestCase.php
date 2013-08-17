<?php
 
namespace Cpt\TestBundle\Lib;
 
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Application\Sonata\UserBundle\Entity\User as User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken as UsernamePasswordToken;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Cpt\TestBundle\DataFixtures\ORM\LoadUserData as LoadUserData;
 
abstract class BaseWebTestCase extends WebTestCase
{
 
    protected static $em;
    protected static $client;
    protected static $application;
     
    protected static $isFirstTest = true;
 
    
    /**
     * Prepare each test
     */
    public function setUp()
    {
        parent::setUp();
 
        static::$client = static::createClient();
 
        if (!$this->useCachedDatabase()) {
            $this->databaseInit();
            $this->loadFixtures();  
        }
    }
 
    protected function LoginUser($username)
    {
        
        $container = static::$kernel->getContainer();
        
        $providerKey = $container->getParameter('fos_user.firewall_name');
        $securityContext = $container->get('security.context');
        $userProvider = $container->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, $providerKey, array('ROLE_USER'));
        $securityContext->setToken($token);

        $this->assertTrue($container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'), "User ".$username." could not be authenticated. Are user fixtures loaded?");

        return $user;
    }
    /**
     * Initialize database
     */
    protected function databaseInit()
    {
        static::$em = static::$kernel
            ->getContainer()
            ->get('doctrine.orm.entity_manager');
 
        static::$application = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
         
        static::$application->setAutoExit(false);
        $this->runConsole("bin/doctrine:schema:drop", array("--force" => true));
        $this->runConsole("bin/doctrine:schema:create");
        $this->runConsole("cache:warmup");
    }
 
    /**
     * Load tests fixtures
     */
    protected function loadFixtures()
    {
         $this->runConsole("doctrine:fixtures:load");
//        $loader = new Loader();
//        $loader->addFixture( new LoadUserData() );
//        $purger     = new ORMPurger();
//        $executor   = new ORMExecutor( static::$em, $purger );
//        $executor->execute( $loader->getFixtures() );
    }
     
    /**
     * Use cached database for testing or return false if not
     */
    protected function useCachedDatabase()
    {
        $container = static::$kernel->getContainer();
        $registry = $container->get('doctrine');
        $om = $registry->getManager();
        $connection = $om->getConnection();
         
        if ($connection->getDriver() instanceOf SqliteDriver) {
            $params = $connection->getParams();
            $name = isset($params['path']) ? $params['path'] : $params['dbname'];
            $filename = pathinfo($name, PATHINFO_BASENAME);
            $backup = $container->getParameter('kernel.cache_dir') . '/'.$filename;
 
            // The first time we won't use the cached version
            if (self::$isFirstTest) {
                self::$isFirstTest = false;
                return false;
            }
             
            self::$isFirstTest = false;
 
            // Regenerate not-existing database
            if (!file_exists($name)) {
                @unlink($backup);
                return false;
            }
 
            $om->flush();
            $om->clear();
             
            // Copy backup to database
            if (!file_exists($backup)) {
                copy($name, $backup);
            }
 
            copy($backup, $name);
            return true;
        }
         
        return false;
    }
 
    /**
     * Executes a console command
     *
     * @param type $command
     * @param array $options
     * @return type integer
     */
    protected function runConsole($command, Array $options = array())
    {
        $options["--env"] = "test";
     //   $options["--quiet"] = null;
        $options["--no-interaction"] = null;
        $options = array_merge($options, array('command' => $command));
        return static::$application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }
}