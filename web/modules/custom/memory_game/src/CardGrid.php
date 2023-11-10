<?php

namespace Drupal\memory_game;

/**
 * A card grid creates a grid of cards for a memory game.
 *
 * Card Grids have a number of rows and columns, a deck of cards in the amount
 * of the product of rows and columns (to fill the grid). Each card in the deck
 * will have exactly 1 matching card somewhere else in the deck. The unique
 * cards will be randomly selected from a larger list of possible cards and
 * randomly distributed throughout the grid.
 */
class CardGrid implements CardGridInterface {
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
   * The number of rows.
   *
   * @var int
   */
  protected $rows;

  /**
   * The number of columns.
   *
   * @var int
   */
  protected $columns;

  /**
   * The grid of cards.
   *
   * @var array
   */
  protected $grid = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(int $rows, int $columns) {
    $num_cards = $rows * $columns;
    if ($num_cards % 2 !== 0) {
      throw new \Exception('Unable to generate a grid with an odd number of cards.');
    }

    $this->rows = $rows;
    $this->columns = $columns;
  }

  /**
   * {@inheritdoc}
   */
  public function generateGrid(): self {
    $current_draw = $this->getCurrentDraw();

    // Arrange the cards in a grid.
    $this->grid = [];
    for ($i = 0; $i < $this->rows; $i++) {
      // Offset the slice by the number of items in each row.
      $offset = $i * $this->columns;
      $this->grid[$i] = array_slice($current_draw, $offset, $this->columns);
    }

    return $this;
  }

  /**
   * Get the grid.
   *
   * @return array
   *   The grid of cards.
   */
  public function getGrid(): array {
    return $this->grid;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentDraw(): array {
    if (empty($this->currentDraw)) {
      $this->drawCards();
    }
    return $this->currentDraw;
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueCards(): array {
    return $this->uniqueCards;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardCount() {
    return count($this->currentDraw);
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueCardCount() {
    return count($this->uniqueCards);
  }

  /**
   * Resets the current draw to be the full deck.
   */
  protected function resetDraw(): self {
    $this->currentDraw = [];
    $this->uniqueCards = self::FULL_DECK;
    return $this;
  }

  /**
   * Draws a number of cards from the Full Deck and returns the draw.
   *
   * The number of cards drawn will equal the product of the rows and columns.
   */
  protected function drawCards(): self {
    // Reset the unique cards to be the full deck.
    $this->resetDraw();

    // Shuffle the unique cards and draw a number of them equal to half the
    // total cards to get a random set of unique cards.
    shuffle($this->uniqueCards);
    $num_cards = $this->rows * $this->columns;
    $this->uniqueCards = array_slice($this->uniqueCards, 0, $num_cards / 2);

    // Fill the currentDraw with 2 copies of each card.
    $this->currentDraw = array_merge($this->uniqueCards, $this->uniqueCards);
    // Shuffle the current Draw.
    shuffle($this->currentDraw);

    return $this;
  }

}
