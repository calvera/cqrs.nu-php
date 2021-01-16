<?php

declare(strict_types=1);

namespace Cafe\Domain\Tab;

use JsonSerializable;

final class OrderedItem implements JsonSerializable
{
    public int $menuNumber;
    public string $description;
    public bool $isDrink;
    public float $price;

    public function __construct(int $menuNumber = 1, string $description = '', bool $isDrink = false, float $price = 0)
    {
        $this->menuNumber = $menuNumber;
        $this->description = $description;
        $this->isDrink = $isDrink;
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getMenuNumber(): int
    {
        return $this->menuNumber;
    }

    /**
     * @param int $menuNumber
     */
    public function setMenuNumber(int $menuNumber): void
    {
        $this->menuNumber = $menuNumber;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isDrink(): bool
    {
        return $this->isDrink;
    }

    /**
     * @param bool $isDrink
     */
    public function setIsDrink(bool $isDrink): void
    {
        $this->isDrink = $isDrink;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function jsonSerialize()
    {
        return [
            'menuNumber' => $this->menuNumber,
            'description' => $this->description,
            'isDrink' => $this->isDrink,
            'price' => $this->price,
        ];
    }
}