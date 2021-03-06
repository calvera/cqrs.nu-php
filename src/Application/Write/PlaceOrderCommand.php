<?php

declare(strict_types=1);

namespace Cafe\Application\Write;

use Cafe\Domain\Tab\OrderedItem;

class PlaceOrderCommand implements LockedCommand
{
    public string $tabId;
    /** @var array<OrderedItem> */
    public array $items;

    public function __construct(string $tabId, array $items)
    {
        $this->tabId = $tabId;
        $this->items = $items;
    }
    public function lockName(): string
    {
        return $this->tabId;
    }

}