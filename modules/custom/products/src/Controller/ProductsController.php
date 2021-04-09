<?php

namespace Drupal\products\Controller;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Pager\PagerManager;
use Drupal\Core\Render\Renderer;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductsController extends ControllerBase {

  private $entity_type_manager;
  private $config;
  private $pager_manager;
  private $renderer;
  private $items_per_page;

  public function __construct(EntityTypeManager $entityTypeManager, ConfigFactory $config, PagerManager $pagerManager, Renderer $renderer){
    $this->entity_type_manager = $entityTypeManager;
    $this->config = $config;
    $this->pager_manager = $pagerManager;
    $this->renderer = $renderer;
    $this->items_per_page = $config->get('products.settings')->get('items_per_page');
  }

  public static function create(ContainerInterface $container){
    $entity_type_manager = $container->get('entity_type.manager');
    $config = $container->get('config.factory');
    $pager_manager = $container->get('pager.manager');
    $renderer = $container->get('renderer');
    return new static($entity_type_manager, $config, $pager_manager, $renderer);
  }

  public function products(){
    $entity_storage_node = $this->entity_type_manager->getStorage('node');
    $products_nodes_query = $entity_storage_node->getQuery()
      ->condition('type', 'bike');
    if(isset($_GET['filter_title']) && $_GET['filter_title'] != "")
      $products_nodes_query->condition('title', $_GET['filter_title'], 'CONTAINS');
    if(isset($_GET['filter_terms']) && $_GET['filter_terms'] != "" && $_GET['filter_terms'] != 0)
      $products_nodes_query->condition('field_content_product_tags',$_GET['filter_terms'],'CONTAINS');
    $products_nids = $this->createPagination($products_nodes_query);
    $products = $entity_storage_node->loadMultiple($products_nids);
    //Get products
    $products_info = $this->getProducts($products);
    $pager = [
      '#type' => 'pager'
    ];
    //Get all terms
    $term_data = $this->getAllTerms();
    $pagination['suffix']['#markup'] = '</ol>'. $this->renderer->render($pager);
    return [
      '#theme' => 'products-template',
      '#products' => $products_info,
      '#pagination' => $pagination,
      '#terms' => $term_data
    ];
  }

  public function parent(){
    $products_nodes_query = $this->entity_type_manager->getStorage('node')->getQuery()
      ->condition('type', 'bike')
      ->condition('field_parent',NULL, 'IS NULL');
    $products_nids = $this->createPagination($products_nodes_query);
    $products = $this->entity_type_manager->getStorage('node')->loadMultiple($products_nids);
    $products_info = $this->getProducts($products);
    $pager = [
      '#type' => 'pager'
    ];
    //Get all terms
    $term_data = $this->getAllTerms();
    $pagination['suffix']['#markup'] = '</ol>'. $this->renderer->render($pager);
    return [
      '#theme' => 'products-template',
      '#products' => $products_info,
      '#pagination' => $pagination,
      '#terms' => $term_data
    ];
  }

  private function getProducts($products){
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
    return $products_info;
  }

  private function getAllTerms(){
    $term_ids = $this->entity_type_manager->getStorage('taxonomy_term')->getQuery()->execute();
    $terms = $this->entity_type_manager->getStorage('taxonomy_term')->loadMultiple($term_ids);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[] = array(
        'id' => $term->tid->value,
        'name' => $term->name->value
      );
    }
    return $term_data;
  }

  private function createPagination($query){
    $nids = $query->execute();
    $this->pager_manager->createPager(count($nids),$this->items_per_page); //Create global variable pager for render pagination
    $query->pager($this->items_per_page); //Create condition for products per page
    return $query->execute();//1
  }
}
