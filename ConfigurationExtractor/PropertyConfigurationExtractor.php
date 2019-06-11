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

use Chaplean\Bundle\DtoHandlerBundle\Annotation\MapTo;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Constraints\All;
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
     *
     * @throws AnnotationException
     */
    public function __construct(\ReflectionProperty $property)
    {
        $annotationReader = new AnnotationReader();

        /** @var ParamConverter $paramConverterAnnotation */
        $paramConverterAnnotation = $annotationReader->getPropertyAnnotation($property, ParamConverter::class);
        /** @var All $arrayAnnotation */
        $arrayAnnotation = $annotationReader->getPropertyAnnotation($property, All::class);
        /** @var Type $typeAnnotation */
        $typeAnnotation = $annotationReader->getPropertyAnnotation($property, Type::class);
        /** @var MapTo $mapToAnnotation */
        $mapToAnnotation = $annotationReader->getPropertyAnnotation($property, MapTo::class);
        /** @var NotNull $notNullAnnotation */
        $notNullAnnotation = $annotationReader->getPropertyAnnotation($property, NotNull::class);
        /** @var NotBlank $notBlankAnnotation */
        $notBlankAnnotation = $annotationReader->getPropertyAnnotation($property, NotBlank::class);

        $this->name = $property->getName();
        $this->mapTo = $mapToAnnotation !== null ? $mapToAnnotation->keyname : null;
        $this->paramConverterAnnotation = $paramConverterAnnotation;
        $this->isOptional = ($notNullAnnotation === null) && ($notBlankAnnotation === null);
        $this->isCollection = ($arrayAnnotation !== null);

        if ($this->isCollection) {
             $typeAnnotation = $this->findTypeConstraint($arrayAnnotation) ?? $typeAnnotation;
        }

        if ($typeAnnotation !== null && class_exists($typeAnnotation->type)) {
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
