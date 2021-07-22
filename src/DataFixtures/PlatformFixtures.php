<?php

namespace App\DataFixtures;

use App\Entity\Meta;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Platform as EntityPlatform;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PlatformFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $entity = new EntityPlatform();
        $metas = new Meta();

        $metas->setAddress("119 Allée de Montaignac, L'Étang-Salé, Réunion")
            ->setAddress2("")
            ->setZipcode("97427")
            ->setCity("L'Étang-Salé")
            ->setPosition([-21.264798, 55.362360])
            ->setPhone("0262345858")
            ->setIsRelaypoint(false);

        $manager->persist($metas);

        $entity->setName("Cap Méchant")
               ->setMetas($metas);

        $manager->persist($entity);

        $manager->flush();
    }
}
