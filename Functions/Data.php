<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Functions;

class Data {

  /**
   * Variable get helper
   * @param  Variable $var [Referenced]
   * @param  Mixed $default
   * @return [mixed]
   */
  public static function get(&$var, $default = null) {
    return isset($var) ? $var : $default;
  }

}