<?php

namespace Drupal\memory_game;

use Drupal\Core\Access\AccessResultInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for request validators.
 */
interface RequestValidatorInterface {

  /**
   * Validate a request to make sure that it is a well formed API request.
   *
   * @param ?\Symfony\Component\HttpFoundation\Request $request
   *   The request to validate. If none is provided, the current request
   *   will be used.
   */
  public function validateRequest(Request $request = NULL): self;

  /**
   * A check to return whether the previous validation request was valid.
   *
   * @return bool
   *   Whether the previous request passed validation.
   */
  public function isValid(): bool;

  /**
   * Return the result of the previous validation.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access validation result.
   */
  public function getValidationResult(): AccessResultInterface;

  /**
   * Get the message result of the previous validation attempt.
   *
   * @return string
   *   The reason for the access result response.
   */
  public function getAccessResultMessage(): string;

}
