<?php

namespace Drupal\products\Controller;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

class ProductsController{
  public function products(){
    $items_per_page = \Drupal::config('products.settings')->get('items_per_page');
    $entity_storage_node = \Drupal::entityTypeManager()->getStorage('node');
    $entity_storage_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    $products_nodes_query = $entity_storage_node->getQuery()
      ->condition('type', 'bike');
    if(isset($_GET['filter_title']) && $_GET['filter_title'] != "")
      $products_nodes_query->condition('title', $_GET['filter_title'], 'CONTAINS');
    if(isset($_GET['filter_terms']) && $_GET['filter_terms'] != "" && $_GET['filter_terms'] != 0)
      $products_nodes_query->condition('field_content_product_tags',$_GET['filter_terms'],'CONTAINS');
    $products_nids = $products_nodes_query->execute();

    \Drupal::service('pager.manager')->createPager(count($products_nids),$items_per_page);
    $products_nodes_query->pager($items_per_page);
    $products_nids = $products_nodes_query->execute();
    $products = $entity_storage_node->loadMultiple($products_nids);

    $products_info = array();


    foreach ($products as $product){
      $slider = $product->field_product_slider->entity;
      $slide_data = [];
      foreach ($slider->field_slides as $slide){
        $slide_data[] = array(
          'title' => $slide->entity->field_title->value,
          'body' => $slide->entity->field_body->value,
          'image' => $slide->entity->field_image->entity->uri->value
        );
      }
      $terms = $product->field_content_product_tags;
      $term_data = [];
      foreach ($terms as $term){
        $term_data[] = array(
          'id' => $term->entity->tid->value,
          'name' => $term->entity->name->value
        );
      }
      $tmp_info = [
        'title' => $product->title->value,
        'description' => $product->body->value,
        'image' => $product->field_image->entity->uri->value,
        'terms' => $term_data,
        'slider' => $slide_data
      ];
      array_push($products_info,$tmp_info);
    }
    $pager = [
      '#type' => 'pager'
    ];
    $term_ids = $entity_storage_term->getQuery()->execute();
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
