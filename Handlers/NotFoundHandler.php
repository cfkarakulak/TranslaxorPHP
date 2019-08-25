<?php

/**
 * @package Translaxor Project
 * @author Cemre Fatih Karakulak <cradexco@gmail.com>
 */

namespace App\Handlers;

use Slim\Handlers\NotFound;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler extends NotFound {

  public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
    return $response->withJson([
      'error' => 'Not Found.',
    ])->withStatus(404);
  }

}