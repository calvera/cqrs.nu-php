<?php

declare(strict_types=1);

namespace Cafe\Application\Read\ChefTodoList;

final class TodoListGroup
{
    public string $groupId;
    public string $tabId;
    public array $items;
    public int $tabNumber;

    public function __construct(string $groupId, string $tabId, int $tabNumber, array $items)
    {
        $this->groupId = $groupId;
        $this->tabId = $tabId;
        $this->tabNumber = $tabNumber;
        $this->items = $items;
    }
}