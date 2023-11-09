<?php

namespace Drupal\memory_game\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\memory_game\GridGenerator;
use Drupal\memory_game\RequestValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Memory game routes.
 */
class MemoryGameController extends ControllerBase {

  /**
   * The memory_game.request_validator service.
   *
   * @var \Drupal\memory_game\RequestValidator
   */
  protected $requestValidator;

  /**
   * The controller constructor.
   *
   * @param \Drupal\memory_game\RequestValidator $request_validator
   *   The memory_game.request_validator service.
   */
  public function __construct(RequestValidator $request_validator) {
    $this->requestValidator = $request_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('memory_game.request_validator')
    );
  }

  /**
   * Builds the response.
   */
  public function build(Request $request) {
    // $rows = $request->query->get('rows');
    // $columns = $request->query->get('columns');
    $rows = 4;
    $columns = 6;
    $grid_generator = new GridGenerator();
    $grid = $grid_generator->generateGrid($rows, $columns, TRUE);

    $response = [
      'meta' => [
        'success' => TRUE,
        'cardCount' => $grid_generator->getCardCount(),
        'uniqueCardCount' => $grid_generator->getUniqueCardCount(),
        'uniqueCards' => $grid_generator->getUniqueCards(),
      ],
      'data' => [
        'cards' => $grid,
      ],
    ];

    return new JsonResponse($response);
  }

}
