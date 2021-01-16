<?php


namespace Cafe\Infra;


use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\UuidAggregateRootId;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AggregateRootIdNormalizer implements NormalizerInterface, DenormalizerInterface
{

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return new UuidAggregateRootId($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_string($data) and $type === AggregateRootId::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        assert($object instanceof UuidAggregateRootId);

        return $object->toString();
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof UuidAggregateRootId;
    }
}