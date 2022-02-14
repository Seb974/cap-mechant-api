<?php

namespace App\Service\User;

use App\Entity\Meta;
use App\Entity\Product;
use App\Entity\User;
use App\Service\Parser\FileParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DataIntegrator
{
    protected $em;
    protected $encoder;
    protected $vifFolder;
    protected $fileParser;
    protected $userFilename;
    protected $productHeaderLine;
    protected $userProductsFilename;

    public function __construct($vifFolder, $userFilename, $userProductsFilename, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, FileParser $fileParser)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->productHeaderLine = 1;
        $this->vifFolder = $vifFolder;
        $this->fileParser = $fileParser;
        $this->userFilename = $userFilename;
        $this->userProductsFilename = $userProductsFilename;
    }

    public function editUsers()
    {
        ini_set('mbstring.substitute_character', "none");
        $status = 0;
        $header = [];
        $lineNumber = 1;
        $users = [];
        try {
            $this->fileParser->parse($this->vifFolder . $this->userFilename);
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

                    $users[] = $user;
                    $this->em->persist($user);
                }
                $lineNumber++;
            }
            $this->editProducts($users);
        } catch( \Exception $e) {
            $status = 1;
            dd($e);
        } finally {
            $this->em->flush();
            fclose($file);
            return $status;
        }
    }

    private function editProducts($editedUsers)
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;
        $users = $this->getResettedUsers($editedUsers);

        try {
            $this->fileParser->parse($this->vifFolder . $this->userProductsFilename);
            $file = fopen($this->vifFolder . $this->userProductsFilename, 'r');
            while(($row = fgetcsv($file, 0, ",")) !== false)
            {
                if ($lineNumber == $this->productHeaderLine) {
                    
                    $header = $this->getHeader($row);
                    
                } else if ($lineNumber > $this->productHeaderLine) {
                    $userCode = trim($row[$header['ctie']]);
                    $productCode = trim($row[$header['cart']]);
                    $user = $this->getConcernedUser($userCode, $users);
                    
                    if (!is_null($user)) {
                        $product = $this->em->getRepository(Product::class)->findOneBy(['sku' => $productCode]);
                        if (!is_null($product))
                            $product->addUser($user);
                    }
                }
                $lineNumber++;
            }

        } catch( \Exception $e) {
            $status = 1;
            dump($e->getMessage());
        } finally {
            fclose($file);
            return $status;
        }
    }

    private function getResettedUsers($editedUsers)
    {
        foreach ($editedUsers as $user) {
            $products = $user->getProducts();
            foreach ($products as $product) {
                $product->removeUser($user);
            }
        }
        return $editedUsers;
    }

    private function getConcernedUser($code, $users)
    {
        foreach ($users as $user) {
            if ($user->getVifCode() == $code)
                return $user;
        }
        return null;
    }

    private function create($row, $header, $code)
    {
        $user = new User();
        $metas = new Meta();
        $identifiant = $this->getIdentifiant($row, $header);
        $email = $this->getEmail($row, $header, $identifiant);
        $isIntern = $this->getIsIntern(trim($row[$header['FAMILLE']]));
        $password = $this->createPassword($identifiant, $user);
        $user->setName(trim($row[$header['LIBELLE']]))
             ->setEmail($email)
             ->setVifCode($code)
             ->setRoles(["ROLE_USER"])
             ->setPassword($password)
             ->setIsIntern($isIntern);

        return $this->setMetas($metas, $user, $row, $header);
    }

    private function update($row, $header, $user)
    {
        $metas = $user->getMetas();
        $identifiant = $this->getIdentifiant($row, $header);
        $email = $this->getEmail($row, $header, $identifiant);
        $isIntern = $this->getIsIntern(trim($row[$header['FAMILLE']]));
        $user->setName(trim($row[$header['LIBELLE']]))
             ->setEmail($email)
             ->setRoles(["ROLE_USER"])
             ->setIsIntern($isIntern);

        return $this->setMetas($metas, $user, $row, $header);
    }

    private function getIsIntern($family)
    {
        return !is_null($family) && $family == "INT";
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