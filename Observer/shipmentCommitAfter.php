<?php
namespace Pickrr\Magento2\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Bootstrap;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Pickrr\Magento2\Helper\ExportShipment;
 
class shipmentCommitAfter implements ObserverInterface
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
        if ("0" == $this->scopeConfig->getValue('pickrr/automatic_export_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
          return NULL;

        $shipment = $observer->getEvent()->getShipment();
        $this->helper->export($shipment);
    }
}