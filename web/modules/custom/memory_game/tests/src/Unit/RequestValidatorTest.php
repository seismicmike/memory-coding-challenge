<?php

namespace Drupal\Tests\memory_game\Unit;

use Drupal\memory_game\RequestValidator;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\Api;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test description.
 *
 * @group memory_game
 */
class RequestValidatorTest extends UnitTestCase {
  use MockApiRequestTrait;

  const CASES = [
    [
      'rows' => 2,
      'columns' => 2,
      'valid' => TRUE,
    ],
    [
      'rows' => 5,
      'columns' => 10,
      'valid' => FALSE,
    ],
    [
      'rows' => 4,
      'columns' => 6,
      'valid' => TRUE,
    ],
    [
      'rows' => 6,
      'columns' => 4,
      'valid' => TRUE,
    ],
    [
      'rows' => 3,
      'columns' => 5,
      'valid' => FALSE,
    ],
    [
      'rows' => 18,
      'columns' => 5,
      'valid' => FALSE,
    ],
    [
      'rows' => 10,
      'columns' => 6,
      'valid' => FALSE,
    ],
    [
      'rows' => NULL,
      'columns' => 5,
      'valid' => FALSE,
    ],
    [
      'rows' => 4,
      'columns' => NULL,
      'valid' => FALSE,
    ],
    [
      'rows' => 0,
      'columns' => 3,
      'valid' => FALSE,
    ],
    [
      'rows' => 4,
      'columns' => 0,
      'valid' => FALSE,
    ],
    [
      'rows' => -5,
      'columns' => 3,
      'valid' => FALSE,
    ],
    [
      'rows' => 4,
      'columns' => -7,
      'valid' => FALSE,
    ],
    [
      'rows' => 'squirrel',
      'columns' => 'monkey',
      'valid' => FALSE,
    ],
    [
      'rows' => '1',
      'columns' => '2',
      'valid' => TRUE,
    ],
    [
      'rows' => '5',
      'columns' => '4',
      'valid' => TRUE,
    ],
    [
      'rows' => '2.5',
      'columns' => '4',
      'valid' => FALSE,
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Create an instance of the RequestValidator that uses supplied inputs.
   *
   * @param mixed $rows
   *   The rows to test.
   * @param mixed $columns
   *   The columns to test.
   *
   * @return \Drupal\memory_game\RequestValidator
   *   A request validator object.
   */
  protected function mockValidator($rows, $columns): RequestValidator {
    $request_stack = $this->createMock(RequestStack::class);
    $mock_request = $this->mockRequest($rows, $columns);

    $request_stack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($mock_request);

    return new RequestValidator($request_stack);
  }

  /**
   * Tests something.
   */
  public function testThroughRequestStackService() {

    foreach (self::CASES as $case) {
      // Test using the request stack service.
      $validator = $this->mockValidator($case['rows'], $case['columns']);
      $validator->validateRequest();
      $message = $validator->getAccessResultMessage();
      $this->assertEquals($case['valid'], $validator->isValid(), "Validation completed with message: $message");
    }
  }

  /**
   * Tests something.
   */
  public function testPassingRequestDirectly() {
    $validator = $this->mockValidator(2, 2);

    foreach (self::CASES as $case) {
      // Test when passing a request object directly.
      $request = $this->mockRequest($case['rows'], $case['columns']);
      $validator->validateRequest($request);
      $message = $validator->getAccessResultMessage();
      $this->assertEquals($case['valid'], $validator->isValid(), "Validation completed with message: $message");
    }
  }

}
