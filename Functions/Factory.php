<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Functions;

use App\Functions\Data;

class Factory {

  public static function data(){
    return new Data;
  }

}