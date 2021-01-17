<?php

declare(strict_types=1);

namespace Cafe\Application\Write;

class CloseTabCommand implements LockedCommand
{
    public string $tabId;
    public float $amountPaid;

    public function __construct(string $tabId, float $amountPaid)
    {
        $this->tabId = $tabId;
        $this->amountPaid = $amountPaid;
    }

    public function lockName(): string
    {
        return $this->tabId;
    }
}