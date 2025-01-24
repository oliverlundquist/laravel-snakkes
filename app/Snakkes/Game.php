<?php declare(strict_types=1);

namespace App\Snakkes;

class Game
{
    public readonly Worm $worm1;
    public readonly Worm $worm2;
    public readonly Apple $apple;

    public function __construct(
        /**
         * @var UUIDv4String
         */
        public string $player1ConnectionId = 'dummy value for now',

        /**
         * @var UUIDv4String
         */
        public string $player2ConnectionId = 'dummy value for now',

        public readonly int $wormLength = 4,
        public readonly int $maxScore = 10,

        public int $player1Score = 0 {
            set {
                if ($value > $this->maxScore) {
                    throw new \InvalidArgumentException('Score can not be larger than maxScore set at ' . $this->maxScore);
                }
                $this->player1Score = $value;
            }
        },
        public int $player2Score = 0 {
            set {
                if ($value > $this->maxScore) {
                    throw new \InvalidArgumentException('Score can not be larger than maxScore set at ' . $this->maxScore);
                }
                $this->player2Score = $value;
            }
        },
        /**
         * @var GameStateString
         */
        public string $gameState = 'running' {
            set {
                if ($value !== 'running' && $value !== 'finished') {
                    throw new \InvalidArgumentException('Game state can only be running or finished');
                }
                $this->gameState = $value;
            }
        },
        /**
         * @var GameDifficultyString
         */
        public string $difficulty = 'normal' {
            set {
                if ($value !== 'normal' && $value !== 'insane') {
                    throw new \InvalidArgumentException('Game state can only be normal or insane');
                }
                $this->difficulty = $value;
            }
        }
    ) {
        $coordinates = new Coordinates();
        $this->worm1 = new Worm(coordinates: $coordinates->getInitialCoordinatesForWorm(true, 4), color: 'steelblue');
        $this->worm2 = new Worm(coordinates: $coordinates->getInitialCoordinatesForWorm(false, 4), color: 'yellow');
        $this->apple = new Apple(coordinates: $coordinates->getNewCoordinatesForApple($this->worm1, $this->worm2));
    }
}
