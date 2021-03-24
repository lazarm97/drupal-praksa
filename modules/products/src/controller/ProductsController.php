<?php

namespace Drupal\products\Controller;

class ProductsController{
  public function products(){
    return array(
      '#title' => 'Products for sale',
      '#markup' => 'Some products....'
    );
  }
}
