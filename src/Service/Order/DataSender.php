<?php

namespace App\Service\Order;

class DataSender
{
    protected $delimiter;
    protected $vifFolder;
    protected $ordersFilename;

    public function __construct($vifFolder, $ordersFilename)
    {
        $this->delimiter = ',';
        $this->vifFolder = $vifFolder;
        $this->ordersFilename = $ordersFilename;
    }

    public function sendToVIF($provision)
    {
        try {
            $file = fopen($this->vifFolder . $this->ordersFilename, 'a');
            $this->setHeader($file, $provision);
            foreach ($provision->getGoods() as $key => $good) {
                $formattedGood = $this->getFormattedRowGood($provision, $key, $good);
                fputcsv($file, $formattedGood, $this->delimiter);
            }
        } catch(\Exception $e) {
            dump($e->getMessage());
        }
        finally {
            fclose($file);
        }
    }

    private function getFormattedRowGood($provision, $key, $good)
    {
        return [
            'L',
            'CL',
            $provision->getSupplier()->getVifCode(),
            'S' . str_pad(strval($provision->getId()), 10, "0", STR_PAD_LEFT),
            $key + 1,
            $good->getProduct()->getSku(),
            $good->getQuantity() * 1000,
            $good->getUnit()
        ];
    }

    private function setHeader($file, $provision)
    {
        $header = $this->getHeaderSite($provision);
        fputcsv($file, $header, $this->delimiter);
    }

    private function getHeaderSite($provision)
    {
        return [
            'E',
            'CL',
            $provision->getSupplier()->getVifCode(),
            'S' . str_pad(strval($provision->getId()), 10, "0", STR_PAD_LEFT),
            $provision->getUser()->getVifCode(),
            $provision->getProvisionDate()->format('d/m/Y'), 
            (new \DateTime())->format('d/m/Y')
        ];
    }
}