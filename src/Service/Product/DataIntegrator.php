<?php

namespace App\Service\Product;

use App\Entity\User;
use App\Entity\Seller;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Repository\TaxRepository;
use App\Service\Parser\FileParser;
use Doctrine\ORM\EntityManagerInterface;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class DataIntegrator
{
    protected $em;
    protected $vifFolder;
    protected $productFilename;
    protected $supplierOwnerFilename;
    protected $commandTypeFileName;
    protected $taxRepository;
    protected $fileParser;

    public function __construct($vifFolder, $productFilename, $supplierOwnerFilename, $commandTypeFileName,EntityManagerInterface $em, TaxRepository $taxRepository, FileParser $fileParser)
    {
        $this->em = $em;
        $this->vifFolder = $vifFolder;
        $this->productFilename = $productFilename;
        $this->supplierOwnerFilename = $supplierOwnerFilename;
        $this->commandTypeFileName = $commandTypeFileName;
        $this->taxRepository = $taxRepository;
        $this->fileParser = $fileParser;
    }

    public function editProducts()
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;
        $products = [];
        $count = [];

        try {
            $status = 0;
            ini_set('memory_limit', -1); 
            // set_time_limit(120);
            $this->fileParser->parse($this->vifFolder . $this->productFilename);
            $file = fopen($this->vifFolder . $this->productFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber <= 1) {
                    $header = $this->getHeader($row);
                } else {
                    $code = trim($row[$header['CODE']]);
                    $existingProduct = $this->em->getRepository(Product::class)->findOneBy(['sku' => $code]);
                    if (is_null($existingProduct)) {
                        $product = $this->create($row, $header, $code);
                    } else {
                        $product = $this->update($row, $header, $existingProduct);

                    }
                    $products[] = $product;
                    $this->em->persist($product);
                }
                $lineNumber++;
            }  

            fclose($file);
            $products = $this->editUsersProducts($products);
            $this->editSuppliers($products);
            $this->em->flush();
            
        } catch( \Exception $e) {
            $status = 1;
            dump($e->getMessage());
        } finally {
            return $status;
        }
    }

    private function editUsersProducts($productsList){
        $lineNumber= 1;
        $header = [];
        $products = $this->getResettedUsersProducts($productsList);
        $this->fileParser->parse($this->vifFolder . $this->commandTypeFileName);
        $file = fopen($this->vifFolder . $this->commandTypeFileName, 'r');
        while(($row = fgetcsv($file, 0, ";")) !== false)
        {
            if ($lineNumber <= 1) {
                $header = $this->getHeader($row);
            } else {
                $sku = trim($row[$header['cart']]);
                $vifUser = trim($row[$header['ctie']]);
                $existingUser = $this->em->getRepository(User::class)->findOneBy(['vifCode' => $vifUser]);
                if (!is_null($existingUser)){
                    $key = $this->getSelectedProduct($products, $sku);
                    if (!is_null($key))
                        $products[$key]->addUser($existingUser); 
                } 
            }
            $lineNumber ++;
        }
        fclose($file);
        return $products;
        
    }

    private function getSelectedProduct($products, $sku) 
    {
        foreach ($products as $key => $product) {
            if ($product->getSku() === $sku) {
                return $key;
            }
        }
        return null;
    }

    private function editSuppliers($editedProducts)
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;
        $products = $this->getResettedProducts($editedProducts);
        try {
            $this->fileParser->parse($this->vifFolder . $this->supplierOwnerFilename);
            $file = fopen($this->vifFolder . $this->supplierOwnerFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber <= 1) {
                    $header = $this->getHeader($row);
                } else {
                    $articleCode = trim($row[$header['Article']]);
                    $supplierCode = trim($row[$header['Fourn.']]);
                    $product = $this->getConcernedProduct($articleCode, $products);
                    if (!is_null($product) ) {       // && !$product->getIsIntern()
                        $supplier = $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $supplierCode]);
                        $supplier->addProduct($product);
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

    private function create($row, $header, $code)
    {
        $seller = $this->getSeller();
        $available = $this->getAvailability($row, $header);
        $unit = $this->getUnit($row, $header);
        $product = new Product();
        $product->setSku($code)
                ->setName(trim($row[$header['LIBELLE']]))
                ->setUnit($unit)
                ->setAvailable($available)
                ->setSeller($seller)
                ->setIsIntern(false)
                ->setWeight(0)
                ->setCategories(trim($row[$header['NOM CATEGORIE']]))
                ;
        $this->setInternSupplierIfNeeded($product, $row, $header);
        return $product;
    }

    private function update($row, $header, $product)
    {
        $available = $this->getAvailability($row, $header);
        $unit = $this->getUnit($row, $header);
        $product->setName(trim($row[$header['LIBELLE']]))
                ->setUnit($unit)
                ->setAvailable($available)
                ->setIsIntern(false)
                ->setCategories(trim($row[$header['NOM CATEGORIE']]));

        $this->setInternSupplierIfNeeded($product, $row, $header);
        return $product;
    }

    private function setInternSupplierIfNeeded(&$product, $row, $header)
    {
        $type = trim($row[$header['TYPE']]);
        $site = trim($row[$header['SITE PRODUCTION']]);
        if ($type == "VTE" && strlen(strval($site)) > 0) {
            $supplier = $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $site]);
            $supplier->addProduct($product);
            $product->setIsIntern(true);
        }
    }

    private function getConcernedProduct($code, $products)
    {
        foreach ($products as $product) {
            if ($product->getSku() == $code)
                return $product;
        }
        return null;
    }

    private function getResettedProducts($editedProducts)
    {
        foreach ($editedProducts as $product) {
            if (!$product->getIsIntern()) {
                $suppliers = $product->getSuppliers();
                foreach ($suppliers as $supplier) {
                    $supplier->removeProduct($product);
                }
            }
        }
        return $editedProducts;
    }

    private function getResettedUsersProducts($editedProducts)
    {
        foreach ($editedProducts as $product) {
                $users = $product->getUsers();
                foreach ($users as $user) {
                    $product->removeUser($user);
                }
        }
        return $editedProducts;
    }

    private function getUnit($row, $header)
    {
        return strlen( trim($row[$header['UNITE PRINCIPALE']]) ) > 0 ? trim($row[$header['UNITE PRINCIPALE']]) : "Kg";
    }

    private function getSeller()
    {
        return $this->em->getRepository(Seller::class)->find(1);
        // return $sellers[0];
    }

    private function getAvailability($row, $header)
    {
        return trim($row[$header['VALIDITE']]) === 'E';
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