<?php

namespace App\Service\Sms;

class ProvisionNotifier
{
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function notifyOrder($provision)
    {
        $message = $this->getOrderMessage($provision);
        if (strlen($message) > 0)
            $this->sms->sendTo($provision->getSupplier()->getPhone(), $message);
    }

    private function getOrderMessage($provision)
    {
        if (count($provision->getGoods()) > 0) {
            $message = $this->getIntro($provision);
            foreach($provision->getGoods() as $good) {
                $message .= $this->getFormattedRow($good);
            }
        }
        return $message;
    }

    private function getFormattedRow($good)
    {
        $product = $this->getProductName($good);
        return " - " . $product . " : " . $good->getQuantity() . $good->getUnit() . "\n";
    }

    private function getIntro($provision)
    {
        $supplierName = $provision->getSupplier()->getName();
        $provisionDate = date_format($provision->getProvisionDate(), 'd/m/Y');
        return "Bonjour " . $supplierName . ",\nVoici ci-dessous la commande du site ". $provision->getUser()->getName() . " pour le ". $provisionDate ." à livrer au " . $this->getAddress($provision) . " :\n";
    }

    private function getProductName($good) {
        $variationName = is_null($good->getVariation()) ? "" : " - " . $good->getVariation()->getColor();
        $sizeName = is_null($good->getSize()) ? "" : " " . $good->getSize()->getName();
        return $good->getProduct()->getName() . $variationName . $sizeName;
    }

    private function getAddress($provision)
    {
        $metas = $provision->getMetas();
        return  $metas->getAddress() . (strlen($metas->getAddress2()) > 0 ? ' ' . $metas->getAddress2() : '') . ' ' .
                $metas->getZipcode() . ' - ' . $metas->getCity();
    }
}