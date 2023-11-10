<?php

namespace Drupal\Tests\memory_game\Unit;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\memory_game\GridGenerator;
use Drupal\memory_game\RequestValidator;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the CardGrid class.
 *
 * @group memory_game
 */
class GridGeneratorTest extends UnitTestCase {
  use MockApiRequestTrait;

  /**
   * Mock validator that will return a success result.
   *
   * @var \Drupal\memory_game\RequestValidator
   */
  protected $successValidator;

  /**
   * Mock validator that will return a neutral result.
   *
   * @var \Drupal\memory_game\RequestValidator
   */
  protected $neutralValidator;

  /**
   * Mock validator that will return a forbidden result.
   *
   * @var \Drupal\memory_game\RequestValidator
   */
  protected $forbiddenValidator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $success_response = $this->createMock(AccessResultAllowed::class);
    $this->successValidator = $this->createMock(RequestValidator::class);
    $this->successValidator->expects($this->any())->method('isValid')->willReturn(TRUE);
    $this->successValidator->expects($this->any())->method('getAccessResultMessage')->willReturn('');
    $this->successValidator->expects($this->any())->method('getValidationResult')->willReturn($success_response);

    $neutral_response = $this->createMock(AccessResultNeutral::class);
    $this->neutralValidator = $this->createMock(RequestValidator::class);
    $this->neutralValidator->expects($this->any())->method('isValid')->willReturn(TRUE);
    $this->neutralValidator->expects($this->any())->method('getAccessResultMessage')->willReturn('');
    $this->neutralValidator->expects($this->any())->method('getValidationResult')->willReturn($neutral_response);

    $forbidden_response = $this->createMock(AccessResultForbidden::class);
    $forbidden_response->expects($this->any())
      ->method('getReason')
      ->willReturn('Access is denied. This is just a test.');
    $this->forbiddenValidator = $this->createMock(RequestValidator::class);
    $this->forbiddenValidator->expects($this->any())->method('isValid')->willReturn(FALSE);
    $this->forbiddenValidator->expects($this->any())->method('getAccessResultMessage')->willReturn('Access is denied. This is just a test.');
    $this->forbiddenValidator->expects($this->any())->method('getValidationResult')->willReturn($forbidden_response);
  }

  /**
   * Tests whether the grid generates the correct number of cards.
   */
  public function testCardGrid() {
    // Note. We're not testing the validator at this point. ReqeustValidatorTest
    // tests whether our validator is successfully validating our inputs.
    // The purpose here is to determine whether GridGenerator responds
    // appropriately to success, fail or neutral validations and outputs the
    // correct grid.
    $cases = [
      [
        'rows' => 2,
        'columns' => 2,
        'validation' => TRUE,
        'expected' => [
          'meta' => [
            'success' => TRUE,
            'cardCount' => 4,
            'uniqueCardCount' => 2,
            // Note we will not be validating the specific values. Those will
            // be random. But we do want to validate that they have the correct
            // number of elements.
            'uniqueCards' => ['one', 'two'],
          ],
          'data' => [
            'cards' => [
              // Note we will not be validating the specific values. Those will
              // be random. But we do want to validate that they have the
              // correct number of elements.
              ['one', 'two'],
              ['one', 'two'],
            ],

          ]
        ],
      ],
      [
        'rows' => 4,
        'columns' => 6,
        'validation' => TRUE,
        'expected' => [
          'meta' => [
            'success' => TRUE,
            'cardCount' => 24,
            'uniqueCardCount' => 12,
            // Note we will not be validating the specific values. Those will
            // be random. But we do want to validate that they have the correct
            // number of elements.
            'uniqueCards' => ['one',
              'two',
              'three',
              'four',
              'five',
              'six',
              'seven',
              'eight',
              'nine',
              'ten',
              'eleven',
              'twelve',
            ],
          ],
          'data' => [
            'cards' => [
              // Note we will not be validating the specific values. Those will
              // be random. But we do want to validate that they have the
              // correct number of elements.
              ['one', 'two', 'three', 'four', 'five', 'six'],
              ['one', 'two', 'three', 'four', 'five', 'six'],
              ['one', 'two', 'three', 'four', 'five', 'six'],
              ['one', 'two', 'three', 'four', 'five', 'six'],
            ],

          ]
        ],
      ],
      [
        'rows' => 1,
        'columns' => 5,
        'validation' => FALSE,
        'expected' => [
          'meta' => [
            'success' => FALSE,
            'message' => 'Access is denied. This is just a test.',
          ],
          'data' => []
        ],
      ],
    ];

    foreach ($cases as $case) {
      $rows = (int) $case['rows'];
      $columns = (int) $case['columns'];
      $expected = $case['expected'];
      $validator = $case['validation'] ? $this->neutralValidator : $this->forbiddenValidator;
      $generator = new GridGenerator($validator);
      $request = $this->mockRequest($rows, $columns);
      $response = $generator->generateGridFromRequest($request);

      $this->assertTrue(is_array($response), 'Response is an array.');
      $this->assertTrue(isset($response['meta']), 'Response has the meta attribute.');
      $this->assertTrue(isset($response['data']), 'Response has the data attribute.');
      $this->assertEquals($response['meta']['success'], $expected['meta']['success'], 'Response has expected success value.');

      if ($expected['meta']['success']) {
        $this->assertFalse(isset($response['meta']['message']), 'There is no message in the metadata');
        $this->assertTrue(isset($response['meta']['cardCount']), 'There is a card count in the metadata.');
        $this->assertEquals($response['meta']['cardCount'], $expected['meta']['cardCount'], 'The card count reports the correct value.');
        $this->assertTrue(isset($response['meta']['uniqueCardCount']), 'There is a unique card count in the metadata.');
        $this->assertEquals($response['meta']['uniqueCardCount'], $expected['meta']['uniqueCardCount'], 'The unique card count reports the correct value.');
        $this->assertEquals($response['meta']['cardCount'], ($response['meta']['uniqueCardCount'] * 2), 'The card count is double the unique card count.');
        $this->assertEquals($response['meta']['cardCount'], ($rows * $columns), 'The card count is the product of the rows and columns.');
        $this->assertTrue(isset($response['meta']['uniqueCards']), 'There is a unique card list in the metadata.');
        $this->assertTrue(is_array($response['meta']['uniqueCards']), 'The unique card list is an array.');
        $this->assertEquals($response['meta']['uniqueCardCount'], count($response['meta']['uniqueCards']), 'The unique card list has the correct number of cards.');
        $this->assertFalse(empty($response['data']), 'Data attribute is not empty.');
        $this->assertTrue(isset($response['data']['cards']), 'There is a cards attribute in the data');
        $this->assertTrue(is_array($response['data']['cards']), 'The cards attribute is an array');
        $this->assertEquals(count($response['data']['cards']), $rows, 'The cards array has the correct number of rows.');

        for ($i = 0; $i < $rows; $i++) {
          $row_number = $i + 1;
          $this->assertTrue(is_array($response['data']['cards'][$i]), "Row $row_number of the cards array is an array");
          $this->assertEquals(count($response['data']['cards'][$i]), $columns, "Row $row_number of the cards array has the correct number of columns.");
        }
      }
      else {
        $this->assertTrue(isset($response['meta']['message']), 'There is a message in the metadata');
        $this->assertFalse(empty($response['meta']['message']), 'The result message is non-empty');
        $this->assertFalse(isset($response['meta']['cardCount']), 'There is no card count in the metadata.');
        $this->assertFalse(isset($response['meta']['uniqueCardCount']), 'There is no unique card count in the metadata.');
        $this->assertFalse(isset($response['meta']['uniqueCards']), 'There is no unique card list in the metadata.');
        $this->assertTrue(empty($response['data']), 'Data attribute is empty.');
      }
    }
  }

}
