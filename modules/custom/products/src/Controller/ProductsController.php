<?php

namespace Drupal\products\Controller;
use Drupal\file\Entity\File;

class ProductsController{
  public function products(){
    $items_per_page = \Drupal::config('products.settings')->get('items_per_page');
    $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
    $title_filter = "";
    if(isset($_GET['filter_title']))
      $title_filter = $_GET['filter_title'];
    $products_nodes_query = $entity_storage->getQuery()
      ->condition('type', 'bike')
      ->condition('title', $title_filter, 'CONTAINS');
    $products_nids = $products_nodes_query->execute();
    \Drupal::service('pager.manager')->createPager(count($products_nids),$items_per_page);
    $products_nodes_query->pager($items_per_page);
    $products_nids = $products_nodes_query->execute();
    $products = $entity_storage->loadMultiple($products_nids);
    $products_info = array();
    foreach ($products as $product){
      $product = $product->toArray();
      $image_id = $product['field_image'][0]['target_id'];
      $image = File::load($image_id)->toArray();
      $image_uri = $image['uri'][0]['value'];
      $tmp_info = ['product' => [
        'title' => $product['field_title'][0]['value'],
        'description' => $product['body'][0]['value'],
        'image' => $image_uri
      ]];
      array_push($products_info,$tmp_info);
    }
    $pager = [
      '#type' => 'pager'
    ];
    $pagination['suffix']['#markup'] = '</ol>'. \Drupal::service('renderer')->render($pager);
    return [
      '#theme' => 'products-template',
      '#products' => $products_info,
      '#pagination' => $pagination
    ];
  }
}
