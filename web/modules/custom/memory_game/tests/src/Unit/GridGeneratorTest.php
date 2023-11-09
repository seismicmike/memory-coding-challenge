<?php

namespace Drupal\Tests\memory_game\Unit;

use Drupal\memory_game\GridGenerator;
use Drupal\Tests\UnitTestCase;

/**
 * Test description.
 *
 * @group memory_game
 */
class GridGeneratorTest extends UnitTestCase {
  /**
   * Grid generator class.
   *
   * @var \Drupal\memory_game\GridGenerator
   */
  protected $gridGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->gridGenerator = new GridGenerator();
  }

  /**
   * Tests whether the grid generates the correct number of cards.
   */
  public function testGridGenerator() {
    $cases = [
      [
        'rows' => 5,
        'columns' => 6,
        'total' => 30,
        'unique' => 15,
      ],
      [
        'rows' => 3,
        'columns' => 4,
        'total' => 12,
        'unique' => 6,
      ],
      [
        'rows' => 8,
        'columns' => 6,
        'total' => 48,
        'unique' => 24,
      ],
      [
        'rows' => 0,
        'columns' => 6,
        'total' => 0,
        'unique' => 0,
      ],
      [
        'rows' => 4,
        'columns' => 0,
        'total' => 0,
        'unique' => 0,
      ],
      [
        'rows' => 4,
        'columns' => -2,
        'total' => -8,
        'unique' => -4,
      ],
      [
        'rows' => -3,
        'columns' => 6,
        'total' => -18,
        'unique' => -9,
      ],
    ];

    foreach ($cases as $case) {
      $this->gridGenerator->resetDraw();
      $rows = $case['rows'];
      $columns = $case['columns'];

      if (!is_int($rows) || !is_int($columns) || $rows < 1 || $columns < 1 || (($rows * $columns) % 2 != 0)) {
        $this->expectException(\Exception::class);
      }

      $grid = $this->gridGenerator->generateGrid($rows, $columns, TRUE);
      $unique_cards = $this->gridGenerator->getUniqueCards();
      $this->assertEquals($case['unique'], count($unique_cards), "{$case['unique']} cards were drawn.");
      $this->assertEquals($case['unique'], $this->gridGenerator->getUniqueCardCount(), "{$case['unique']} cards were reported.");
      $current_draw = $this->gridGenerator->getCurrentDraw();
      $this->assertEquals($case['total'], count($current_draw), "{$case['total']} cards were drawn.");
      $this->assertEquals($case['total'], $this->gridGenerator->getCardCount(), "{$case['total']} cards were reported.");

      // Test that the full list of cards has exactly 1 match for each unique
      // card.
      $this->assertEquals($case['unique'], count($current_draw) / 2, 'There are twice as many cards as there are unique cards.');

      // Test that every unique value has exactly 2 occurrences in the full draw.
      $counts = array_count_values($current_draw);
      $this->assertEquals($case['unique'], count($counts), "There are {$case['unique']} values in the full draw");
      $max = max($counts);
      $min = min($counts);
      $this->assertEquals(2, $max, "The maximum number of occurrences for any given card is 2.");
      $this->assertEquals(2, $min, "The minimum number of occurrences for any given card is 2.");

      // Make sure no cards were used that aren't in the designated unique set.
      $diff = array_diff(array_keys($counts), $unique_cards);
      $this->assertEquals(count($diff), 0, "No cards were used in the grid that were not in the list of unique cards");

      // Collapse the grid into a single array again and check that it matches
      // the current draw.
      $collapsed_list = [];
      foreach ($grid as $row) {
        $collapsed_list = array_merge($collapsed_list, $row);
      }
      sort($collapsed_list);
      sort($current_draw);
      $this->assertTrue($current_draw === $collapsed_list, 'The grid has the same cards as the current draw.');
    }
  }

}
