<?php


namespace App\Services;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CustomSerializer
{
    public function serialize($dataToSerialize, array $ignoredAttributes)
    {
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        return $serializer->serialize($dataToSerialize, 'json', ['ignored_attributes' => $ignoredAttributes]);
    }

    public function serializeByGroups($dataToSerialize, array $groups)
    {
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            'groups' => $groups
        ];

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize(
            $dataToSerialize,
            'json',
            $defaultContext
        );
        return $data;
    }
}