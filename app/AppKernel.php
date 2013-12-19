<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Cpt\EventBundle\CptEventBundle(),
            new Cpt\MainBundle\CptMainBundle(),
            new Cpt\PublicationBundle\CptPublicationBundle(),
            new Cpt\TestBundle\CptTestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Application\FOS\UserBundle\ApplicationFOSUserBundle(),
            new Sonata\UserBundle\SonataUserBundle('ApplicationFOSUserBundle'),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\FormatterBundle\SonataFormatterBundle(),
            new Sonata\MarkItUpBundle\SonataMarkItUpBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
            new Sonata\MediaBundle\SonataMediaBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new Application\Ivory\CKEditorBundle\ApplicationIvoryCKEditorBundle(),
            new Cpt\BlogBundle\CptBlogBundle(),
            new Application\Sonata\UserBundle\ApplicationSonataUserBundle(),
            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            new Thrace\FormBundle\ThraceFormBundle(),
            new Ivory\GoogleMapBundle\IvoryGoogleMapBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FrequenceWeb\Bundle\CalendRBundle\FrequenceWebCalendRBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
       );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
