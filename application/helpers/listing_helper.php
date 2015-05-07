<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function getStartIndex ( $total , $page = 1 , $recrod_limit = 20 ) {
    
    $start_index = ($page-1) * $recrod_limit;
    return $start_index;
}

function getArrayForJGrid ( $list_data , $primary_key , $page , $total_pages , $total_records) {
    
    $return_array = Array();
    $return_array['page'] = $page;
    $return_array['total'] = $total_pages;
    $return_array['records'] = $total_records;
    for ( $i = 0; $i < count($list_data); $i++ ) {
        $arr = Array();
        $arr['id'] = $list_data[$i][$primary_key];
        foreach ($list_data[$i] as $key => $val ) {
            if ( $key != $primary_key) {
                $arr['cell'][] = $val;
            }
        }
        $return_array['rows'][] = $arr;
    }
    return $return_array;
}

function getTotalPages($total_records , $records_per_page) {
    $total_pages = ceil($total_records/$records_per_page);
    return $total_pages;
}