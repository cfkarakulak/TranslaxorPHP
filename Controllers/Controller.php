<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Controllers;

use Interop\Container\ContainerInterface;

abstract class Controller {

  protected $container;
    
  public function __construct(ContainerInterface $container){
    $this->container = $container;
  }

  public function __get($property){
    if($this->container->get($property)){
      return $this->container->get($property);
    }
  }
  
}