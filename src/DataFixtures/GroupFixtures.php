<?php

namespace App\DataFixtures;

use Doctrine\ORM\Mapping\Entity;
use App\Entity\Group as EntityGroup;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class GroupFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $entityArray = [
            ["user", "ROLE_USER", true, 1, true, 0, new \Datetime(), true, false, true, false],
            ["admin", "ROLE_ADMIN", true, 1, true, 0, new \Datetime(), true, true, true, false], 
            ["super admin", "ROLE_SUPER_ADMIN", true, 1, true, 0, new \Datetime(), true, true, true, false],
            ["seller", "ROLE_SELLER", true, null, true, 0, new \Datetime(), true, true, false, false],
            ["deliverer", "ROLE_DELIVERER", true, null, true, 0, new \Datetime(), true, true, false, false],
            ["picker", "ROLE_PICKER", true, null, true, 0, new \Datetime(), true, true, false, false],
            ["relaypoint", "ROLE_RELAYPOINT", true, null, true, 0, new \Datetime(), true, true, false, false],
            ["supervisor", "ROLE_SUPERVISOR", true, null, true, 0, new \Datetime(), true, true, false, false],
        ];

        for($i = 0; $i < count($entityArray); $i ++){
            $group = new EntityGroup();
            $group->setLabel($entityArray[$i][0])
                 ->setValue($entityArray[$i][1])
                 ->setIsFixed($entityArray[$i][2])
                 ->setPriceGroup(null)
                 ->setSubjectToTaxes($entityArray[$i][4])
                 ->setDayInterval($entityArray[$i][5])
                 ->setHourLimit(new \Datetime())
                 ->setOnlinePayment($entityArray[$i][7])
                 ->setHasAdminAccess($entityArray[$i][8])
                 ->setHasShopAccess($entityArray[$i][9])
                 ->setSoldOutNotification($entityArray[$i][10]);


            $manager->persist($group);
        }

        $manager->flush();
    }
}
