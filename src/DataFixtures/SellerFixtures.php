<?php

namespace App\DataFixtures;

use App\Entity\Meta;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Seller as EntitySeller;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SellerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $entity = new EntitySeller();

        $entity->setName("Cap Méchant")
               ->setDelay(0)
               ->setRecoveryDelay(0)
               ->setOwnerRate(0)
               ->setTurnover(0)
               ->setTotalToPay(0)
               ->setTurnoverTTC(0)
               ->setTotalToPayTTC(0)
               ->setNeedsRecovery(false)
               ->setDelayInDays(true);

        $manager->persist($entity);
        $manager->flush();
    }
}
