<?php

namespace App\DataFixtures;

use App\Entity\Meta;
use Doctrine\ORM\Mapping\Entity;
use App\Entity\User as EntityUser;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{  
    /**
     * Password encoder
     *
     * @var UserPasswordEncoderInterface
     *
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $usersArray = [
            ["Brian Narbe", "brian.narbe@digitalguild.re", ["ROLE_SUPER_ADMIN"], "noutpromo**"],
            ["Sebsatien Maillot", "m_seb@icloud.com", ["ROLE_SUPER_ADMIN"], "Soleil01"],
            ["David Scheller", "david@cap-mechant.re", ["ROLE_USER"], "cap-mechant"]
        ];

        for($i = 0; $i < count($usersArray); $i ++){
            $user = new EntityUser();
            $hash = $this->encoder->encodePassword($user, $usersArray[$i][3]);
            $metas = new Meta();
            $user->setName($usersArray[$i][0])
                 ->setEmail($usersArray[$i][1])
                 ->setRoles($usersArray[$i][2])
                 ->setPassword($hash)
                 ->setMetas($metas);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
