<?php

function zb_henri_views_query_alter(&$view, &$query) {
    module_load_include('php', 'zb_henri', '/classes/sorting');
    $sorting = new sorting();
    $term = taxonomy_term_load($view->args[0]);

    if ($view->name == 'taxonomy_term') {
        if ($term->vocabulary_machine_name == $sorting->product_line['taxonomy_name'] ||
                $term->vocabulary_machine_name == $sorting->product_category['taxonomy_name']) {
            $taxonomy = $term->vocabulary_machine_name == $sorting->product_line['taxonomy_name']?$sorting->product_line:$sorting->product_category;
            $parameters = $sorting->getSortingParameter($term, $taxonomy);
            if (null == $parameters) return;
            $join = $parameters['join'];
            if (null != $join) $query->add_relationship($join->table, $join, $join->left_table);
            $query->orderby[0] = array(
                'field' => $parameters['field'],
                'direction' => $parameters['direction']
            );
        }
    }
}