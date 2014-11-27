<?php

function henri_views_query_alter(&$view, &$query) {
    module_load_include('php', 'henri', '/classes/sorting');
    $sorting = new sorting();
    $term = taxonomy_term_load($view->args[0]);
    if ($view->name == 'taxonomy_term'
        && $term->vocabulary_machine_name == $sorting->product_line['taxonomy_name']){
        $parameters = $sorting->getProductLineSortingParam($term);
        $join = $parameters['join'];
        $query->add_relationship($join->table, $join, $join->left_table);
        $query->orderby[0] = array(
            'field' => $parameters['field'],
            'direction' => $parameters['direction']
        );
    }
}