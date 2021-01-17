<?php

declare(strict_types=1);

namespace Cafe\Application\Write;

final class OpenTabCommand implements LockedCommand
{
    public string $tabId;
    public int $tableNumber;
    public string $waiter;

    public function __construct(string $tabId, int $tableNumber, string $waiter)
    {
        $this->tabId = $tabId;
        $this->tableNumber = $tableNumber;
        $this->waiter = $waiter;
    }
    public function lockName(): string
    {
        return $this->tabId;
    }

}