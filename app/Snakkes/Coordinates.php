<?php declare(strict_types=1);

namespace App\Snakkes;

class Coordinates
{
    private Board $board;
    private Collision $collision;

    public function __construct(?Board $board = null, ?Collision $collision = null)
    {
        $this->board     = $board     ?? new Board;
        $this->collision = $collision ?? new Collision;
    }

    /**
     * @return array<int, array{x: int, y: int}>
     */
    public function getInitialCoordinatesForWorm(bool $player1, int $wormLength): array
    {
        $coordinates = [];

        $centerTickX = floor(count($this->board->ticksX) / 2);
        $centerTickY = floor(count($this->board->ticksY) / 2);
        $coordinateX = $this->board->ticksX[$centerTickX];
        $coordinateY = $player1
                        ? $this->board->ticksY[$centerTickY] - $this->board->ticksY[floor($centerTickY / 2)]
                        : $this->board->ticksY[$centerTickY] + $this->board->ticksY[floor($centerTickY / 2)];

        for ($i = 0; $i < abs($wormLength); $i++) {
            $coordinates[] = ['x' => $coordinateX, 'y' => $coordinateY];
            $coordinateX = $coordinateX + $this->board->squareWidth;
        }
        return $coordinates;
    }

    /**
     * @return ?array{x: int, y: int}
     */
    public function getNextCoordinatesForWorm(Worm $worm): ?array
    {
        $lastCoordinates = $worm->coordinates[array_key_last($worm->coordinates)];
        $newCoordinates  = $worm->coordinates[array_key_last($worm->coordinates)];

        if ($worm->direction === 'left' && $this->collision->wormNotHittingLeftBounds($newCoordinates)) {
            $newCoordinates['x'] = $newCoordinates['x'] - $this->board->squareWidth;
        }
        if ($worm->direction === 'up' && $this->collision->wormNotHittingTopBounds($newCoordinates)) {
            $newCoordinates['y'] = $newCoordinates['y'] - $this->board->squareHeight;
        }
        if ($worm->direction === 'right' && $this->collision->wormNotHittingRightBounds($newCoordinates)) {
            $newCoordinates['x'] = $newCoordinates['x'] + $this->board->squareWidth;
        }
        if ($worm->direction === 'down' && $this->collision->wormNotHittingBottomBounds($newCoordinates)) {
            $newCoordinates['y'] = $newCoordinates['y'] + $this->board->squareHeight;
        }
        return $lastCoordinates === $newCoordinates ? null : $newCoordinates;
    }

    /**
     * @param 0|3 $precision
     * @return 'left'|'up'|'right'|'down'
     */
    public function calculateBestDirectionForWorm(Worm $worm, Apple $apple, int $precision): string
    {
        $wormDirection    = $worm->direction;
        $bestDirection    = $worm->direction;
        $nextDirection    = $worm->direction;
        $wormCoordinates  = $worm->coordinates[array_key_last($worm->coordinates)];
        $appleCoordinates = $apple->coordinates;

        if ($wormCoordinates['x'] > $appleCoordinates['x']) {
            $bestDirection = 'left';
            $nextDirection = $wormDirection === 'right' ? 'up' : $bestDirection;
        }
        if ($wormCoordinates['y'] > $appleCoordinates['y']) {
            $bestDirection = 'up';
            $nextDirection = $wormDirection === 'down' ? 'left' : $bestDirection;
        }
        if ($wormCoordinates['x'] < $appleCoordinates['x']) {
            $bestDirection = 'right';
            $nextDirection = $wormDirection === 'left' ? 'down' : $bestDirection;
        }
        if ($wormCoordinates['y'] < $appleCoordinates['y']) {
            $bestDirection = 'down';
            $nextDirection = $wormDirection === 'up' ? 'right' : $bestDirection;
        }
        return mt_rand(0, $precision) < 3 ? $nextDirection : ['left', 'up', 'right', 'down'][mt_rand(0, 3)];
    }

    /**
     * @return array{x: int, y: int}
     */
    public function getNewCoordinatesForApple(Worm $worm1, Worm $worm2): array
    {
        $ticksXMax = intval(array_key_last($this->board->ticksX));
        $ticksYMax = intval(array_key_last($this->board->ticksY));

        $appleCoordinates = [
            'x' => $this->board->ticksX[random_int(0, $ticksXMax)],
            'y' => $this->board->ticksY[random_int(0, $ticksYMax)]
        ];
        $apple = new Apple(coordinates: $appleCoordinates);

        while ($this->collision->wormCollisionWithApple($worm1, $apple) || $this->collision->wormCollisionWithApple($worm2, $apple)) {
            $appleCoordinates = [
                'x' => $this->board->ticksX[random_int(0, $ticksXMax)],
                'y' => $this->board->ticksY[random_int(0, $ticksYMax)]
            ];
            $apple = new Apple(coordinates: $appleCoordinates);
        }
        return $appleCoordinates;
    }
}
