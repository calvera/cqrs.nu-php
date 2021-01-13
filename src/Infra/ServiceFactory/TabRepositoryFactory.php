<?php

declare(strict_types=1);

namespace Cafe\Infra\ServiceFactory;

use Cafe\Domain\Tab\Tab;
use Cafe\Infra\Read\ChefTodoProjector;
use Cafe\Infra\Read\TabProjector;
use Cafe\Infra\TabRepositoryEventSauce;
use Doctrine\DBAL\Connection;
use EventSauce\DoctrineMessageRepository\DoctrineMessageRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\InMemorySnapshotRepository;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;

class TabRepositoryFactory
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(): TabRepositoryEventSauce
    {
        $className = Tab::class;

        $repository = new DoctrineMessageRepository(
            $this->connection,
            new ConstructingMessageSerializer(),
            'aggregate_tab'
        );

        $snapshotRepository = new InMemorySnapshotRepository();
//        $snapshotRepository = new DoctrineMessageRepository(
//            $this->connection,
//            new ConstructingMessageSerializer(),
//            'aggregate_snapshot_tab'
//        );

        return new TabRepositoryEventSauce(
            new ConstructingAggregateRootRepositoryWithSnapshotting(
                $className,
                $repository,
                $snapshotRepository,
                new ConstructingAggregateRootRepository(
                    $className,
                    $repository,
                    new SynchronousMessageDispatcher(
                        new TabProjector($this->connection),
                        new ChefTodoProjector($this->connection),
                    )
                )
            )
        );
    }
}