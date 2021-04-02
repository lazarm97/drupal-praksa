<?php

namespace Drupal\products\Extension;
use Drupal\Core\Url;
use Twig_Extension;

class ProcessLinkExtension extends Twig_Extension{

  public function getFunctions()
  {
    return [
      new \Twig_SimpleFunction('href_link', array($this, 'processLink'))
    ];
  }

  public function processLink($link_field)
  {
    $link_uri = $link_field['#object']->field_link->uri;
    $link_url = Url::fromUri($link_uri)->toString();
    return [
      '#name' => $link_field['#object']->field_link->title,
      '#url' => $link_url
    ];;
  }
}
