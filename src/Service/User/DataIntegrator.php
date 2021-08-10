<?php

namespace App\Service\User;

use App\Entity\Meta;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DataIntegrator
{
    protected $em;
    protected $encoder;
    protected $vifFolder;
    protected $userFilename;

    public function __construct($vifFolder, $userFilename, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->vifFolder = $vifFolder;
        $this->userFilename = $userFilename;
    }

    public function editUsers()
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;

        try {
            $file = fopen($this->vifFolder . $this->userFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber <= 1) {
                    $header = $this->getHeader($row);
                } else {
                    $code = trim($row[$header['CODE CLIENT']]);
                    $existingUser = $this->em->getRepository(User::class)->findOneBy(['vifCode' => $code]);
                    if (is_null($existingUser))
                        $user = $this->create($row, $header, $code);
                    else 
                        $user = $this->update($row, $header, $existingUser);

                    $this->em->persist($user);
                }
                $lineNumber++;
            }
            $this->em->flush();
        } catch( \Exception $e) {
            $status = 1;
        } finally {
            fclose($file);
            return $status;
        }
    }

    private function create($row, $header, $code)
    {
        $user = new User();
        $metas = new Meta();
        $identifiant = $this->getIdentifiant($row, $header);
        $email = $this->getEmail($row, $header, $identifiant);
        $password = $this->createPassword($identifiant, $user);
        $user->setName(trim($row[$header['LIBELLE']]))
             ->setEmail($email)
             ->setVifCode($code)
             ->setRoles(["ROLE_USER"])
             ->setPassword($password);

        return $this->setMetas($metas, $user, $row, $header);
    }

    private function update($row, $header, $user)
    {
        $metas = $user->getMetas();
        $identifiant = $this->getIdentifiant($row, $header);
        $email = $this->getEmail($row, $header, $identifiant);
        $user->setName(trim($row[$header['LIBELLE']]))
             ->setEmail($email)
             ->setRoles(["ROLE_USER"]);

        return $this->setMetas($metas, $user, $row, $header);
    }

    private function setMetas($metas, $user, $row, $header)
    {
        $metas->setAddress(trim($row[$header['ADRESSE 1']]))
              ->setAddress2(trim($row[$header['ADRESSE 2']]))
              ->setZipcode(trim($row[$header['CODE POSTAL']]))
              ->setCity(trim($row[$header['VILLE']]))
              ->setUser($user);

        $this->em->persist($metas);
        return $user;
    }

    private function getIdentifiant($row, $header)
    {
        return strtolower(str_replace("_", "", trim($row[$header['CODE CLIENT']])));
    }

    private function getEmail($row, $header, $identifiant)
    {
        if (array_key_exists('EMAIL', $header) && array_key_exists($header['EMAIL'], $row) && strlen(trim($row[$header['EMAIL']])) > 0)
            return trim($row[$header['EMAIL']]);
        else
            return $identifiant . "@cap-mechant.re";
    }

    private function createPassword($identifiant, $user)
    {
        return $this->encoder->encodePassword($user, $identifiant);
    }

    private function getHeader($row)
    {
        $header = [];
        foreach ($row as $key => $value) {
            $header[$value] = $key;
        }
        return $header;
    }
}