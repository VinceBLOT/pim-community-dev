<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Collection normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var array */
    protected $supportedFormat = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($elements, $format = null, array $context = array())
    {
        $normalizedElements = [];

        foreach ($elements as $key => $element) {
            $normalizedElements[$key] = $this->serializer->normalize($element, $format, $context);
        }

        return $normalizedElements;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof \Traversable || is_array($data)) && in_array($format, $this->supportedFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}