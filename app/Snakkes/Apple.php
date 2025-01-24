<?php declare(strict_types=1);

namespace App\Snakkes;

class Apple
{
    public function __construct(
        /**
         * @var array{x: int, y: int}
         */
        public array $coordinates {
            set {
                if ($value === [] || array_keys($value) !== ['x', 'y']) {
                    $msg = 'Coordinates must be an array with the keys x and y';
                    throw new \InvalidArgumentException($msg);
                }
                $this->coordinates = $value;
            }
        },
        /**
         * @var AppleColorString
         */
        public readonly string $color = 'white'
    ) {}
}
