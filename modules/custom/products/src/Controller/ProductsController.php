<?php

namespace Drupal\products\Controller;
use Drupal\file\Entity\File;

class ProductsController{
  public function products(){
    $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
    $title_filter = $_GET['filter_title'];
    $products_nodes_ids = $entity_storage->getQuery()
      ->condition('type', 'bike')
      ->condition('title', $title_filter, 'CONTAINS')
      ->execute();
    $products = $entity_storage->loadMultiple($products_nodes_ids);
    $products_info = array();
    foreach ($products as $product){
      $product = $product->toArray();
      $image_id = $product['field_image'][0]['target_id'];
      $image = File::load($image_id)->toArray();
      $image_uri = $image['uri'][0]['value'];
      $tmp_info = ['product' => [
        'title' => $product['field_title'][0]['value'],
        'description' => $product['body'][0]['value'],
        'image' => $image_uri,
        'filter' => $title_filter
      ]];
      array_push($products_info,$tmp_info);
    }
    return [
      '#theme' => 'products-template',
      '#products' => $products_info
    ];
  }
}
