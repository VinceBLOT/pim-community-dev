<?php

namespace Pim\Component\Localization\Factory;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The NumberFactory create instances of NumberFormatter, taking account of predefined formats.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFactory
{
    /** @var array */
    protected $currencyFormats;

    /**
     * @param array $currencyFormats
     */
    public function __construct(array $currencyFormats)
    {
        $this->currencyFormats = $currencyFormats;
    }

    /**
     * Creates a number formatter according to options and with predefined formats.
     *
     * @param array $options
     *
     * @return \NumberFormatter
     */
    public function create($options)
    {
        $options = $this->resolve($options);

        $formatter = new \NumberFormatter($options['locale'], $options['type']);

        if (null !== $options['number_format']) {
            $formatter->setPattern($options['number_format']);
        }

        return $formatter;
    }

    /**
     * Resolve the options for the factory instances.
     *
     * @param $options
     * @return array
     */
    protected function resolve($options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired('type')
            ->setDefaults(['locale' => 'en', 'number_format' => null])
            ->setNormalizer('number_format', function () use ($options) {
                if (isset($options['locale']) && isset($this->currencyFormats[$options['locale']])) {
                    return $this->currencyFormats[$options['locale']];
                }

                if (isset($options['number_format'])) {
                    return $options['number_format'];
                }
            });

        return $resolver->resolve($options);
    }
}
