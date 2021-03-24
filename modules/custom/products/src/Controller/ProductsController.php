<?php

namespace Drupal\products\Controller;

class ProductsController{
  public function products(){
//    $element = array(
//      '#theme' => 'products-template',
//      '#test_var' => 'test'
//    );
//    return $element;
    return [
      '#theme' => 'products-template',
      '#test_var' => 'test',
    ];
  }
}
