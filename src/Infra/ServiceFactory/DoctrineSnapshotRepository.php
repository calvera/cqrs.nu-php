<?php


namespace Cafe\Infra\ServiceFactory;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Symfony\Component\Serializer\SerializerInterface;

class DoctrineSnapshotRepository implements SnapshotRepository
{
    private SerializerInterface $serializer;
    private Connection $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->connection = $connection;
    }

    public function persist(Snapshot $snapshot): void
    {
        $id = $snapshot->aggregateRootId()->toString();
        $version = $snapshot->aggregateRootVersion();
        $type = get_class($snapshot->state());
        $state = $this->serializer->serialize($snapshot->state(), 'json');

        try {
            $this->connection->insert(
                'snapshot',
                [
                    'aggregate_root_id' => $id,
                    'aggregate_root_version' => $version,
                    'aggregate_type' => $type,
                    'payload' => $state,
                ]
            );
        } catch (Exception $e) {
            $this->connection->update(
                'snapshot',
                [
                    'aggregate_root_version' => $version,
                    'aggregate_type' => $type,
                    'payload' => $state,
                ],
                [
                    'aggregate_root_id' => $id,
                ]
            );
        }
    }

    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('payload')
            ->addSelect('aggregate_root_version')
            ->addSelect('aggregate_type')
            ->from('snapshot')
            ->where('aggregate_root_id = :aggregate_root_id')
            ->setParameter('aggregate_root_id', $id->toString());

        $statement = $qb->setMaxResults(1)->execute();

        $row = $statement->fetchAssociative();
        if ($row === false) {
            return null;
        }

        $deserialized = $this->serializer->deserialize($row['payload'], $row['aggregate_type'], 'json');
        return new Snapshot($id, $row['aggregate_root_version'], $deserialized);
    }

}