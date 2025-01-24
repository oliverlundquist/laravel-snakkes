<?php declare(strict_types=1);

namespace App\Snakkes;

class Board
{
    /**
     * @var array<int, int>
     */
    public readonly array $ticksX;

    /**
     * @var array<int, int>
     */
    public readonly array $ticksY;

    public function __construct(
        public readonly string $backgroundColor = 'black',
        public readonly int $width = 400,
        public readonly int $height = 400,
        public readonly int $squareWidth = 10,
        public readonly int $squareHeight = 10,
    ) {
        $this->ticksX = range(0, $this->width  - $this->squareWidth,  $this->squareWidth);
        $this->ticksY = range(0, $this->height - $this->squareHeight, $this->squareHeight);
    }
}
