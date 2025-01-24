<?php declare(strict_types=1);

namespace App\Snakkes;

class Worm
{
    public function __construct(
        /**
         * @var array<int, array{x: int, y: int}>
         */
        public array $coordinates {
            set {
                if ($value === [] || $value !== array_filter($value, fn ($_value) => array_keys($_value) === ['x', 'y'])) {
                    $msg = 'Coordinates must be an array of arrays with the keys x and y';
                    throw new \InvalidArgumentException($msg);
                }
                $this->coordinates = $value;
            }
        },
        /**
         * @var WormColorString
         */
        public string $color = 'steelblue' {
            set {
                if ($value !== 'steelblue' && $value !== 'yellow') {
                    throw new \InvalidArgumentException('Color can only be either "steelblue" or "yellow"');
                }
                $this->color = $value;
            }
        },
        /**
         * @var WormDirectionString
         */
        public string $direction = 'right' {
            set {
                if (! in_array($value, ['left', 'up', 'right', 'down'], true)) {
                    throw new \InvalidArgumentException('Direction can only be any of these values: "left", "up", "right", "down"');
                }
                $this->direction = $value;
            }
        }
    ) {}
}
