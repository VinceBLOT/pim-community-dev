<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Localization\Validator\Constraints\DateFormat;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PIM date type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateType extends OroDateType
{
    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param LocaleResolver $localeResolver
     */
    public function __construct(LocaleResolver $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $localeOptions = $this->localeResolver->getFormats();
        $resolver->setDefault('format', $localeOptions['date_format']);

        $constraint = new DateFormat();

        $resolver->setDefaults(
            [
                'invalid_message'            => $constraint->message,
                'invalid_message_parameters' => ['{{ date_format }}' => $localeOptions['date_format']],
                'locale_options'             => $localeOptions
            ]
        );
    }
}
