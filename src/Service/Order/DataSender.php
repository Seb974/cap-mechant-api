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

    public function sendToVIF($order)
    {
        try {
            $file = fopen($this->vifFolder . $this->ordersFilename, 'a');
            $internalItems = $this->getInternalItems($order);
            foreach ($internalItems as $site => $items) {
                if (count($items) > 0) {
                    $this->setHeader($file, $site, $order);
                    foreach ($items as $key => $item) {
                        $formattedItem = $this->getFormattedRowItem($site, $order, $key, $item);
                        fputcsv($file, $formattedItem, $this->delimiter);
                    }
                }
            }
        } catch(\Exception $e) {
            dump($e->getMessage());
        }
        finally {
            fclose($file);
        }
    }

    private function getInternalItems($order)
    {
        $internalProducts = [];
        foreach ($order->getItems() as $item) {
            $supplier = $item->getProduct()->getSupplier();
            if ($supplier->getIsIntern())
                $internalProducts[$supplier->getVifCode()][] = $item;
        }
        return $internalProducts;
    }

    private function getFormattedRowItem($site, $order, $key, $item)
    {
        return [
            'L',
            'CL',
            $site,
            'S' . str_pad(strval($order->getId()), 10, "0", STR_PAD_LEFT),
            $key + 1,
            $item->getProduct()->getSku(),
            $item->getOrderedQty() * 1000,
            $item->getUnit()
        ];
    }

    private function setHeader($file, $site, $order)
    {
        $header = $this->getHeaderSite($site, $order);
        fputcsv($file, $header, $this->delimiter);
    }

    private function getHeaderSite($site, $order)
    {
        return [
            'E',
            'CL',
            $site,
            'S' . str_pad(strval($order->getId()), 10, "0", STR_PAD_LEFT),
            $order->getUser()->getVifCode(),
            $order->getDeliveryDate()->format('d/m/Y'), 
            (new \DateTime())->format('d/m/Y')
        ];
    }
}