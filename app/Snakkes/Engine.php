<?php declare(strict_types=1);

namespace App\Snakkes;

class Engine
{
    private Collision $collision;
    private Coordinates $coordinates;
    private GameHandler $gameHandler;

    public function __construct(?Collision $collision = null, ?Coordinates $coordinates = null, ?GameHandler $gameHandler = null)
    {
        $this->collision   = $collision   ?? new Collision;
        $this->coordinates = $coordinates ?? new Coordinates;
        $this->gameHandler = $gameHandler ?? new GameHandler;
    }

    public function process(Game $game): void
    {
        $player1AteApple = false;
        $player2AteApple = false;

        // should game end?
        if ($this->gameHandler->gameShouldEnd($game)) {
            $this->gameHandler->endGame($game);
        }

        // return if game has ended
        if ($this->gameHandler->gameIsFinished($game)) {
            return;
        }

        // did player1's worm eat an apple?
        if ($this->collision->wormCollisionWithApple($game->worm1, $game->apple)) {
            $player1AteApple = true;
            $game->player1Score = $game->player1Score + 1;
        }

        // did player2's worm eat an apple?
        if ($this->collision->wormCollisionWithApple($game->worm2, $game->apple)) {
            $player2AteApple = true;
            $game->player2Score = $game->player2Score + 1;
        }

        // calculate best direction for computer worm
        $precision = $game->difficulty === 'normal' ? 3 : 0; // 25% change of wrong direction in normal mode, 0% in insane mode
        $game->worm2->direction = $this->coordinates->calculateBestDirectionForWorm($game->worm2, $game->apple, $precision);

        // move player1's worm
        $newCoordinates = $this->coordinates->getNextCoordinatesForWorm($game->worm1);
        if (! is_null($newCoordinates)) {
            $wormCoordinates = [...$game->worm1->coordinates, $newCoordinates];
            // if worm didn't eat apple, remove last coordinate, otherwise let it grow by 1 square
            if (! $player1AteApple) {
                array_shift($wormCoordinates);
            }
            $game->worm1->coordinates = $wormCoordinates;
        }

        // move player2's worm
        $newCoordinates = $this->coordinates->getNextCoordinatesForWorm($game->worm2);
        if (! is_null($newCoordinates)) {
            $wormCoordinates = [...$game->worm2->coordinates, $newCoordinates];
            // if worm didn't eat apple, remove last coordinate, otherwise let it grow by 1 square
            if (! $player2AteApple) {
                array_shift($wormCoordinates);
            }
            $game->worm2->coordinates = $wormCoordinates;
        }

        // respawn apple if eaten
        if ($player1AteApple || $player2AteApple) {
            $appleCoordinates = $this->coordinates->getNewCoordinatesForApple($game->worm1, $game->worm2);
            $game->apple->coordinates = $appleCoordinates;
        }
    }
}
