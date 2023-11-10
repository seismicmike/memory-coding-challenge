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

    $rows = $request->query->get('rows');
    $columns = $request->query->get('columns');

    if (empty($rows)) {
      $this->validationError = '`rows` is required.';
      return FALSE;
    }

    if (empty($columns)) {
      $this->validationError = '`columns` is required.';
      return FALSE;
    }

    // Make sure rows is a positive integer between 1 and 6.
    if (!preg_match('/^[1-6]$/', $rows)) {
      $this->validationError = '`rows` must be a positive integer between 1 and 6.';
      return FALSE;
    }

    // Make sure columns is a positive integer between 1 and 6.
    if (!preg_match('/^[1-6]$/', $columns)) {
      $this->validationError = '`columns` must be a positive integer between 1 and 6.';
      return FALSE;
    }

    if (($rows % 2 != 0) && ($columns % 2 != 0)) {
      $this->validationError = 'Either `rows` or `columns` needs to be an even number.';
      return FALSE;
    }

    // This is to catch an possible edge case just in case somehow we get past
    // the previous check while still being able to generate an odd number of
    // cards.
    if (($rows * $columns) % 2 != 0) {
      $this->validationError = 'Requested `rows` and `columns` would generate an odd number of cards.';
      return FALSE;
    }

    // All checks pass. Return true.
    return TRUE;
  }

}
