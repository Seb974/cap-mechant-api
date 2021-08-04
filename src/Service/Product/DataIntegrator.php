<?php

namespace App\Service\Product;

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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class DataIntegrator
{
    protected $em;
    protected $vifFolder;
    protected $productFilename;
    protected $taxRepository;

    public function __construct($vifFolder, $productFilename, EntityManagerInterface $em, TaxRepository $taxRepository)
    {
        $this->em = $em;
        $this->vifFolder = $vifFolder;
        $this->productFilename = $productFilename;
        $this->taxRepository = $taxRepository;
    }

    public function editProducts()
    {
        $status = 0;
        $header = [];
        $lineNumber = 1;

        try {
            $file = fopen($this->vifFolder . $this->productFilename, 'r');
            while(($row = fgetcsv($file, 0, ";")) !== false)
            {
                if ($lineNumber <= 1) {
                    $header = $this->getHeader($row);
                } else {
                    $code = trim($row[$header['CODE']]);
                    $existingProduct = $this->em->getRepository(Product::class)->findOneBy(['sku' => $code]);
                    if (is_null($existingProduct))
                        $product = $this->create($row, $header, $code);
                    else 
                        $product = $this->update($row, $header, $existingProduct);

                    $this->em->persist($product);
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
        $tax = $this->getTax();
        $seller = $this->getSeller();
        $userGroups = $this->getUserGroups();
        $catalogs = $this->getCatalogs();
        
        $stock = $this->createStock();
        $price = $this->createPrice();
        $supplier = $this->getSupplier($row, $header);
        $available = $this->getAvailability($row, $header);
        $categories = $this->getCategories($row, $header);
        $unit = $this->getUnit($row, $header);
        $product = new Product();
        $product->setSku($code)
                ->setName(trim($row[$header['LIBELLE']]))
                ->setUnit($unit)
                ->setCategories($categories)
                ->setAvailable($available)
                ->setSeller($seller)
                ->setSupplier($supplier)
                ->addPrice($price)
                ->setTax($tax)
                ->setStock($stock)
                ->setNew(false)
                ->setStockManaged(false)
                ->setRequireLegalAge(false)
                ->setWeight(0)
                ->setProductGroup("J + 1")
                ->setIsMixed(false)
                ->setRequireDeclaration(false)
                ->setContentWeight(0)
                ->setUserGroups($userGroups)
                ->setCatalogs($catalogs);
        // $this->em->persist($product);
        return $product;
    }

    private function update($row, $header, $product)
    {
        $available = $this->getAvailability($row, $header);
        $supplier = $this->getSupplier($row, $header);
        $categories = $this->getCategories($row, $header);
        $unit = $this->getUnit($row, $header);
        $product->setName(trim($row[$header['LIBELLE']]))
                ->setUnit($unit)
                ->setAvailable($available)
                ->setSupplier($supplier)
                ->setCategories($categories);

        return $product;
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