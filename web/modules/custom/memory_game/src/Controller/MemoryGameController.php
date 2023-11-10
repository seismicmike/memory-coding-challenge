<?php

namespace Drupal\memory_game\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\memory_game\GridGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Memory game routes.
 */
class MemoryGameController extends ControllerBase {

  /**
   * The memory_game.grid_generator service.
   *
   * @var \Drupal\memory_game\GridGenerator
   */
  protected $gridGenerator;

  /**
   * The controller constructor.
   *
   * @param \Drupal\memory_game\GridGenerator $grid_generator
   *   The memory_game.grid_generator service.
   */
  public function __construct(GridGenerator $grid_generator) {
    $this->gridGenerator = $grid_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('memory_game.grid_generator')
    );
  }

  /**
   * Builds the response.
   */
  public function build(Request $request) {
    $response = $this->gridGenerator->generateGridFromRequest($request);
    $response_code = ($response['meta']['success']) ? 200 : 400;

    // Return the response as JSON.
    return new JsonResponse($response, $response_code);
  }

}
