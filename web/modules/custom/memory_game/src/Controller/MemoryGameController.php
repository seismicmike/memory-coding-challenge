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
    // Initialize the status code and a response placeholder.
    $status = 400;
    $response = [
      'meta' => [
        'success' => FALSE,
        'message' => 'Invalid request',
      ],
      'data' => [],
    ];

    // Check the request for validity.
    if (!$this->requestValidator->validateRequest($request)) {
      // If the request is invalid, retrieve the error message to report
      // back to the user.
      $response['meta']['message'] = $this->requestValidator->validationError;
    }
    else {
      // If the request is valid, set the status code, generate the grid, and
      // update the output.
      //
      // Retrieve inputs from the request.
      $rows = $request->query->get('rows');
      $columns = $request->query->get('columns');

      // Generate the grid.
      $grid_generator = new GridGenerator();
      $response['data']['cards'] = $grid_generator->generateGrid($rows, $columns);

      // Update the response status and message.
      $status = 200;
      $response['meta']['success'] = TRUE;
      unset($response['meta']['message']);

      // Add metadata to the output.
      $response['meta']['cardCount'] = $grid_generator->getCardCount();
      $response['meta']['uniqueCardCount'] = $grid_generator->getUniqueCardCount();
      $response['meta']['uniqueCards'] = $grid_generator->getUniqueCards();
    }

    // Return the response as JSON.
    return new JsonResponse($response, $status);
  }

}
