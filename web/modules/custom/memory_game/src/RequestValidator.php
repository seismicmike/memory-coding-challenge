<?php

namespace Drupal\memory_game;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service description.
 */
class RequestValidator implements RequestValidatorInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The result of the previous validation attempt.
   *
   * @var \Drupal\Core\Access\AccessResultInterface
   */
  protected $validationResult;

  /**
   * Constructs a RequestValidator object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
    $this->validationResult = new AccessResultForbidden('No validation has been performed.');
  }

  /**
   * Validate a request to make sure that it is a well formed API request.
   *
   * @param ?\Symfony\Component\HttpFoundation\Request $request
   *   The request to validate. If none is provided, the current request
   *   will be used.
   */
  public function validateRequest(Request $request = NULL): self {
    if (!$request) {
      $request = $this->requestStack->getCurrentRequest();
    }

    $rows = $request->query->get('rows');
    $columns = $request->query->get('columns');

    // Make sure we have a rows value.
    if (is_null($rows)) {
      $this->validationResult = new AccessResultForbidden('`rows` is required.');
    }
    // Make sure we have a columns value.
    elseif (is_null($columns)) {
      $this->validationResult = new AccessResultForbidden('`columns` is required.');
    }
    // Rows and columns are going to come through as strings, so use a regular
    // expression validate that they contain integers in the range we want.
    elseif (!preg_match('/^[1-6]$/', $rows)) {
      $this->validationResult = new AccessResultForbidden('`rows` must be a positive integer between 1 and 6.');
    }
    elseif (!preg_match('/^[1-6]$/', $columns)) {
      $this->validationResult = new AccessResultForbidden('`columns` must be a positive integer between 1 and 6.');
    }
    // Make sure at least one of the inputs is an even number.
    elseif (($rows % 2 != 0) && ($columns % 2 != 0)) {
      $this->validationResult = new AccessResultForbidden('Either `rows` or `columns` needs to be an even number.');
    }
    // This is to catch an possible edge case just in case somehow we get past
    // the previous check while still being able to generate an odd number of
    // cards.
    elseif (($rows * $columns) % 2 != 0) {
      $this->validationResult = new AccessResultForbidden('Requested `rows` and `columns` would generate an odd number of cards.');
    }
    // All checks pass. Set result to valid.
    else {
      $this->validationResult = new AccessResultNeutral();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationResult(): AccessResultInterface {
    return $this->validationResult;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(): bool {
    if ($this->validationResult instanceof AccessResultNeutral || $this->validationResult instanceof AccessResultAllowed) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessResultMessage(): string {
    $message = 'Request Invalid.';
    if ($this->isValid()) {
      $message = '';
    }
    elseif ($this->validationResult instanceof AccessResultForbidden) {
      $message = $this->validationResult->getReason();
    }

    return $message;
  }

}
