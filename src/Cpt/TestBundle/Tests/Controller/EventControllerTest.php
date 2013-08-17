<?php
namespace Cpt\TestBundle\Tests\Controller;

use Cpt\TestBundle\Lib\BaseWebTestCase as BaseWebTestCase;

class EventControllerTest extends BaseWebTestCase
{
    public function testIndex()
    {
        $this->LoginUser("user1");
        
        
        $this->client->followRedirects();
        
   //     $client = static::createClient();

   //     $crawler = $client->request('GET', '/demo/hello/Fabien');

   //     $this->assertGreaterThan(0, $crawler->filter('html:contains("Hello Fabien")')->count());
    }
    
    protected function Login()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/home');
        $form = $crawler->selectButton('_submit')->form();
        
        $form['username'] = 'admin';
        $form['password'] = 'wxcvbn';
        
        $crawler = $client->submit($form);
    }
}
?>
