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
        'sorting_field' => 'field_tpl_1f_sorting',
        'sorting_type_field' => 'field_tpl_1f_sortype',
    );
    var $product_category = array(
        'taxonomy_name' => 'zb_product_category',
        'sorting_field' => 'field_tc_1f_sorting',
        'sorting_type_field' => 'field_tc_1f_sorttype',
    );
    var $product_display = array(
        'content_type_name' => 'zb_product_display',
        'natural_fields' => array('title' => 'Product Display Title')
    );

    function updateSortingOptions() {
        $options = $this->getProductDisplayFields();
        $this->updateCategorySortingOption($options);
        $this->updateProductLineSortingOption($options);
        drupal_set_message('Sorting options updated successfully'.  json_encode($options));
    }

    /*
     * preparing parameters for sorting pusposes in CUSTOM_MODULE_NAME.views.inc
     * @arg term object, taxonomy config ($product_line or $product_category )
     * @return array containing : 
     *      views_join object
     *      sorting base (field name)
     *      sorting type (ASC/DESC)
     */
    function getSortingParameter($term, $taxonomy) {
        $retrieved_field = field_get_items('taxonomy_term', $term, $taxonomy['sorting_field']);
        if(!$retrieved_field) return null;
        else $sorting_field = $retrieved_field[0]['value'];
        $retrieved_field = field_get_items('taxonomy_term', $term, $taxonomy['sorting_type_field']);
        $sorting_type = $retrieved_field[0]['value'] ? 'ASC' : 'DESC';

        $param = array(
            'join' => null,
            'field' => $sorting_field,
            'direction' => $sorting_type,
        );

        if (!in_array($sorting_field, array_keys($this->product_display['natural_fields']))) {
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
    
    /*
     * retrieve all fields of product display content type
     * @return array field_name => field label
     */
    function getProductDisplayFields(){
        $field_list = array();
        foreach (field_info_instances('node', $this->product_display['content_type_name']) as $key => $value)
            $field_list[$key] = $value['label'];
        foreach($this->product_display['natural_fields'] as $field => $label)
            $field_list[$field] = $label;
        return $field_list;
    }
    
    /*
     * provide list of product display fields as sorting option in each product category
     * @arg list of options as array
     * @action update field setting
     */
    function updateCategorySortingOption($options){
        $info = field_info_field($this->product_category['sorting_field']);
        $values = &$info['settings']['allowed_values'];
        $values = $options;
        field_update_field($info);
    }
    
    /*
     * provide list of product display fields as sorting option in each Product Line
     * @arg list of options as array
     * @action update field setting
     */
    function updateProductLineSortingOption($options){
        $info = field_info_field($this->product_line['sorting_field']);
        $values = &$info['settings']['allowed_values'];
        $values = $options;
        field_update_field($info);
    }
}