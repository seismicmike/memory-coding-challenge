<?php

namespace Drupal\memory_game;

use Symfony\Component\HttpFoundation\Request;

/**
 * The GridGenerator creates a grid of cards.
 *
 * A grid has an even number of cards. Each card has exactly one match in the
 * grid. The order of cards is random.
 */
class GridGenerator implements GridGeneratorInterface {


  /**
   * The request validator.
   *
   * @var \Drupal\memory_game\RequestValidatorInterface
   */
  protected $requestValidator;

  /**
   * Create a GridGenerator.
   *
   * @param \Drupal\memory_game\RequestValidatorInterface $request_validator
   *   The request validator to use to make sure the inputs are valid.
   */
  public function __construct(RequestValidatorInterface $request_validator) {
    $this->requestValidator = $request_validator;
  }

  /**
   * {@inheritdoc}
   */
  public function generateGridFromRequest(Request $request): array {
    // Validate the request.
    $this->requestValidator->validateRequest($request);

    $response = [
      'meta' => [
        'success' => $this->requestValidator->isValid(),
        'message' => $this->requestValidator->getAccessResultMessage(),
      ],
      'data' => [],
    ];

    if ($response['meta']['success']) {
      // If the request is valid, set the status code, generate the grid, and
      // update the output.
      unset($response['meta']['message']);

      // Retrieve inputs from the request and generate the grid.
      $rows = (int) $request->query->get('rows');
      $columns = (int) $request->query->get('columns');
      $grid = $this->generateGrid($rows, $columns);

      $response['meta']['cardCount'] = $grid->getCardCount();
      $response['meta']['uniqueCardCount'] = $grid->getUniqueCardCount();
      $response['meta']['uniqueCards'] = $grid->getUniqueCards();
      $response['data']['cards'] = $grid->getGrid();
    }

    return $response;
  }

  /**
   * Generate a game grid.
   *
   * @param int $rows
   *   The number of rows. Must be a positive integer. $rows * $columns must be
   *   a positive even integer.
   * @param int $columns
   *   The number of columns. Must be a positive integer. $rows * $columns must
   *   be a positive even integer.
   *
   * @return CardGridInterface
   *   The card grid object.
   */
  protected function generateGrid(int $rows, int $columns): CardGridInterface {
    $grid = new CardGrid($rows, $columns);
    return $grid->generateGrid();
  }

}
