<?php


namespace Cafe\Infra\ServiceFactory;


use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Redis;
use Symfony\Component\Serializer\SerializerInterface;

class RedisSnapshotRepository implements SnapshotRepository
{
    private Redis $redis;
    private SerializerInterface $serializer;
    private int $ttl;

    public function __construct(SerializerInterface $serializer, int $ttl = 3600)
    {
        $this->redis = new Redis();
        $this->redis->connect('redis');
        $this->serializer = $serializer;
        $this->ttl = $ttl;
    }

    public function persist(Snapshot $snapshot): void
    {
        $payload = [
            'version' => $snapshot->aggregateRootVersion(),
            'type' => get_class($snapshot->state()),
            'state' => $this->serializer->serialize($snapshot->state(), 'json'),
        ];
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->redis->setex($this->getKey($snapshot->aggregateRootId()), $this->ttl, $json);
    }

    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        $value = $this->redis->get($id->toString());

        if ($value === false) {
            return null;
        }
        $payload = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        $deserialized = $this->serializer->deserialize($payload['state'], $payload['type'], 'json');

        return new Snapshot($id, $payload['version'], $deserialized);
    }

    private function getKey(AggregateRootId $id): string
    {
        return sprintf('snapshot-%s', $id->toString());
    }

}