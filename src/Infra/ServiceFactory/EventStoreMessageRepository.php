<?php


namespace Cafe\Infra\ServiceFactory;


use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Prooph\EventStore\EventData;
use Prooph\EventStore\EventId;
use Prooph\EventStore\EventStoreConnection;
use Prooph\EventStore\ExpectedVersion;
use Ramsey\Uuid\Uuid;

class EventStoreMessageRepository implements MessageRepository
{
    private const SLICE_SIZE = 1;

    protected EventStoreConnection $connection;

    protected MessageSerializer $serializer;

    protected string $tableName;

    private int $jsonEncodeOptions;

    public function __construct(EventStoreConnection $connection, MessageSerializer $serializer, string $tableName, int $jsonEncodeOptions = 0)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->tableName = $tableName;
        $this->jsonEncodeOptions = $jsonEncodeOptions;
    }

    public function persist(Message ...$messages): void
    {
        if (count($messages) === 0) {
            return;
        }

        foreach ($messages as $index => $message) {
            $streamName = $this->getStreamName($message->aggregateRootId());
            $serialized = $this->serializer->serializeMessage($message);
            $serialized['headers'][Header::EVENT_ID] = $serialized['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString();
            $data = new EventData(
                EventId::fromString($serialized['headers'][Header::EVENT_ID]),
                $serialized['headers'][Header::EVENT_TYPE],
                true,
                json_encode($serialized['payload'], JSON_THROW_ON_ERROR | $this->jsonEncodeOptions),
                json_encode($serialized['headers'], JSON_THROW_ON_ERROR | $this->jsonEncodeOptions)
            );
            $this->connection->appendToStream(
                $streamName,
                ExpectedVersion::ANY,
                [$data]
            );
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        return $this->retrieveAllAfterVersion($id, 0);
    }

    public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator
    {
        $currentStart = $aggregateRootVersion;

        while (true) {
            $slice = $this->connection->readStreamEventsForward($this->getStreamName($id), $currentStart, self::SLICE_SIZE);

            foreach ($slice->events() as $event) {
                $recordedEvent = $event->event();
                assert($recordedEvent !== null);
                $payload = [
                    'payload' => json_decode($recordedEvent->data(), true, 512, JSON_THROW_ON_ERROR),
                    'headers' => json_decode($recordedEvent->metadata(), true, 512, JSON_THROW_ON_ERROR),
                ];
                foreach ($this->serializer->unserializePayload($payload) as $message) {
                    assert($message instanceof Message);

                    yield $message;
                }
            }

            if ($slice->isEndOfStream()) {
                break;
            }

            $currentStart += self::SLICE_SIZE;
        }

        if (isset($message)) {
            return $message->header(Header::AGGREGATE_ROOT_VERSION) ?: 0;
        }

        return 0;
    }

    /**
     * @param AggregateRootId $aggregateRootId
     *
     * @return string
     */
    private function getStreamName(AggregateRootId $aggregateRootId): string
    {
        return sprintf('%s-%s', $this->tableName, $aggregateRootId->toString());
    }
}
