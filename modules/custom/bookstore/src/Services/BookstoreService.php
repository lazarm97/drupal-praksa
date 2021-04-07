<?php

namespace Drupal\bookstore\Services;

use Drupal\Core\Entity\EntityTypeManager;

class BookstoreService{

  protected $node;
  protected $comment;
  protected $entity_type_manager;

  public function __construct(){
//    $this->entity_type_manager = $entity_type_manager;
    $this->entity_type_manager = \Drupal::entityTypeManager();
    $this->node = $this->entity_type_manager->getStorage('node');
    $this->comment = $this->entity_type_manager->getStorage('comment');
  }

  private function getBooksFromXml(){
    $xml_string = file_get_contents("https://www.chilkatsoft.com/xml-samples/bookstore.xml");
    $xml = simplexml_load_string($xml_string);
    $json = json_encode($xml);
    return json_decode($json, true);
  }

  public function showBooks(){
    $books = $this->getBooksFromXml();

    foreach ($books['book'] as $book){
      $book_entity = $this->node->create(
        [
          'type' => 'book',
          'title' => $book['title'],
          'field_title' => $book['title'],
          'field_isbn' => $book['@attributes']['ISBN'],
          'field_price' => $book['price']
        ]
      );
      $this->node->save($book_entity);

      if(isset($book['comments']) && is_array($book['comments']['userComment']))
        $comments = $book['comments']['userComment'];
      elseif (isset($book['comments']))
        $comments = [$book['comments']['userComment']];
      else
        $comments = [];

      if(count($comments) > 0){
        foreach ($comments as $comment){
          $comment_entity = $this->comment->create(
            [
              'entity_type' => $book_entity->getEntityTypeId(),
              'entity_id' => $book_entity->id(),
              'field_name' => 'field_comments',
              'uid' => 0,
              'comment_type' => 'comment',
              'subject' => 'Nesto',
              'comment_body' => $comment
            ]
          );
          $this->comment->save($comment_entity);
        }
      }
    }
    $books_arr = $this->getBooks();
    return $books_arr;
  }

  public function getBooks(){
    $ids = $this->node->getQuery()
      ->condition('type', 'book')
      ->execute();
    return $this->node->loadMultiple($ids);
  }
}

