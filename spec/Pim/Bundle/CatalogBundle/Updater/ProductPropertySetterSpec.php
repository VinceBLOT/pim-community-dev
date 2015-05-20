<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\AttributeSetterInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;
use Prophecy\Argument;

class ProductPropertySetterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        SetterRegistryInterface $setterRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $setterRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductPropertySetter');
    }

    function it_sets_a_data_to_a_product_attribute(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $attribute,
        AttributeSetterInterface $setter
    ) {
        $attributeRepository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $setterRegistry->getAttributeSetter($attribute)->willReturn($setter);
        $setter
            ->setAttributeData($product, $attribute, 'my name', [])
            ->shouldBeCalled();

        $this->setData($product, 'name', 'my name', []);
    }

    function it_sets_a_data_to_a_product_field(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product,
        FieldSetterInterface $setter
    ) {
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $setterRegistry->getFieldSetter('category')->willReturn($setter);
        $setter
            ->setFieldData($product, 'category', ['tshirt'], [])
            ->shouldBeCalled();

        $this->setData($product, 'category', ['tshirt'], []);
    }

    function it_throws_an_exception_when_it_sets_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('No setter found for field "unknown_field"'))->during(
            'setData', [$product, 'unknown_field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]
        );
    }
}