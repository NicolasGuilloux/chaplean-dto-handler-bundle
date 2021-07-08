<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\Field;
use Chaplean\Bundle\DtoHandlerBundle\Annotation\MapTo;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Class PropertyConfigurationModel.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Extractor
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class PropertyConfigurationExtractor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $mapTo;

    /**
     * @var string
     */
    private $type;

    /**
     * @var ParamConverter
     */
    private $paramConverterAnnotation;

    /**
     * @var boolean
     */
    private $isOptional;

    /**
     * @var boolean
     */
    private $isCollection;

    /**
     * PropertyConfigurationModel constructor.
     *
     * @param \ReflectionProperty $property
     */
    public function __construct(\ReflectionProperty $property)
    {
        /** @var Field|null $fieldAnnotation */
        $fieldAnnotation = AnnotationFetcher::get($property, Field::class);
        /** @var ParamConverter|null $paramConverterAnnotation */
        $paramConverterAnnotation = AnnotationFetcher::get($property, ParamConverter::class);
        /** @var All|null $arrayAnnotation */
        $arrayAnnotation = AnnotationFetcher::get($property, All::class);
        /** @var Type|null $typeAnnotation */
        $typeAnnotation = AnnotationFetcher::get($property, Type::class);
        /** @var DateTime|null $dateTimeAnnotation */
        $dateTimeAnnotation = AnnotationFetcher::get($property, DateTime::class);
        /** @var Date|null $dateAnnotation */
        $dateAnnotation = AnnotationFetcher::get($property, Date::class);
        /** @var MapTo|null $mapToAnnotation */
        $mapToAnnotation = AnnotationFetcher::get($property, MapTo::class);
        /** @var NotNull|null $notNullAnnotation */
        $notNullAnnotation = AnnotationFetcher::get($property, NotNull::class);
        /** @var NotBlank|null $notBlankAnnotation */
        $notBlankAnnotation = AnnotationFetcher::get($property, NotBlank::class);

        $this->name = $property->getName();
        $this->field = $fieldAnnotation !== null ? $fieldAnnotation->keyname : $property->getName();
        $this->mapTo = $mapToAnnotation !== null ? $mapToAnnotation->keyname : null;
        $this->paramConverterAnnotation = $paramConverterAnnotation;
        $this->isOptional = ($notNullAnnotation === null) && ($notBlankAnnotation === null);
        $this->isCollection = ($arrayAnnotation !== null);

        if ($this->isCollection) {
             $typeAnnotation = $this->findTypeConstraint($arrayAnnotation) ?? $typeAnnotation;
        }

        if ($dateTimeAnnotation !== null || $dateAnnotation !== null) {
            $this->type = \DateTime::class;
        }

        if ($typeAnnotation !== null && \class_exists($typeAnnotation->type)) {
            $this->type = $typeAnnotation->type;
        }
    }

    /**
     * @return string|null
     */
    public function getMapTo(): ?string
    {
        return $this->mapTo;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return ParamConverter|null
     */
    public function getParamConverterAnnotation(): ?ParamConverter
    {
        return $this->paramConverterAnnotation;
    }

    /**
     * @return boolean|null
     */
    public function isOptional(): ?bool
    {
        return $this->isOptional;
    }

    /**
     * @return boolean|null
     */
    public function isCollection(): ?bool
    {
        return $this->isCollection;
    }

    /**
     * @param All $arrayAnnotation
     *
     * @return Type|null
     */
    private function findTypeConstraint(All $arrayAnnotation): ?Type
    {
        foreach ($arrayAnnotation->constraints as $constraint) {
            if ($constraint instanceof Type) {
                return $constraint;
            }
        }

        return null;
    }
}
