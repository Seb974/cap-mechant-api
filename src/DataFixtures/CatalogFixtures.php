<?php

namespace App\DataFixtures;

use Doctrine\ORM\Mapping\Entity;
use App\Entity\Catalog as EntityCatalog;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $entityArray = [
            ["Réunion","RE", false, [-21.065285, 55.48027], -20.871965, -21.389627, 55.216556, 55.83694]
        ];

        for($i = 0; $i < count($entityArray); $i ++){
             $entity = new EntityCatalog();
             $entity->setName($entityArray[$i][0])
                    ->setCode($entityArray[$i][1])
                    ->setNeedsParcel($entityArray[$i][2])
                    ->setCenter($entityArray[$i][3])
                    ->setMinLat($entityArray[$i][4])
                    ->setMaxLat($entityArray[$i][5])
                    ->setMinLng($entityArray[$i][6])
                    ->setMaxLng($entityArray[$i][7])
                    ->setZoom(9)
                    ->setIsDefault(true);

             $manager->persist($entity);
        }
        $manager->flush();
    }
}
