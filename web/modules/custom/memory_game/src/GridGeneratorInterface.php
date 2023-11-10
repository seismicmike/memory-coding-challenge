<?php

namespace Drupal\memory_game;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for grid generators.
 */
interface GridGeneratorInterface {

  /**
   * Generate a grid from a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request to generate the grid for.
   *
   * @return array
   *   An array with the grid and metadata. If the request is invalid, this
   *   will return a meta array with success set to false and a message as to
   *   the reason.
   */
  public function generateGridFromRequest(Request $request): array;

}
