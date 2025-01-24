<?php declare(strict_types=1);

namespace App\Snakkes;

class Collision
{
    private Board $board;

    public function __construct(?Board $board = null)
    {
        $this->board = $board ?? new Board;
    }

    public function wormCollisionWithApple(Worm $worm, Apple $apple): bool
    {
        return in_array($apple->coordinates, $worm->coordinates, true);
    }

    /**
     * @param array{x: int, y: int} $lastCoordinates
     */
    public function wormNotHittingLeftBounds(array $lastCoordinates): bool
    {
        return $lastCoordinates['x'] > 0;
    }

    /**
     * @param array{x: int, y: int} $lastCoordinates
     */
    public function wormNotHittingTopBounds(array $lastCoordinates): bool
    {
        return $lastCoordinates['y'] > 0;
    }

    /**
     * @param array{x: int, y: int} $lastCoordinates
     */
    public function wormNotHittingRightBounds(array $lastCoordinates): bool
    {
        $rightBounds = $this->board->ticksX[array_key_last($this->board->ticksX)];
        return $lastCoordinates['x'] < $rightBounds;
    }

    /**
     * @param array{x: int, y: int} $lastCoordinates
     */
    public function wormNotHittingBottomBounds(array $lastCoordinates): bool
    {
        $bottomBounds = $this->board->ticksY[array_key_last($this->board->ticksY)];
        return $lastCoordinates['y'] < $bottomBounds;
    }
}
