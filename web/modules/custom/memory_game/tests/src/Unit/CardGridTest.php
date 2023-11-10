<?php

namespace Drupal\Tests\memory_game\Unit;

use Drupal\memory_game\CardGrid;
use Drupal\Tests\UnitTestCase;

/**
 * Test the CardGrid class.
 *
 * @group memory_game
 */
class CardGridTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests whether the grid generates the correct number of cards.
   */
  public function testCardGrid() {
    $cases = [
      [
        'rows' => 2,
        'columns' => 2,
        'total' => 4,
        'unique' => 2,
      ],
      [
        'rows' => 4,
        'columns' => 5,
        'total' => 20,
        'unique' => 10,
      ],
      [
        'rows' => 6,
        'columns' => 6,
        'total' => 36,
        'unique' => 18,
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
        'rows' => 8,
        'columns' => 16,
        'total' => 128,
        'unique' => 64,
      ],
    ];

    foreach ($cases as $case) {
      $rows = (int) $case['rows'];
      $columns = (int) $case['columns'];
      $grid = new CardGrid($rows, $columns);
      $grid->generateGrid();

      $unique_cards = $grid->getUniqueCards();
      $unique_card_count = $grid->getUniqueCardCount();
      $card_count = $grid->getCardCount();
      $current_draw = $grid->getCurrentDraw();

      $this->assertEquals($case['unique'], count($unique_cards), "{$case['unique']} unique cards were drawn.");
      $this->assertEquals($case['unique'], $grid->getUniqueCardCount(), "{$case['unique']} unique cards were reported.");
      $this->assertEquals($case['total'], count($current_draw), "{$case['total']} cards were drawn.");
      $this->assertEquals($case['total'], $grid->getCardCount(), "{$case['total']} cards were reported.");
      $this->assertEquals($card_count, ($unique_card_count * 2), 'The card count is double the unique card count.');
      $this->assertEquals($card_count, ($rows * $columns), 'The card count is the product of the rows and columns.');

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
      $card_grid = $grid->getGrid();
      foreach ($card_grid as $row) {
        $collapsed_list = array_merge($collapsed_list, $row);
      }
      sort($collapsed_list);
      sort($current_draw);
      $this->assertTrue($current_draw === $collapsed_list, 'The grid has the same cards as the current draw.');
    }
  }

}
