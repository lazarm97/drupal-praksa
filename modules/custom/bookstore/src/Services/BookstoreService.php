<?php

namespace Drupal\bookstore\Services;

use Drupal\Core\Entity\EntityTypeManager;
use GuzzleHttp\Client;

class BookstoreService{

  protected $entity_type_manager;
  protected $http_client;

  public function __construct(EntityTypeManager $entityTypeManager, Client $client){
    $this->entity_type_manager = $entityTypeManager;
    $this->http_client = $client;
  }

  public function showBooks(){
    $books_xml = new \SimpleXMLElement($this->http_client->get("https://www.chilkatsoft.com/xml-samples/bookstore.xml")->getBody()->getContents());
    $books = json_decode(json_encode($books_xml),true);
    foreach ($books['book'] as $book){
      $book_entity = $this->entity_type_manager->getStorage('node')->create(
        [
          'type' => 'book',
          'title' => $book['title'],
          'field_title' => $book['title'],
          'field_isbn' => $book['@attributes']['ISBN'],
          'field_price' => $book['price']
        ]
      );
      $this->entity_type_manager->getStorage('node')->save($book_entity);

      if(isset($book['comments']) && is_array($book['comments']['userComment']))
        $comments = $book['comments']['userComment'];
      elseif (isset($book['comments']))
        $comments = [$book['comments']['userComment']];
      else
        $comments = [];

      if(count($comments) > 0){
        foreach ($comments as $comment){
          $comment_entity = $this->entity_type_manager->getStorage('comment')->create(
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
          $this->entity_type_manager->getStorage('comment')->save($comment_entity);
        }
      }
    }
    $books_arr = $this->getBooks();
    return $books_arr;
  }

  public function getBooks(){
    $ids = $this->entity_type_manager->getStorage('node')->getQuery()
      ->condition('type', 'book')
      ->execute();
    return $this->entity_type_manager->getStorage('node')->loadMultiple($ids);
  }
}

