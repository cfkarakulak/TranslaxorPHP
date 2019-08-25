<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Handlers;

use Slim\Handlers\NotAllowed;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotAllowedHandler extends NotAllowed {

  public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods) {
    return $response->withJson([
      'error' => 'Not Allowed.',
    ])->withStatus(405);
  }

}