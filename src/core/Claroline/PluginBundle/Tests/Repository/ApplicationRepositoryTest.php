<?php

namespace Claroline\PluginBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Entity\Application;
use Claroline\PluginBundle\Service\ApplicationManager\Exception\ApplicationException;

class ApplicationRepositoryTest extends WebTestCase
{
    /** Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
    private $client;
    /** Doctrine\ORM\Entity */
    private $em;
    /** Claroline\PluginBundle\Repository\ApplicationRepository */
    private $appRepo;
    
    public function setUp()
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->appRepo = $this->em->getRepository('Claroline\PluginBundle\Entity\Application');
        $this->client->beginTransaction();
    }
    
    public function tearDown()
    {
        $this->client->rollback();
    }
    
    public function testGetIndexApplicationReturnsFalseIfNoIndexApplicationIsSet()
    {
        $this->assertFalse($this->appRepo->getIndexApplication());
    }
    
    public function testGetIndexApplicationThrowsAnExceptionIfMultiplesIndexApplicationsAreSet()
    {        
        $firstApp = new Application();
        $firstApp->setType('App');
        $firstApp->setBundleFQCN('VendorX\FirstAppBundle\VendorXFirstAppBundle');
        $firstApp->setVendorName('VendorX');
        $firstApp->setBundleName('FirstAppBundle');
        $firstApp->setNameTranslationKey('name_key_1');
        $firstApp->setDescriptionTranslationKey('desc_key_1');
        $firstApp->setIndexRoute('index_route_1');
        $firstApp->setIsPlatformIndex(true);
        
        $secondApp = new Application();
        $secondApp->setType('App');
        $secondApp->setBundleFQCN('VendorX\SecondAppBundle\VendorXSecondAppBundle');
        $secondApp->setVendorName('VendorX');
        $secondApp->setBundleName('SecondAppBundle');
        $secondApp->setNameTranslationKey('name_key_2');
        $secondApp->setDescriptionTranslationKey('desc_key_2');
        $secondApp->setIndexRoute('index_route_2');
        $secondApp->setIsPlatformIndex(true);
        
        $this->em->persist($firstApp);
        $this->em->persist($secondApp);      
        $this->em->flush();
        
        try
        {
            $this->appRepo->getIndexApplication();
            $this->fail('No exception thrown');
        }
        catch (ApplicationException $ex)
        {
            // Note: using the application manager to mark an application
            // as platform index prevents this situation. 
            $this->assertEquals(ApplicationException::MULTIPLES_INDEX_APPLICATIONS, $ex->getCode());
        }
    }
    
    public function testGetIndexApplicationRethrowsOthersException()
    {
        $this->setExpectedException('\Exception');
        
        $this->em->persist(new Application());
        $this->em->flush(); // should throw a PDOException (not null fields aren't set)
    }
}