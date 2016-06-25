## Pickrr Shipment Extension for Magento

Pickrr Magento 2 module for automatic/manual importing of shipment/tracking details in your pickrr account.

###Installation Instructions:

* Extract the zip file in the the <root_of_Magento2>/app/code
* Goto root folder of magento in terminal, and run: 
```shell
bin/magento module:enable Pickrr_Magento2
bin/magento setup:upgrade

```
* Verify in Magento Admin Panel whether the module is enabled. To check go to Admin>Stores>Configuration>Advanced>Advanced>Pickrr_magento1

---

###Usage Instructions:

####Import helper class:

```php
//import helper class

Pickrr\Magento2\Helper\ExportShipment $helper;

```

####Create a simple Pickrr Shipment:

**Prototype of the function:**
```php
createShipment($auth_token, $item_name, $pickup_time, $from_name, $from_phone_number, $from_pincode, $from_address, $to_name, $to_phone_number, $to_pincode, $to_address, $order_id = 'NULL', $cod=0.0);
```

It returns the tracking_id from Pickrr.

**Usage:**
```php
//Create shipment using order

$auth_key =  'Your Auth Key';

$helper->createOrderShipment($auth_key, "Item's Name", '2016-06-17 17:00', "Merchant/Sender's Name", "Merchant/Sender's Phone", 'Pickup Address Pin', 'Pickup Address');
```

---

####Create Shipment using order:

This will also create shipment and associate it with the passed order. The client/customer's address, item's name and order's id will be extracted from order.

**Prototype of the function:**
```php
createOrderShipment($auth_token, $order, $pickup_time, $from_name, $from_phone_number, $from_pincode, $from_address, $cod=0.0);

```

**Usage:**
```php
//Create shipment using order

$auth_key =  'Your Auth Key';
$order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId('100000094');

$helper->createOrderShipment($auth_key, $order, '2016-06-17 17:00', "Merchant/Sender's Name", "Merchant/Sender's Phone", 'Pickup Address Pin', 'Pickup Address');
```
