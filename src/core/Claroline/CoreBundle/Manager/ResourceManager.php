<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource;
use Claroline\CoreBundle\Entity\User;

class ResourceManager
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    /** @var RightManagerInterface */
    private $rightManager;

    public function __construct(EntityManager $em, RightManagerInterface $rightManager)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
    }
    
    public function createResource(Resource $resource, User $owner)
    {
        $this->em->persist($resource);
        $this->em->flush();
        $this->rightManager->addRight($resource, $owner, MaskBuilder::MASK_OWNER);
    }
}