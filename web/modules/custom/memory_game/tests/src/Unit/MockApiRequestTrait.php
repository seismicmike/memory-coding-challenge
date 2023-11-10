<?php

namespace Drupal\Tests\memory_game\Unit;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * A trait for mocking requests for the game API.
 */
trait MockApiRequestTrait {

  /**
   * Create a mock request.
   *
   * @param mixed $rows
   *   The value to set for rows.
   * @param mixed $columns
   *   The value to set for columns.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The request mock.
   */
  protected function mockRequest($rows, $columns): Request {
    $mock_query = $this->createMock(ParameterBag::class);
    $mock_query->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($parameter) use ($rows, $columns) {
        if ($parameter === 'rows') {
          return $rows;
        }
        elseif ($parameter === 'columns') {
          return $columns;
        }
        // Handle other cases or return a default value.
        return NULL;
      });

    $mock_request = $this->createMock(Request::class);
    $mock_request->query = $mock_query;
    return $mock_request;
  }

}
