<?php

namespace App\Service\Supplier;

use App\Entity\Supplier;
use App\Repository\SellerRepository;
use App\Service\Parser\FileParser;
use Doctrine\ORM\EntityManagerInterface;

class DataIntegrator
{
    protected $em;
    protected $vifFolder;
    protected $fileParser;
    protected $supplierFilename;
    protected $sellerRepository;
    protected $contactHeaderLine;
    protected $supplierHeaderLine;
    protected $contactSupplierFilename;
    protected $phonePattern = "/^(?:(?:\+|00)33|(?:\+|00)39|(?:\+|00)262|0)[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/";

    public function __construct($vifFolder, $supplierFilename, $contactSupplierFilename, EntityManagerInterface $em, SellerRepository $sellerRepository, FileParser $fileParser)
    {
        $this->em = $em;
        $this->contactHeaderLine = 1;
        $this->supplierHeaderLine = 1;
        $this->vifFolder = $vifFolder;
        $this->fileParser = $fileParser;
        $this->supplierFilename = $supplierFilename;
        $this->sellerRepository = $sellerRepository;
        $this->contactSupplierFilename = $contactSupplierFilename;
    }

    public function editSuppliers()
    {

        $newSuppliers = $this->createNewSuppliers();
        $status = $this->editContacts($newSuppliers);

        return $status;
    }

    private function createNewSuppliers()
    {
        $header = [];
        $lineNumber = 1;
        $seller = $this->getSeller();
        $suppliers = [];

        try {
            $this->fileParser->parse($this->vifFolder . $this->supplierFilename);
            $file = fopen($this->vifFolder . $this->supplierFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber == $this->supplierHeaderLine) {
                    $header = $this->getHeader($row);
                } else if ($lineNumber > $this->supplierHeaderLine) {
                    $code = trim($row[$header['ctie']]);
                    $existingSupplier = $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $code]);
                    if (is_null($existingSupplier))
                        $suppliers[] = $this->create($code, $seller, trim($row[$header['ltie']]), trim($row[$header['tel']]));
                }
                $lineNumber++;
            }
        } catch( \Exception $e) {
            $suppliers = null;
            dump($e->getMessage());
        } finally {
            fclose($file);
            return $suppliers;
        }
    }

    private function editContacts($newSuppliers)
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;

        if (!is_null($newSuppliers)) {
            $suppliers = $this->getResettedSuppliers($newSuppliers);
    
            try {
                $this->fileParser->parse($this->vifFolder . $this->contactSupplierFilename);
                $file = fopen($this->vifFolder . $this->contactSupplierFilename, 'r');
                while(($row = fgetcsv($file, 0, ";")) !== false)
                {
                    if ($lineNumber == $this->contactHeaderLine) {
                        $header = $this->getHeader($row);
                    } else if ($lineNumber > $this->contactHeaderLine) {
                        $code = trim($row[$header['ctie']]);
                        $supplier = $this->getConcernedSupplier($code, $suppliers);
                        $this->setEmailsIfExists($supplier, $row, $header);
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
        return 1;
    }

    private function create($code, $seller, $name, $tel)
    {
        $supplier = new Supplier();
        $supplier->setName($name)
                 ->setVifCode($code)
                 ->setSeller($seller)
                 ->setPhone($tel)
                 ->setIsIntern(false);

        if (strlen(strval($tel)) > 0 && preg_match($this->phonePattern, $tel)) {
            $supplier->setPhone($tel);
        }

        $this->em->persist($supplier);
        return $supplier;
    }

    private function getConcernedSupplier($code, $suppliers)
    {
        foreach ($suppliers as $supplier) {
            if ($supplier->getVifCode() === $code)
                return $supplier;
        }
    }

    private function getResettedSuppliers($newSuppliers)
    {
        $previousSuppliers = $this->em->getRepository(Supplier::class)->findAll();
        $suppliers = array_merge($previousSuppliers, $newSuppliers);

        foreach ($suppliers as $supplier) {
            $supplier->setEmails([]);
        }
        // $this->em->flush();

        return $suppliers;
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
            if (str_contains($key, 'email') && strlen($row[intVal($value)]) > 0 && !in_array($row[intVal($value)], $emailList)) {
                $emailList[] = trim($row[intVal($value)]);
            }
        }
        $supplier->setEmails($emailList);
    }

    private function isDefined($key, $row, $header)
    {
        return !is_null($row[$header[$key]]) && strlen(trim($row[$header[$key]])) > 0;
    }

    private function getHeader($row)
    {
        $header = [];
        foreach ($row as $key => $value) {
            $header[$value] = $key;
        }
        return $header;
    }

    // private function getHeader()
    // {
    //     return [
    //         'CODE' => 0,
    //         'LIBELLE' => 1,
    //         'LIBELLE COMMERCIAL' => 2,
    //         'LIBELLE ETENDU' => 3,
    //         'ADRESSE 1' => 4,
    //         'ADRESSE 2' => 5,
    //         'CODE POSTAL' => 6,
    //         'VILLE' => 7,
    //         'PAYS' => 8,
    //         'TELEPHONE 1' => 9,
    //         'TELEPHONE 2' => 10,
    //         'EMAIL' => 11
    //     ];
    // }
}