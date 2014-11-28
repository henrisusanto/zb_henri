<?php

/**
 * Zaner-Bloser need to define default sorting for each term of taxonomy product-line and product-category
 * 1. add list-of-text field to store sorting option into both taxonomy
 * 2. add boolean field to mark whether ASC or DESC into both taxonomy
 * 3. use hook_field_delete_instance & hook_field_create_instance to update options of field no.1
 * 4. use .view.inc file to implement sorting alter into target views
 */
class sorting {

    // PRODUCT CATEGORY > PRODUCT LINE > PRODUCT DISPLAY
    var $product_line = array(
        'taxonomy_name' => 'zb_product_line',
        'sorting_field' => 'field_pline_1field_sorting',
        'sorting_type_field' => 'field_pline_1bool_sorttype',
        'natural_fields' => array('title' => 'Product Display Title')
    );
    var $product_category = array(
        'taxonomy_name' => 'zb_product_category',
        'sorting_field' => 'field_pcat_1field_sorting',
        'sorting_type_field' => 'field_pcat_1bool_sorttype',
        'views_machine_name' => 'list_of_product_line',
        'natural_fields' => array('taxonomy_term_data_name' => 'Product Line Name')
    );
    var $product_display = array(
        'content_type_name' => 'zb_product_display'
    );

    function updateProductLineSortingOptions() {
        $allowed_list = array();
        foreach (field_info_instances('node', $this->product_display['content_type_name']) as $key => $value)
            $allowed_list[$key] = $value['label'];
        
        foreach($this->product_line['natural_fields'] as $field => $label)
            $allowed_list[$field] = $label;
        
        $info = field_info_field($this->product_line['sorting_field']);
        $values = &$info['settings']['allowed_values'];
//        foreach ($allowed_list as $value => $label)
//            if(in_array($value, array_keys($values))) unset($allowed_list[$value]);
        $values = $allowed_list;
        field_update_field($info);
    }

    function updateProductCategorySortingOptions() {
        $allowed_list = array();
        foreach (field_info_instances('taxonomy_term', $this->product_line['taxonomy_name']) as $key => $value)
            if ($key != $this->product_line['sorting_field'] &&
                $key != $this->product_line['sorting_type_field']&&
                $value != 'Product Category')
                $allowed_list[$key] = $value['label'];
            
        foreach($this->product_category['natural_fields'] as $field => $label)
            $allowed_list[$field] = $label;
        
        $info = field_info_field($this->product_category['sorting_field']);
        $values = &$info['settings']['allowed_values'];
//        foreach ($allowed_list as $value => $label)
//            if(in_array($value, array_keys($values))) unset($allowed_list[$value]);
        $values = $allowed_list;
        field_update_field($info);
    }

    function getProductLineSortingParam($term) {
        $retrieved_field = field_get_items('taxonomy_term', $term, $this->product_line['sorting_field']);
        $sorting_field = $retrieved_field[0]['value'];
        $retrieved_field = field_get_items('taxonomy_term', $term, $this->product_line['sorting_type_field']);

        $param = array(
            'join' => null,
            'field' => $sorting_field,
            'direction' => $retrieved_field[0]['value'] ? 'ASC' : 'DESC',
        );

        if ($sorting_field == '0') $param = null;
        else if (!in_array($sorting_field, array_keys($this->product_line['natural_fields']))) {
            $table_fields = db_query("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME`='field_data_$sorting_field'")->fetchAll();
            $foregin_key_relation = end($table_fields);

            $join = new views_join();
            $join->table = "field_data_$sorting_field";
            $join->field = 'entity_id';
            $join->left_table = 'node';
            $join->left_field = 'nid';
            $join->type = 'LEFT';

            $param['join'] = $join;
            $param['field'] = $foregin_key_relation->COLUMN_NAME;
        }
        return $param;
    }

    function getProductCategorySortingParam($term) {
        $retrieved_field = field_get_items('taxonomy_term', $term, $this->product_category['sorting_field']);
        $sorting_field = $retrieved_field[0]['value'];
        $retrieved_field = field_get_items('taxonomy_term', $term, $this->product_category['sorting_type_field']);

        $param = array(
            'join' => null,
            'field' => $sorting_field,
            'direction' => $retrieved_field[0]['value'] ? 'ASC' : 'DESC',
        );

        if ($sorting_field == '0') $param = null;
        else if (!in_array($sorting_field, array_keys($this->product_category['natural_fields']))) {
            $table_fields = db_query("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME`='field_data_$sorting_field'")->fetchAll();
            $foregin_key_relation = end($table_fields);

            $join = new views_join();
            $join->table = "field_data_$sorting_field";
            $join->field = 'entity_id';
            $join->left_table = 'node';
            $join->left_field = 'nid';
            $join->type = 'LEFT';

            $param['join'] = $join;
            $param['field'] = $foregin_key_relation->COLUMN_NAME;
        }
        return $param;
    }
}