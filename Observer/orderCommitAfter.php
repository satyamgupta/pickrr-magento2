<?php
namespace Pickrr\Magento2\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Bootstrap;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Pickrr\Magento2\Helper\ExportShipment;
use Magento\Sales\Model\Order;
 
class orderCommitAfter implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        ExportShipment $helper
    ) {
        $this->helper = $helper;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }
 
    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ("0" == $this->scopeConfig->getValue('pickrr_magento2/general/automatic_export_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
          return NULL;

        $order = $observer->getEvent()->getOrder();

        if ($order->getState() == Order::STATE_PROCESSING )
            return NULL;

        $auth_token = $this->scopeConfig->getValue('pickrr_magento2/general/auth_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $pickup_time = $this->scopeConfig->getValue('pickrr_magento2/shipment_details/pickup_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from_name = $this->scopeConfig->getValue('pickrr_magento2/shipment_details/from_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from_phone_number = $this->scopeConfig->getValue('pickrr_magento2/shipment_details/from_phone_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from_pincode = $this->scopeConfig->getValue('pickrr_magento2/shipment_details/from_pincode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from_address = $this->scopeConfig->getValue('pickrr_magento2/shipment_details/from_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->helper->createOrderShipment($auth_token, $order, $from_name, $from_phone_number, $from_pincode, $from_address, $pickup_time);
    }
}