<?php

namespace App\Service\Order;

use App\Entity\Group;
use App\Entity\Catalog;
use App\Entity\Product;
use App\Service\Tax\Tax;
use App\Entity\Container;
use App\Service\Package\Packer;
use App\Repository\CatalogRepository;
use App\Repository\ProductRepository;
use App\Service\User\UserGroupDefiner;
use App\Repository\ConditionRepository;
use Symfony\Component\Security\Core\Security;

class Calculator
{
    private $tax;
    private $packer;
    private $security;
    private $userGroupDefiner;
    private $productRepository;
    private $conditionRepository;

    public function __construct(Security $security, UserGroupDefiner $userGroupDefiner, Packer $packer, Tax $tax, CatalogRepository $catalogRepository, ProductRepository $productRepository, ConditionRepository $conditionRepository)
    {
        $this->tax = $tax;
        $this->packer = $packer;
        $this->security = $security;
        $this->userGroupDefiner = $userGroupDefiner;
        $this->catalogRepository = $catalogRepository;
        $this->productRepository = $productRepository;
        $this->conditionRepository = $conditionRepository;
    }

    // public function getTotalCost($parameters)
    // {
    //     $user = $this->security->getUser();
    //     $userGroup = $this->userGroupDefiner->getUserGroup($user);
    //     $catalog = $this->catalogRepository->find($parameters->get('area')['id']);
    //     $condition = $parameters->get('condition') !== null ? $this->conditionRepository->find($parameters->get('condition')['id']) : 0;
    //     $itemsCost = $this->getItemsCost($parameters->get('items'), $catalog, $userGroup);
    //     $finalItemsCost = $this->applyDiscount($parameters->get('promotion'), $itemsCost);
    //     $packagesCost = $catalog->getNeedsParcel() ?
    //                     $this->getPackagesCost($parameters->get('items'), $catalog) :
    //                     $this->getDeliveryCost($condition, $catalog, $itemsCost);
    //     return $finalItemsCost + $packagesCost;
    // }

    // private function applyDiscount($discount, $itemsCost)
    // {
    //     if ($discount === null)
    //         return round($itemsCost * 100) / 100;
    //     else if ($discount['percentage'])
    //         return round($itemsCost * (1 - ($discount['discount'] < 1 ? $discount['discount'] : $discount['discount'] / 100)) * 100) / 100 ;
    //     else
    //         return (round($itemsCost * 100) / 100) - $discount['discount'];
    // }

    // private function getItemsCost(array $items, Catalog $catalog, Group $userGroup)
    // {
    //     $accumulator = 0;
    //     foreach ($items as $item) {
    //         $product = $this->productRepository->find($item['product']['id']);
    //         $price = $this->getProductPrice($product, $catalog, $userGroup);
    //         $accumulator += ($item['quantity'] * $price);
    //     }
    //     return $accumulator;
    // }

    // private function getPackagesCost(array $items, Catalog $catalog)
    // {
    //     $accumulator = 0;
    //     $packages = $this->packer->getPackages($items);
    //     foreach ($packages as $package) {
    //         $price = $this->getContainerPrice($package['container'], $catalog);
    //         $accumulator += ($package['quantity'] * $price);
    //     }
    //     return $accumulator;
    // }

    // private function getDeliveryCost($condition, $catalog, $itemsCost)
    // {
    //     if ($condition == null)
    //         return 0;

    //     $tax = $this->tax->getTaxRate($condition, $catalog);
    //     $price = $itemsCost < $condition->getMinForFree() ? $condition->getPrice() : 0;
    //     return round($price * (1 + $tax) * 100) / 100;
    // }

    // private function getProductPrice(Product $product, Catalog $catalog, Group $userGroup)
    // {
    //     $priceGroup = $userGroup->getPriceGroup();
    //     $tax = $this->tax->getTaxRate($product, $catalog);
    //     foreach ($product->getPrices() as $price) {
    //         if ($price->getPriceGroup()->getId() == $priceGroup->getId())
    //             return round($price->getAmount() * (1 + $tax) * 100) / 100;
    //     }
    //     return 0;
    // }

    // private function getContainerPrice(Container $container, Catalog $catalog)
    // {
    //     $tax = $this->tax->getTaxRate($container, $catalog);
    //     foreach ($container->getCatalogPrices() as $catalogPrice) {
    //         if ($catalogPrice->getCatalog()->getId() == $catalog->getId())
    //             return ($catalogPrice->getAmount() * (1 + $tax));
    //     }
    //     return 0;
    // }
}