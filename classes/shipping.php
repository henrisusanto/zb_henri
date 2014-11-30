<?php

/* Zaner-Bloser shipping policy: 9% or $5 whichever is higher
 * How to use this class :
 * 1. install commerce_shipping, commerce_flat_rate
 * 2. create a flat rate shipping service
 * 3. implement hook_commerce_shipping_service_info_alter(&$shipping_services) to point callback function into our defined function
 * 4. call this class from the callback function above
 */

class shipping {

    var $shipping_product_types = array('book');
    
    function getShippingPrice($order) {
        $config = $this->getConfig();
        $nine_percent = 0;
        foreach ($order->commerce_line_items['und'] as $container) {
            $line_item = commerce_line_item_load($container['line_item_id']);
            if ($line_item->type == 'product') {
                $product = commerce_product_load_by_sku($line_item->line_item_label);
                if (in_array($product->type, $this->shipping_product_types)) {
                    $price = $product->commerce_price;
                    $price_amount = $price['und'][0]['amount'];
                    $nine_percent += $price_amount * $line_item->quantity * $config['zb_shipping_percentage'];
                }
            }
        }
        
        $shipping_rate = array(
            'amount' => $config['zb_shipping_amount'],
            'currency_code' => 'USD',
            'data' => array(),
        );
        
        $shipping_rate['amount'] = 
            $nine_percent > $shipping_rate['amount'] ?
            $nine_percent : $shipping_rate['amount'];
        
        return $shipping_rate;
    }
    
    function getConfigFormElement(){
        $current_config = $this->getConfig(false);
        $form['zb_shipping_amount'] = array(
            '#type' => 'textfield',
            '#title' => t('Shipping amount ($)'),
            '#required' => TRUE,
            '#default_value' => $current_config['zb_shipping_amount'],
        );
        $form['zb_shipping_percentage'] = array(
            '#type' => 'textfield',
            '#title' => t('Shipping percentage (%)'),
            '#required' => TRUE,
            '#default_value' => $current_config['zb_shipping_percentage'],
        );
        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );
        return $form;
    }
    
    function setConfig($form_state){
        foreach(array('zb_shipping_amount','zb_shipping_percentage') as $config)
            $zb_shipping_config[$config] = $form_state['values'][$config];
        variable_set('zb_shipping_config', $zb_shipping_config);
    }
    
    function getConfig($calculated = TRUE){
        $config = array(
            'zb_shipping_amount' => 5,
            'zb_shipping_percentage' => 9,
        );
        $current_config = variable_get('zb_shipping_config');
        if(null!=$current_config){
            $config['zb_shipping_amount'] = $current_config['zb_shipping_amount'];
            $config['zb_shipping_percentage'] = $current_config['zb_shipping_percentage'];
        }
        if($calculated){
            $config['zb_shipping_amount'] = $config['zb_shipping_amount'] * 100;
            $config['zb_shipping_percentage'] = $config['zb_shipping_percentage'] / 100;
        }
        return $config;
    }
}