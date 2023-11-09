<?php

namespace Drupal\memory_game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service description.
 */
class RequestValidator {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The error message from the most recent validation attempt.
   *
   * @var string
   */
  public $validationError = '';

  /**
   * Constructs a RequestValidator object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * Validate a request to make sure that it is a well formed API request.
   *
   * @param ?\Symfony\Component\HttpFoundation\Request $request
   *   The request to validate. If none is provided, the current request
   *   will be used.
   *
   * @return bool
   *   Whether to consider the request valid.
   */
  public function validateRequest(Request $request = NULL): bool {
    $this->validationError = '';
    if (!$request) {
      $request = $this->requestStack->getCurrentRequest();
    }

    $rows = (int) $request->query->get('rows');
    $columns = (int) $request->query->get('columns');

    if (empty($rows) || $rows < 1) {
      $this->validationError = 'Row count is not a positive integer';
      return FALSE;
    }

    if (empty($columns) || $columns < 1) {
      $this->validationError = 'Column count is not a positive integer';
      return FALSE;
    }

    if (($rows % 2 != 0) && ($columns % 2 != 0)) {
      $this->validationError = 'Either `rows` or `columns` needs to be an even number.';
      return FALSE;
    }

    if ($rows > 6) {
      $this->validationError = 'Row count is greater than 6';
      return FALSE;
    }

    if ($columns > 6) {
      $this->validationError = 'Column count is greater than 6';
      return FALSE;
    }

    // This one should never happen with the check above to require at least one
    // even input, but just to be safe.
    if (($rows * $columns) % 2 != 0) {
      $this->validationError = 'Requested `rows` and `columns` would generate an odd number of cards.';
      return FALSE;
    }

    // All checks pass. Return true.
    return TRUE;
  }

}
