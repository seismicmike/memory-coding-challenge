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
    if (!$request) {
      $request = $this->requestStack->getCurrentRequest();
    }

    // @todo Validate the request.
    return TRUE;
  }

}
