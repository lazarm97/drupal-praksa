<?php

namespace Drupal\bookstore\Controller;

use Drupal\bookstore\Services\BookstoreService;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class BookstoreController{

  protected $bookstore_service;
  protected $entity_type_manager;

  public function __construct(){
    $this->bookstore_service = new BookstoreService();
//    $this->bookstore_service = \Drupal::service('bookstore.Books');
    $this->entity_type_manager = \Drupal::entityTypeManager()->getStorage('comment');
  }

//  public function __construct(EntityTypeManagerInterface $entityTypeManager){
//    $this->entity_type_manager = $entityTypeManager;
//  }

  public function books(){
    $books_entity = $this->bookstore_service->showBooks();
    foreach ($books_entity as $entity){
      $cids = $this->entity_type_manager
        ->getQuery()
        ->condition('entity_id', $entity->id())
        ->execute();
      $comments = $this->entity_type_manager->loadMultiple($cids);
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
