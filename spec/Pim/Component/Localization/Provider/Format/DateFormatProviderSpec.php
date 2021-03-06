<?php

namespace spec\Pim\Component\Localization\Provider\Format;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\DateFormatConverter;

class DateFormatProviderSpec extends ObjectBehavior
{
    function let(DateFormatConverter $converter)
    {
        $this->beConstructedWith(
            $converter,
            [
                'en_US' => 'n/j/y',
                'fr_FR' => 'd/m/Y',
            ]
        );
    }

    function it_should_return_known_formats()
    {
        $this->getFormat('en_US')->shouldReturn('n/j/y');
        $this->getFormat('fr_FR')->shouldReturn('d/m/Y');
    }

    function it_should_return_unknown_format(DateFormatConverter $converter)
    {
        $converter->convert('dd/MM/yy')->willReturn('d/m/y');
        $this->getFormat('zh_SG')->shouldReturn('d/m/y');
    }
}
