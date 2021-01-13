<?php

declare(strict_types=1);

namespace Cafe\Infra;

use Cafe\Domain\Tab\Tab;
use Cafe\Domain\Tab\TabRepository;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\UuidAggregateRootId;

class TabRepositoryEventSauce implements TabRepository
{
    private AggregateRootRepositoryWithSnapshotting $repository;

    public function __construct(AggregateRootRepositoryWithSnapshotting $repository)
    {
        $this->repository = $repository;
    }

    public function save(Tab $tab): void
    {
        $this->repository->persist($tab);
        $this->repository->storeSnapshot($tab);
    }

    public function get(string $tabId): Tab
    {
        /** @var Tab $tab */
        $tab = $this->repository->retrieveFromSnapshot(UuidAggregateRootId::fromString($tabId));// todo use annonymous class

        return $tab;
    }

}