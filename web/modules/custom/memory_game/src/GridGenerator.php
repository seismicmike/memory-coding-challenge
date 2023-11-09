<?php

namespace Drupal\memory_game;

/**
 * The GridGenerator creates a grid of cards.
 *
 * A grid has an even number of cards. Each card has exactly one match in the
 * grid. The order of cards is random.
 */
class GridGenerator {
  /**
   * The full list of available cards that came in the box.
   *
   * These cards will be used to create a random draw of cards for the grid.
   *
   * @const array
   */
  const FULL_DECK = [
    "Genesis",
    "Exodus",
    "Leviticus",
    "Numbers",
    "Deuteronomy",
    "Joshua",
    "Judges",
    "Ruth",
    "1 Samuel",
    "2 Samuel",
    "1 Kings",
    "2 Kings",
    "1 Chronicles",
    "2 Chronicles",
    "Ezra",
    "Nehemiah",
    "Esther",
    "Job",
    "Psalms",
    "Proverbs",
    "Ecclesiastes",
    "Song of Solomon",
    "Isaiah",
    "Jeremiah",
    "Lamentations",
    "Ezekiel",
    "Daniel",
    "Hosea",
    "Joel",
    "Amos",
    "Obadiah",
    "Jonah",
    "Micah",
    "Nahum",
    "Habakkuk",
    "Zephaniah",
    "Haggai",
    "Zechariah",
    "Malachi",
    "Matthew",
    "Mark",
    "Luke",
    "John",
    "Acts",
    "Romans",
    "1 Corinthians",
    "2 Corinthians",
    "Galatians",
    "Ephesians",
    "Philippians",
    "Colossians",
    "1 Thessalonians",
    "2 Thessalonians",
    "1 Timothy",
    "2 Timothy",
    "Titus",
    "Philemon",
    "Hebrews",
    "James",
    "1 Peter",
    "2 Peter",
    "1 John",
    "2 John",
    "3 John",
    "Jude",
    "Revelation",
  ];
  /**
   * The current draw of matches.
   *
   * If it is not empty, this will be an even number of cards and every card
   * will have exactly one match in the list. The order will be random so the
   * matches will not necessarily be next to each other. (It will be possible,
   * but it should not be like Noah's ark).
   *
   * @var array
   */
  protected $currentDraw = [];
  /**
   * The unique cards used to generate the current draw of matches.
   *
   * @var array
   */
  protected $uniqueCards = self::FULL_DECK;

  /**
   * Resets the current draw to be the full deck.
   */
  public function resetDraw(): self {
    $this->currentDraw = [];
    $this->uniqueCards = self::FULL_DECK;
    return $this;
  }

  /**
   * Draws a number of cards from the Full Deck and returns the draw.
   *
   * @param int $numCards
   *   The total number of cards to show in the grid (including both matches).
   *   So if you have a 2x2 grid, the number of cards is 4, even though there
   *   will be 2 unique cards.
   *
   * @return array
   *   The new draw of cards.
   */
  public function drawCards(int $numCards): array {
    // @todo We're preventing this with validation, but we should also make sure
    //   to handle this error gracefully.
    if ($numCards % 2 != 0) {
      throw new \Exception("There is not an even number of cards. Generating a grid would result in a card with no match.");
    }

    // Reset the unique cards to be the full deck.
    $this->resetDraw();

    // Shuffle the unique cards and draw a number of them equal to half the
    // total cards to get a random set of unique cards.
    shuffle($this->uniqueCards);
    $this->uniqueCards = array_slice($this->uniqueCards, 0, $numCards / 2);

    // Fill the currentDraw with 2 copies of each card.
    $this->currentDraw = array_merge($this->uniqueCards, $this->uniqueCards);
    // Shuffle the current Draw.
    shuffle($this->currentDraw);

    // Return the current draw.
    return $this->currentDraw;
  }

  /**
   * Get the current draw.
   */
  public function getCurrentDraw(): array {
    return $this->currentDraw;
  }

  /**
   * Get the current list of unique cards.
   */
  public function getUniqueCards(): array {
    return $this->uniqueCards;
  }

  /**
   * Returns the count of the current number of cards in the grid.
   */
  public function getCardCount() {
    return count($this->currentDraw);
  }

  /**
   * Returns the count of the current number of unique cards in the grid.
   */
  public function getUniqueCardCount() {
    return count($this->uniqueCards);
  }

  /**
   * Generate a game grid.
   *
   * @param int $rows
   *   The number of rows. Must be a positive integer. $rows * $columns must be
   *   a positive even integer.
   * @param int $columns
   *   The number of columns. Must be a positive integer. $rows * $columns must
   *   be a positive even integer.
   * @param bool $resetDraw
   *   Whether to reset the draw when generating the grid. If specified, a fresh
   *   draw with the specified number of cards will be generated. Otherwise the
   *   current draw will be used. Be ware as this can created unexpected
   *   results. If, for instance, you create a grid with 4 rows and 6 columns,
   *   you can then create a grid with 6 rows and 4 columns with no problem
   *   because there are the same number of cards. But if you generate a grid
   *   with 5 rows and 4 columns, you can't  then use the same deck to generate
   *   a grid with 5 rows and 6 columns.
   *
   * @return array
   *   A 2 dimensional array with the specified number of rows and cards
   *   arraying a grid of cards. Each card will have exactly one match in the
   *   grid.
   */
  public function generateGrid(int $rows, int $columns, bool $resetDraw = FALSE): array {
    if ($rows <= 0) {
      throw new \Exception("Invalid number of rows specified. Please indicate a positive integer for the number of rows.");
    }

    if ($columns <= 0) {
      throw new \Exception("Invalid number of columns specified. Please indicate a positive integer for the number of columns.");
    }

    if ($resetDraw || $this->currentDraw === NULL) {
      $this->drawCards($rows * $columns);
    }

    // Arrange the cards in a grid.
    $grid = [];

    for ($i = 0; $i < $rows; $i++) {
      // Offset the slice by the number of items in each row.
      $offset = $i * $columns;
      $grid[$i] = array_slice($this->currentDraw, $offset, $columns);
    }

    return $grid;
  }

}
