<?php

namespace Drupal\products\Controller;
use Drupal\taxonomy\Entity\Term;

class ProductsController{
  public function products(){
    $items_per_page = \Drupal::config('products.settings')->get('items_per_page');
    $entity_storage_node = \Drupal::entityTypeManager()->getStorage('node');
    $products_nodes_query = $entity_storage_node->getQuery()
      ->condition('type', 'bike');
    if(isset($_GET['filter_title']) && $_GET['filter_title'] != "")
      $products_nodes_query->condition('title', $_GET['filter_title'], 'CONTAINS');
    if(isset($_GET['filter_terms']) && $_GET['filter_terms'] != "" && $_GET['filter_terms'] != 0)
      $products_nodes_query->condition('field_content_product_tags',$_GET['filter_terms'],'CONTAINS');
    $products_nids = $products_nodes_query; //2
    \Drupal::service('pager.manager')->createPager(count($products_nids),$items_per_page); //Create global variable pager for render pagination
    $products_nodes_query->pager($items_per_page); //Create condition for products per page
    $products_nids = $products_nodes_query->execute();//1
    $products = $entity_storage_node->loadMultiple($products_nids);
    $products_info = array();
    foreach ($products as $product){
      //Get slider
      $slide_data = [];
      foreach ($product->field_product_slider->entity->field_slides as $slide){
        $slide_data[] = array(
          'title' => $slide->entity->field_title->value,
          'body' => $slide->entity->field_body->value,
          'image' => $slide->entity->field_image->entity->uri->value
        );
      }
      //Get terms for product
      $term_data = [];
      foreach ($product->field_content_product_tags as $term){
        $term_data[] = array(
          'id' => $term->entity->tid->value,
          'name' => $term->entity->name->value
        );
      }
      array_push($products_info,
        [
          'title' => $product->title->value,
          'description' => $product->body->value,
          'image' => $product->field_image->entity->uri->value,
          'terms' => $term_data,
          'slider' => $slide_data
        ]
      );
    }
    $pager = [
      '#type' => 'pager'
    ];
    //Get all terms
    $term_ids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()->execute();
    $terms = Term::loadMultiple($term_ids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[] = array(
        'id' => $term->tid->value,
        'name' => $term->name->value
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
