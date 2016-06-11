<?php

namespace Pickrr\Magento2\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;


class ExportShipment
extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function export(Shipment $shipment)
    {
        try{
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($shipment->getCustomerId());
            $billing_address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($shipment->getBillingAddressId());
            $shipping_address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($shipment->getShippingAddressId());

            $params = array(
                  'store_id' => $shipment->getStoreId(),
                  'order_id' => $shipment->getOrderId(),
                  'customer_id' => $shipment->getCustomerId(),
                  'customer_name' => $customer->getName(),
                  'customer_email' => $customer->getEmail(),
                  'billing_address' => $billing_address->debug(),
                  'shipping_address' => $shipping_address->debug(),//$shipment->getAddress(),//$shipping_address,$shipment->getShippingAddressId(),//$order->getShippingAddress(),
                  'entity_id' => $shipment->getEntityId(),
                  'created_at' => $shipment->getCreatedAt(),
                  'tracks' => array()
            );

            $trackingNumbers = array();
            foreach ($shipment->getAllTracks() as $track) {
                array_push($params['tracks'],$track->debug());
            };
            // $this->scopeConfig->getValue('pickrr/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $url = 'http://www.pickrr.com';
            $params = http_build_query($params);

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($params));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params);

            $result = curl_exec($ch);
        }
        catch (\Exception $e) {
            throw new LocalizedException(__('There was an error exporting the shipment to Pickrr: %1.', $e->getMessage()));
        }

        return 1;
    }
}