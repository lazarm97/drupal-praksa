<?php

namespace Drupal\products\Controller;
use Drupal\file\Entity\File;

class ProductsController{
  public function products(){
    $image_local_path = "http://localhost/drupal-praksa/sites/default/files/";
    $entity_type_manager = \Drupal::entityTypeManager();
//    $nodes = Node::load();
    $products_nodes = $entity_type_manager->getStorage('node')->loadByProperties(['type' => 'bike']);
//    $image = $products_nodes[1]->getValue();
    $products_info = array();
    foreach ($products_nodes as $product){
      $product = $product->toArray();
      $image_id = $product['field_image'][0]['target_id'];
      $image = File::load($image_id)->toArray();
      $image_uri = $image['uri'][0]['value'];
      $image_uri = substr($image_uri,9);
      $image_path = $image_local_path.$image_uri;
      $tmp_info = ['product' => [
        'title' => $product['field_title'][0]['value'],
        'description' => $product['body'][0]['value'],
        'image' => $image_path
      ]];
      array_push($products_info,$tmp_info);
    }
    return [
      '#theme' => 'products-template',
      '#products' => $products_info
    ];
  }
}
