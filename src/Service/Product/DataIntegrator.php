<?php

namespace App\Service\Product;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Price;
use App\Entity\Stock;
use App\Entity\Seller;
use App\Entity\Catalog;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Supplier;
use App\Entity\PriceGroup;
use App\Repository\TaxRepository;
use App\Service\Parser\FileParser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
            // return "status =  ". $status .", edit product = " . memory_get_usage()/1048576.2 . "MB";
            return $status;
        }
    }

    private function editUsersProducts($products){
        // $this->em->clear();
        $lineNumber= 1;
        $header = [];
        //$products = [];
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

    private function setUsersProducts($row, $header, $product, $user){
        $product->addUser($user);
        return $product;
    }

    private function insertOrUpdateProducts($productsList){
        ini_set('max_execution_time', 240);
        $this->em->clear();
        // dd($productsList);
        $status = 0;
        //le modulo de nombre total de produit par 500.
        $modulo = count($productsList)/600;
        //je cherche le chiffre entier pour ma boucle
        // ainsi avoir le nombre d'itération pour une insertion de 500 par 500
        $floor = floor($modulo);
        //je commence mon tour pour la boucle for à 0;
        $round = 0;
        try{
            while($floor > -1 ){
                $min = ($round != 0) ? $round + 1 : $round ;
                $max = ($modulo-$floor)*600;
                for($i = $min; $i < $max; $i ++){

                    if (is_null($productsList[$i]->getId()))
                        $this->em->persist($productsList[$i]); 
                    else 
                        $this->em->merge($productsList[$i]);
                }
                
                $round = $max;
                $floor--;
            }
            $this->em->flush();
        } catch( \Exception $e) {
            $status = 1;
        }finally{
            // $this->em->clear();
            return $status;
        }
    }

    private function editSuppliers($editedProducts)
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;
        $products = $this->getResettedProducts($editedProducts);
        try {
            $file = fopen($this->vifFolder . $this->supplierOwnerFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber <= 1) {
                    $header = $this->getHeader($row);
                } else {
                    $articleCode = trim($row[$header['Article']]);
                    $supplierCode = trim($row[$header['Fourn.']]);
                    $product = $this->getConcernedProduct($articleCode, $products);
                    if (!is_null($product) && !$product->getIsIntern()) {
                        $supplier = $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $supplierCode]);
                        $supplier->addProduct($product);
                    }
                }
                $lineNumber++;
            }
        } catch( \Exception $e) {
            $status = 1;
        } finally {
            fclose($file);
            // return "edit supplier = " . memory_get_usage()/1048576.2;
            return $status;
        }
    }

    private function create($row, $header, $code)
    {
        $seller = $this->getSeller();
        $available = $this->getAvailability($row, $header);
        // $categories = $this->getCategories($row, $header);
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
        // $categories = $this->getCategories($row, $header);
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

    private function createStock()
    {
        $stock = new Stock;
        $stock->setQuantity(0)
              ->setSecurity(0)
              ->setAlert(0);

        $this->em->persist($stock);
        return $stock;
    }

    private function createPrice()
    {
        $priceGroups = $this->em->getRepository(PriceGroup::class)->findAll();

        $price = new Price();
        $price->setAmount(0)
              ->setPriceGroup($priceGroups[0]);

        $this->em->persist($price);
        return $price;
    }

    private function getUnit($row, $header)
    {
        return strlen(trim($row[$header['UNITE COMMANDE']])) > 0 ? trim($row[$header['UNITE COMMANDE']]) : "Kg";
    }

    private function getSupplier($row, $header)
    {
        $type = trim($row[$header['TYPE']]);
        $site = trim($row[$header['SITE PRODUCTION']]);

        if ($type == 'ACH')
            return $this->em->getRepository(Supplier::class)->findOneBy(['isIntern' => false]);
        else if (strlen($site) > 0)
            return $this->em->getRepository(Supplier::class)->findOneBy(['vifCode' => $site]);
        else 
            return $this->em->getRepository(Supplier::class)->findOneBy(['isIntern' => true]);
    }

    private function getSeller()
    {
        $sellers = $this->em->getRepository(Seller::class)->findAll();
        return $sellers[0];
    }

    private function getTax()
    {
        $taxes = $this->taxRepository->findAll();
        return $taxes[0];
    }

    private function getCatalogs()
    {
        $catalogsCollection = new ArrayCollection();
        $catalogs = $this->em->getRepository(Catalog::class)->findAll();
        foreach ($catalogs as $catalog) {
            if (!$catalogsCollection->contains($catalog))
                $catalogsCollection[] = $catalog;
        }
        return $catalogsCollection;
    }

    private function getAvailability($row, $header)
    {
        return trim($row[$header['VALIDITE']]) === 'E';
    }

    private function getUserGroups()
    {
        $groupsCollection = new ArrayCollection();
        $userGroups = $this->em->getRepository(Group::class)->findAll();
        foreach ($userGroups as $userGroup) {
            if (!$groupsCollection->contains($userGroup))
                $groupsCollection[] = $userGroup;
        }
        return $groupsCollection;
    }

    private function getCategories($row, $header)
    {
        $code = trim($row[$header['CATEGORIE']]);
        $category = strlen($code) > 0 ?
            $this->em->getRepository(Category::class)->findOneBy(['code' => $code]) :
            ($this->em->getRepository(Category::class)->findAll())[0];
        $categories = new ArrayCollection();
        $categories[] = is_null($category) ? ($this->em->getRepository(Category::class)->findAll())[0] : $category;
        return $categories;
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