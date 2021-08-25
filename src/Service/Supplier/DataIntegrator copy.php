<?php

namespace App\Service\Supplier;

use App\Entity\Supplier;
use App\Repository\SellerRepository;
use Doctrine\ORM\EntityManagerInterface;

class DataIntegrator
{
    protected $em;
    protected $vifFolder;
    protected $supplierFilename;
    protected $sellerRepository;

    public function __construct($vifFolder, $supplierFilename, EntityManagerInterface $em, SellerRepository $sellerRepository)
    {
        $this->em = $em;
        $this->vifFolder = $vifFolder;
        $this->supplierFilename = $supplierFilename;
        $this->sellerRepository = $sellerRepository;
    }

    public function editSuppliers()
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;

        try {
            $file = fopen($this->vifFolder . $this->supplierFilename, 'r');
            $header = $this->getHeader();
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                $code = trim($row[$header['CODE']]);
                $existingSupplier = $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $code]);
                if (is_null($existingSupplier))
                    $supplier = $this->create($row, $header, $code);
                else 
                    $supplier = $this->update($row, $header, $existingSupplier);

                $this->em->persist($supplier);
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
        $seller = $this->getSeller();

        $supplier = new Supplier();
        $supplier->setName(trim($row[$header['LIBELLE']]))
                 ->setVifCode($code)
                 ->setSeller($seller)
                 ->setIsIntern(false);

        $this->setPhoneIfExists($supplier, $row, $header);
        $this->setEmailsIfExists($supplier, $row, $header);

        return $supplier;
    }

    private function update($row, $header, $supplier)
    {
        $supplier->setName(trim($row[$header['LIBELLE']]));

        $this->setPhoneIfExists($supplier, $row, $header);
        $this->setEmailsIfExists($supplier, $row, $header);

        return $supplier;
    }

    private function getSeller()
    {
        $sellers = $this->sellerRepository->findAll();
        return $sellers[0];
    }

    private function setPhoneIfExists(Supplier &$supplier, $row, $header)
    {
        $pattern = "/^(?:(?:\+|00)33|(?:\+|00)39|(?:\+|00)262|0)[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/";
        $tel1 = $this->isDefined('TELEPHONE 1', $row, $header) ? trim($row[$header['TELEPHONE 1']]) : "";
        $tel2 = $this->isDefined('TELEPHONE 2', $row, $header) ? trim($row[$header['TELEPHONE 2']]) : "";
        $selectedTel = strlen($tel1) > 0 && strlen($tel2) == 0 ? $tel1 :
                       (strlen($tel1) == 0 && strlen($tel2) > 0 ? $tel2 :
                       (strlen($tel1) > 0 && strlen($tel2) > 0 && str_starts_with($tel1, '06') ? $tel1 :
                       (strlen($tel1) > 0 && strlen($tel2) > 0 && str_starts_with($tel2, '06') ? $tel2 : 
                       (strlen($tel1) == 0 && strlen($tel2) == 0 ? null : $tel1))));
        if (!is_null($selectedTel) && preg_match($pattern, $selectedTel))
            $supplier->setPhone($selectedTel);
    }

    // private function setEmailIfExists(Supplier &$supplier, $row, $header)
    // {
    //     if ($this->isDefined('EMAIL', $row, $header)) 
    //         $supplier->setEmail(trim($row[$header['EMAIL']]));
    // }

    private function setEmailsIfExists(Supplier &$supplier, $row, $header)
    {
        $emailList = $supplier->getEmails();
        foreach ($header as $key => $value) {
            if (str_contains(strtoupper($key), 'EMAIL') && !in_array($row[intVal($value)], $emailList)) {
                $emailList[] = trim($row[intVal($value)]);
            }
        }
        dump($emailList);
        $supplier->setEmails($emailList);
    }

    private function isDefined($key, $row, $header)
    {
        return !is_null($row[$header[$key]]) && strlen(trim($row[$header[$key]])) > 0;
    }

    private function getHeader()
    {
        return [
            'CODE' => 0,
            'LIBELLE' => 1,
            'LIBELLE COMMERCIAL' => 2,
            'LIBELLE ETENDU' => 3,
            'ADRESSE 1' => 4,
            'ADRESSE 2' => 5,
            'CODE POSTAL' => 6,
            'VILLE' => 7,
            'PAYS' => 8,
            'TELEPHONE 1' => 9,
            'TELEPHONE 2' => 10,
            'EMAIL' => 11
        ];
    }
}