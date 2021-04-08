<?php

namespace Drupal\bookstore\Controller;

use Drupal\bookstore\Services\BookstoreService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookstoreController extends ControllerBase {

  protected $bookstore_service;
  protected $entity_type_manager;

  public function __construct(BookstoreService $service, EntityTypeManager $entityTypeManager){
    $this->bookstore_service = $service;
    $this->entity_type_manager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container){
    $bookstore_service = $container->get('bookstore.Books');
    $entity_type_manager = $container->get('entity_type.manager');
    return new static($bookstore_service, $entity_type_manager);
  }

  public function books(){
    $books_entity = $this->bookstore_service->showBooks();
    foreach ($books_entity as $entity){
      $cids = $this->entity_type_manager->getStorage('comment')
        ->getQuery()
        ->condition('entity_id', $entity->id())
        ->execute();
      $comments = $this->entity_type_manager->getStorage('comment')->loadMultiple($cids);
      $comments_arr = [];
      foreach ($comments as $comment){
        $comments_arr[] = array(
          'body' => $comment->comment_body->value
        );
      }
      $content[] = array(
        'title' => $entity->field_title->value,
        'price' => $entity->field_price->value,
        'isbn' => $entity->field_isbn->value,
        'comments' => $comments_arr
      );
    }
    return [
      '#theme' => 'bookstore-template',
      '#books' => $content
    ];
  }
}
