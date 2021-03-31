<?php

namespace Drupal\products\Controller;
use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

class ProductsController{
  public function products(){
    $items_per_page = \Drupal::config('products.settings')->get('items_per_page');
    $entity_storage_node = \Drupal::entityTypeManager()->getStorage('node');
    $entity_storage_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $term_nodes_query = $entity_storage_term->getQuery();
    $products_nodes_query = $entity_storage_node->getQuery()
      ->condition('type', 'bike');
    if(isset($_GET['filter_title']) && $_GET['filter_title'] != "")
      $products_nodes_query->condition('title', $_GET['filter_title'], 'CONTAINS');
    if(isset($_GET['filter_terms']) && $_GET['filter_terms'] != "" && $_GET['filter_terms'] != 0)
      $products_nodes_query->condition('field_content_product_tags',$_GET['filter_terms'],'CONTAINS');
    $products_nids = $products_nodes_query->execute();
    $term_ids = $term_nodes_query->execute();
    \Drupal::service('pager.manager')->createPager(count($products_nids),$items_per_page);
    $products_nodes_query->pager($items_per_page);
    $products_nids = $products_nodes_query->execute();
    $products = $entity_storage_node->loadMultiple($products_nids);
    $terms = Term::loadMultiple($term_ids);
    $products_info = array();
    foreach ($products as $product){
      $product = $product->toArray();
      $image_id = $product['field_image'][0]['target_id'];
      $image = File::load($image_id)->toArray();
      $image_uri = $image['uri'][0]['value'];
      $slides_ids = [];
      $slides = Paragraph::load($product['field_product_slider'][0]['target_id'])->toArray();
      foreach ($slides['field_slides'] as $slide){
        array_push($slides_ids,$slide['target_id']);
      }
      $slides = Paragraph::loadMultiple($slides_ids);
      $slide_data = [];
      foreach ($slides as $slide){
        $slide = $slide->toArray();
        $slide_image_id = $slide['field_image'][0]['target_id'];
        $slide_image = File::load($slide_image_id)->toArray();
        $slide_data[] = array(
          'title' => $slide['field_title'][0]['value'],
          'body' => $slide['field_body'][0]['value'],
          'image' => $slide_image['uri'][0]['value']
        );
      }
      $term_data = [];
      foreach ($product['field_content_product_tags'] as $term){
        $term_data[] = array(
          'id' => $term['target_id'],
          'name' => t(Term::load($term['target_id'])->get('name')->value)
        );
      }
      $tmp_info = ['product' => [
        'title' => t($product['field_title'][0]['value']),
        'description' => t($product['body'][0]['value']),
        'image' => $image_uri,
        'terms' => $term_data,
        'slider' => $slide_data
      ]];
      array_push($products_info,$tmp_info);
    }
    $pager = [
      '#type' => 'pager'
    ];
    $term_data = [];
    foreach ($terms as $term) {
      $term = $term->toArray();
      $term_data[] = array(
        'id' => $term['tid'][0]['value'],
        'name' => t($term['name'][0]['value'])
      );
    }
    $pagination['suffix']['#markup'] = '</ol>'. \Drupal::service('renderer')->render($pager);
    return [
      '#theme' => 'products-template',
      '#products' => $products_info,
      '#pagination' => $pagination,
      '#terms' => $term_data
    ];
  }
}
