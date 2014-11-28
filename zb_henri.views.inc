<?php

function zb_henri_views_query_alter(&$view, &$query) {
    module_load_include('php', 'zb_henri', '/classes/sorting');
    $sorting = new sorting();
    $term = taxonomy_term_load($view->args[0]);
    
    if ($view->name == 'taxonomy_term'
        && $term->vocabulary_machine_name == $sorting->product_line['taxonomy_name']){
        $parameters = $sorting->getProductLineSortingParam($term);
        if(null == $parameters) return;
        $join = $parameters['join'];
        if(null != $join) $query->add_relationship($join->table, $join, $join->left_table);
        $query->orderby[0] = array(
            'field' => $parameters['field'],
            'direction' => $parameters['direction']
        );
    }else if($view->name == $sorting->product_category['views_machine_name']){
        ## SUSPEND
//        $parameters = $sorting->getProductCategorySortingParam($term);
//        if(null == $parameters) return;
//        $join = $parameters['join'];
//        if(null != $join) $query->add_relationship($join->table, $join, $join->left_table);
//        $query->orderby[0] = array(
//            'field' => $parameters['field'],
//            'field' => 'field_pline_1term_level_tid',
//            'direction' => $parameters['direction']
//        );
    }
}