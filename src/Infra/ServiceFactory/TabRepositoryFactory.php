<?php

declare(strict_types=1);

namespace Cafe\Infra\ServiceFactory;

use Cafe\Domain\Tab\Tab;
use Cafe\Infra\Read\ChefTodoProjector;
use Cafe\Infra\Read\TabProjector;
use Cafe\Infra\TabRepositoryEventSauce;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Prooph\EventStore\EventStoreConnection;
use Symfony\Component\Serializer\SerializerInterface;

class TabRepositoryFactory
{
    private EventStoreConnection $connection;
    /**
     * @var Connection
     */
    private Connection $dbalConnection;
    private SerializerInterface $serializer;

    public function __construct(EventStoreConnection $connection, Connection $dbalConnection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->dbalConnection = $dbalConnection;
        $this->serializer = $serializer;
    }

    public function create(): TabRepositoryEventSauce
    {
        $className = Tab::class;

        $repository = new EventStoreMessageRepository(
            $this->connection,
            new ConstructingMessageSerializer(),
            'tab'
        );

        $snapshotRepository = new RedisSnapshotRepository($this->serializer);
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
                        new TabProjector($this->dbalConnection),
                        new ChefTodoProjector($this->dbalConnection),
                    )
                )
            )
        );
    }
}