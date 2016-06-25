<?php

namespace Pickrr\Magento2\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order;

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

    // Some code referred from: http://www.anyknowledge.com/magento-programmatically-add-shipment-with-its-tracking-number-to-any-specific-order/

    public function completeShipment($order, $shipmentTrackingNumber)
    {
        // $shipmentTrackingNumber = 'P01234';
     
        $customerEmailComments = '';
     
        if (!$order->getId()) {
            throw new LocalizedException(__("Order does not exist, for the Shipment process to complete"));
        }
     
        if ($order->canShip()) {
            try {
                $shipment = $this->_objectManager->get('Magento\Sales\Model\Order\ShipmentFactory');

                $shipmentCarrierCode = 'custom';
                $shipmentCarrierTitle = 'Pickrr';
     
                $arrTracking = array(
                    'carrier_code' => isset($shipmentCarrierCode) ? $shipmentCarrierCode : $order->getShippingCarrier()->getCarrierCode(),
                    'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $order->getShippingCarrier()->getConfigData('title'),
                    'number' => $shipmentTrackingNumber,
                );
     
                $track = $this->_objectManager->get('Magento\Sales\Model\Order\Shipment\Track')->addData($arrTracking);

                $shipment =  $shipment->create($order, $this->_getItemQtys($order), array($arrTracking));
                $shipment->register();
                $shipment->save();

                $emailSentStatus = $shipment->getData('email_sent');
                if (!is_null($customerEmailComments) && !$emailSentStatus) {
                    // $this->_objectManager->get('Magento\Sales\Model\Order\Email\Sender\ShipmentSender')->send($shipment);
                    $shipment->setEmailSent(true);
                }

                $this->_saveOrder($order);

            } catch (Exception $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
    }
     
    /**
     * Get the Quantities shipped for the Order, based on an item-level
     * This method can also be modified, to have the Partial Shipment functionality in place
     *
     * @param $order Mage_Sales_Model_Order
     * @return array
     */
    protected function _getItemQtys(Order $order)
    {
        $qty = array();
     
        foreach ($order->getAllItems() as $_eachItem) {
            if ($_eachItem->getParentItemId()) {
                $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
            } else {
                $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
            }
        }
     
        return $qty;
    }
     
    /**
     * Saves the Order, to complete the full life-cycle of the Order
     * Order status will now show as Complete
     *
     * @param $order Mage_Sales_Model_Order
     */
    protected function _saveOrder(Order $order)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);
     
        $order->save();
    }

    public function createShipment($auth_token, $item_name, $pickup_time, $from_name, $from_phone_number, $from_pincode, $from_address, $to_name, $to_phone_number, $to_pincode, $to_address, $order_id = 'NULL', $cod=0.0)
    {
        try{

          $from_pincode = $to_pincode = '245101';

          $params = array(
                      'auth_token' => $auth_token,
                      'item_name' => $item_name,
                      'order_time' => $pickup_time,
                      'from_name' => $from_name,
                      'from_phone_number' => $from_phone_number,
                      'from_pincode'=> $from_pincode,
                      'from_address'=> $from_address,
                      'to_name'=> $to_name,
                      'to_phone_number' => $to_phone_number,
                      'to_pincode' => $to_pincode,
                      'to_address' => $to_address,
                      'client_order_id' => $order_id
                    );

            if($cod>0.0) $params['cod'] = $cod;

            $json_params = json_encode( $params );

            $url = 'http://www.pickrr.com/api/place-order/';
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $json_params);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            //execute post
            $result = curl_exec($ch);
            $result = json_decode($result, true);

            //close connection
            curl_close($ch);

            return $result['tracking_id'];

        }
        catch (\Exception $e) {
            throw new LocalizedException(__('There was an error in creating the Pickrr shipment: %1.', $e->getMessage()));
        }


    }

    public function createOrderShipment($auth_token, $order, $pickup_time, $from_name, $from_phone_number, $from_pincode, $from_address, $cod=0.0)
    {
        try{
            $itemCount = $order->getTotalItemCount();
            $item_name = "NULL";

            if($itemCount==1) $item_name = $order->getItemsCollection()->getFirstItem()->getName();
            else $item_name = 'Multiple Items';

            $shipping_address = $order->getShippingAddress();            
            $to_name = $shipping_address->getName();
            $to_phone_number = $shipping_address->getTelephone();
            $to_pincode = $shipping_address->getPostcode();
            $to_address = implode(', ', $shipping_address->getStreet()) . ", " . $shipping_address->getCity() . ", " . $shipping_address->getRegion();
            $order_id = $order->getIncrementId();

            $tracking_no = $this->createShipment($auth_token, $item_name, $pickup_time, $from_name, $from_phone_number, $from_pincode, $from_address, $to_name, $to_phone_number, $to_pincode, $to_address, $order_id, $cod);

            $this->completeShipment($order, $tracking_no);
        }
        catch (\Exception $e) {
            throw new LocalizedException(__('There was an error in creating a Pickrr shipment using order object: %1.', $e->getMessage()));
        }
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
                  'shipping_address' => $shipping_address->debug(),
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