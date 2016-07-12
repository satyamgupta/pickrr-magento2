## Pickrr Shipment Extension for Magento

Pickrr Magento 2 module for automatic/manual creation of shipments thorugh Pickrr.

###Installation Instructions:

1. Extract the zip file in the the `<root_of_Magento2>/app/code` <br>
 OR <br>
 Goto Magento's root folder and install it using composer from terminal:
 
 ```shell
 composer require pickrr/magento2:dev-master
 ```
2. Goto root folder of magento in terminal, and run:
 ```shell
 bin/magento module:enable Pickrr_Magento2
 bin/magento setup:upgrade
 
 ```
3. Verify in Magento Admin Panel whether the module is enabled. To check, go to Admin Panel >Stores>Configuration>Advanced>Advanced>Pickrr_magento2 <br>
4. Goto Admin Panel >Stores>configuration>PickrrExtensions>PickrrMagento2, and enable the automatic shipment option & enter the asked details.

In case of any problems/queries, contact info@pickrr.com

---

###Usage Instructions (only for manual calls, when automatic shipment mode is not enabled):

####Import helper class:

```php
//import helper class

Pickrr\Magento2\Helper\ExportShipment $helper;

```

####Create a simple Pickrr Shipment:

Passing $pickup_time and $cod is optional.

**Prototype of the function:**
```php
createShipment($auth_token, $item_name, $from_name, $from_phone_number, $from_pincode, $from_address, $to_name, $to_phone_number, $to_pincode, $to_address, $cod=0.0, $pickup_time='NULL', $order_id = 'NULL');
```

It returns the tracking_id from Pickrr.

**Usage:**
```php
//Create shipment using order

$auth_key =  'Your Auth Key';

$helper->createOrderShipment($auth_key, "Item's Name", "Merchant/Sender's Name", "Merchant/Sender's Phone", 'Pickup Address Pin', 'Pickup Address', 300.0, '2016-06-17 17:00');
```

---

####Create Shipment using order:

This will also create shipment and associate it with the passed order. The client/customer's address, item's name and order's id will be extracted from order.

**Prototype of the function:**
```php
createOrderShipment($auth_token, $order, $from_name, $from_phone_number, $from_pincode, $from_address, $cod=0.0, $pickup_time='NULL');

```

**Usage:**
```php
//Create shipment using order

$auth_key =  'Your Auth Key';
$order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId('100000094');

$helper->createOrderShipment($auth_key, $order, "Merchant/Sender's Name", "Merchant/Sender's Phone", 'Pickup Address Pin', 'Pickup Address', 300.0, '2016-06-17 17:00');
```
