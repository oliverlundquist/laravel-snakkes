<?php declare(strict_types=1);

namespace App\Snakkes;

class GameHandler
{
    /**
     * @var array<UUIDv4String, \App\Snakkes\Game>
     */
    private array $games = [];

    /**
     * @param UUIDv4String $connectionId
     * @param GameDifficultyString $difficulty
     */
    public function startNewGame(string $connectionId, string $difficulty): void
    {
        $this->games[$connectionId] = new Game(difficulty: $difficulty);
    }

    /**
     * @return array<UUIDv4String, \App\Snakkes\Game>
     */
    public function getAllGames(): array
    {
        return $this->games;
    }

    /**
     * @param UUIDv4String $connectionId
     */
    public function getGame(string $connectionId): ?Game
    {
        return $this->games[$connectionId] ?? null;
    }

    public function gameShouldEnd(Game $game): bool
    {
        return $game->player1Score >= $game->maxScore || $game->player2Score >= $game->maxScore;
    }

    public function endGame(Game $game): void
    {
        $game->gameState = 'finished';
    }

    public function gameIsFinished(Game $game): bool
    {
        return $game->gameState === 'finished';
    }

    /**
     * @param UUIDv4String $connectionId
     */
    public function removeGame(string $connectionId): void
    {
        unset($this->games[$connectionId]);
    }

    /**
     * @return array{
     *      state: GameStateString,
     *      board: array<int, array{0: int, 1: int, 2: WormColorString|AppleColorString}>,
     *      scores: array{ player1: int, player2: int }
     * }
     */
    public function getGameState(Game $game): array
    {
        $paintedCoordinates = [];
        foreach ($game->worm2->coordinates as $coordinates) {
            $paintedCoordinates[] = [$coordinates['x'], $coordinates['y'], $game->worm2->color];
        }
        foreach ($game->worm1->coordinates as $coordinates) {
            $paintedCoordinates[] = [$coordinates['x'], $coordinates['y'], $game->worm1->color];
        }
        $paintedCoordinates[] = [$game->apple->coordinates['x'], $game->apple->coordinates['y'], $game->apple->color];

        return [
            'state'  => $game->gameState,
            'board'  => $paintedCoordinates,
            'scores' => ['player1' => $game->player1Score, 'player2' => $game->player2Score]
        ];
    }
}
