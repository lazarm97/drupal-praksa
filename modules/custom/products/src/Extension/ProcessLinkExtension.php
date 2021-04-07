<?php

namespace Drupal\products\Extension;
use Twig_Extension;

class ProcessLinkExtension extends Twig_Extension{

  public function getFunctions()
  {
    return [
      new \Twig_SimpleFunction('process_link', array($this, 'process_link'))
    ];
  }

  public function process_link($link_field)
  {
    return [
      '#name' => $link_field['#items']->title,
      '#url' => $link_field['#items']->first()->getUrl()->toString()
    ];;
  }
}
