<?php

namespace Drupal\memory_game;

/**
 * Interface for card grids.
 */
interface CardGridInterface {

  /**
   * Constructor.
   *
   * @param int $rows
   *   The number of rows.
   * @param int $columns
   *   The number of columns.
   */
  public function __construct(int $rows, int $columns);

  /**
   * Generate the grid of cards.
   */
  public function generateGrid(): self;

  /**
   * Get the grid of cards.
   *
   * @return array
   *   An array of cards laid out in the specified number of rows and columns.
   */
  public function getGrid(): array;

  /**
   * Get the current draw.
   */
  public function getCurrentDraw(): array;

  /**
   * Get the current list of unique cards.
   */
  public function getUniqueCards(): array;

  /**
   * Returns the count of the current number of cards in the grid.
   */
  public function getCardCount();

  /**
   * Returns the count of the current number of unique cards in the grid.
   */
  public function getUniqueCardCount();

}
