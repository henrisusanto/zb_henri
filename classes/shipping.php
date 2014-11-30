<?php

/* Zaner-Bloser shipping policy: 9% or $5 whichever is higher
 * How to use this class :
 * 1. install commerce_shipping, commerce_flat_rate
 * 2. create a flat rate shipping service
 * 3. implement hook_commerce_shipping_service_info_alter(&$shipping_services) to point callback function into our defined function
 * 4. call this class from the callback function above
 */

class shipping {

    var $shipping_product_types = array('zb_book');
    var $shipping_percentage = 0.09; // 9%
    var $shipping_rate = array(
        'amount' => 500, // $5
        'currency_code' => 'USD',
        'data' => array(),
    );

    function getShippingPrice($order) {
        $nine_percent = 0;
        foreach ($order->commerce_line_items['und'] as $container) {
            $line_item = commerce_line_item_load($container['line_item_id']);
            if ($line_item->type == 'product') {
                $product = commerce_product_load_by_sku($line_item->line_item_label);
                if (in_array($product->type, $this->shipping_product_types)) {
                    $price = $product->commerce_price;
                    $price_amount = $price['und'][0]['amount'];
                    $nine_percent += $price_amount * $line_item->quantity * $this->shipping_percentage;
                }
            }
        }
        $this->shipping_rate['amount'] = 
            $nine_percent > $this->shipping_rate['amount'] ?
            $nine_percent : $this->shipping_rate['amount'];
        return $this->shipping_rate;
    }

}