<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//Bacth Function for deleting a node
function server_json_index_batch_process($info, $context)
{
    // check if numeric

    try {

        $object = \Drupal::entityTypeManager()->getStorage('node')->load($info['nid']);
        if (is_object($object)) {
            $product =     \Drupal::service('product_json')->productItem($object);
            if($info['action'] == 'delete'){
                $response = \Drupal::service('product_json')->deleteItem('products',$product) ;
            }
            if($info['action'] == 'build'){
                $response = \Drupal::service('product_json')->createItem('products',$product) ;
            }
            if($info['action'] == 'update'){
                $response = \Drupal::service('product_json')->updateItem('products',$product) ;
            }
            drush_print("Total:".$info['total']."/".$info['index'].",nid=".$info['nid']." ".$response );
        }
    } catch (\Exception $e) {
        drush_print($e->getMessage());
    }

}
