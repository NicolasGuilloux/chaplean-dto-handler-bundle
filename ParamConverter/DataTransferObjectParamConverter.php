<?php declare(strict_types=1);

/*
 * This file is part of the DtoHandlerBundle package.
 *
 * (c) Chaplean.coop <contact@chaplean.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chaplean\Bundle\DtoHandlerBundle\ParamConverter;

use Chaplean\Bundle\DtoHandlerBundle\Annotation\DTO;
use Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor;
use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class DataTransferObjectParamConverter.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\ParamConverter
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class DataTransferObjectParamConverter implements ParamConverterInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @var ParamConverterManager
     */
    protected $manager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * DataTransferObjectParamConverter constructor.
     *
     * @param ContainerBuilder        $containerBuilder
     * @param ParamConverterManager   $paramConverterManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        ContainerBuilder $containerBuilder,
        ParamConverterManager $paramConverterManager,
        ValidatorInterface $validator = null
    ) {
        $this->containerBuilder = $containerBuilder;
        $this->manager = $paramConverterManager;
        $this->validator = $validator;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return boolean
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $options = (array) $configuration->getOptions();
        $reflectionClass = new \ReflectionClass($configuration->getClass());

        $uuid = \uniqid('', false);
        // Null when the processed DTO is at the top level in case of nested DTO
        $actualDtoName = $request->attributes->get($configuration->getName())
            ? $configuration->getName()
            : null
        ;

        $config = $this->autoConfigure($reflectionClass, $request, $uuid, $actualDtoName);
        $this->manager->apply($request, $config);

        $object = $this->buildObject($request, $configuration, $uuid);
        $request->attributes->set($configuration->getName(), $object);

        // Validate only the top level DTO
        if ($actualDtoName === null) {
            $this->validate($object, $request, $options);
        }

        return true;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return boolean
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function supports(ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();

        if ($class === null) {
            return false;
        }

        $dtoServices = $this->containerBuilder->findTaggedServiceIds('app.data_transfer_object');

        if (\array_key_exists($class, $dtoServices)) {
            return true;
        }

        $propertyReflectionClass = new \ReflectionClass($class);

        $annotationReader = new AnnotationReader();
        $typeAnnotation = $annotationReader->getClassAnnotation($propertyReflectionClass, DTO::class);

        return $typeAnnotation !== null;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param Request          $request
     * @param string           $prefix
     * @param string|null      $dtoName
     *
     * @return array
     *
     * @throws AnnotationException
     */
    private function autoConfigure(\ReflectionClass $reflectionClass, Request $request, string $prefix, ?string $dtoName): array
    {
        $paramConfiguration = [];
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $parameters = new PropertyConfigurationExtractor($property);

            $paramConfiguration = $this->autoConfigureProperty($request, $paramConfiguration, $prefix, $dtoName, $parameters);
        }

        return $paramConfiguration;
    }

    /**
     * @param Request                        $request
     * @param array                          $paramConfiguration
     * @param string                         $prefix
     * @param string|null                    $dtoName
     * @param PropertyConfigurationExtractor $propertyConfigurationModel
     *
     * @return array
     */
    private function autoConfigureProperty(
        Request $request,
        array $paramConfiguration,
        string $prefix,
        ?string $dtoName,
        PropertyConfigurationExtractor $propertyConfigurationModel
    ): array {
        $name = $propertyConfigurationModel->getName();
        $content = $dtoName
            ? ($request->attributes->get($dtoName)[$name] ?? null)
            : $request->request->get($name)
        ;

        if ($propertyConfigurationModel->getParamConverterAnnotation() !== null) {
            $name = $prefix . '_#_' . $propertyConfigurationModel->getName();
            $request->attributes->set($name, $content);
            $paramConfiguration[$name] = $propertyConfigurationModel->getParamConverterAnnotation();
            $paramConfiguration[$name]->setName($name);

            return $paramConfiguration;
        }

        if ($propertyConfigurationModel->isCollection()) {
            $content = $content ?? [];

            foreach ($content as $key => $value) {
                $paramConfiguration = $this->autoConfigureOne(
                    $request,
                    $paramConfiguration,
                    $prefix . '_' . $key,
                    $propertyConfigurationModel,
                    $value
                );
            }

            return $paramConfiguration;
        }

        return $this->autoConfigureOne(
            $request,
            $paramConfiguration,
            $prefix . '_' . '#',
            $propertyConfigurationModel,
            $content
        );
    }

    /**
     * @param Request                        $request
     * @param array                          $paramConfiguration
     * @param string                         $prefix
     * @param PropertyConfigurationExtractor $propertyConfigurationModel
     * @param mixed                          $value
     *
     * @return array
     */
    private function autoConfigureOne(
        Request $request,
        array $paramConfiguration,
        string $prefix,
        PropertyConfigurationExtractor $propertyConfigurationModel,
        $value
    ): array {
        $name = $prefix . '_' . $propertyConfigurationModel->getName();
        $request->attributes->set($name, $value);

        if ($propertyConfigurationModel->getType() === null) {
            return $paramConfiguration;
        }

        $config = new ParamConverter([]);
        $config->setName($name);
        $config->setClass($propertyConfigurationModel->getType());
        $config->setIsOptional($propertyConfigurationModel->isOptional());

        if ($propertyConfigurationModel->getMapTo() !== null) {
            $config->setOptions(
                [
                    'mapping' => [
                        $name => $propertyConfigurationModel->getMapTo()
                    ]
                ]
            );
        }

        $paramConfiguration[$name] = $config;

        return $paramConfiguration;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @param string         $prefix
     *
     * @return mixed
     */
    private function buildObject(Request $request, ParamConverter $configuration, string $prefix)
    {
        $class = $configuration->getClass();
        $object = new $class();

        foreach ($request->attributes as $key => $attribute) {
            if (!\is_string($key)) {
                continue;
            }

            $keyParts = \explode('_', $key);
            $prefixes = \array_slice($keyParts, 0, 2);
            $propertyName = \implode('_', \array_slice($keyParts, 2));

            if ($prefixes[0] !== $prefix || \count($keyParts) < 3) {
                continue;
            }

            $request->attributes->remove($key);

            if ($prefixes[1] === '#') {
                $object->$propertyName = $attribute;
            } else {
                $property = $object->$propertyName ?? [];
                $property[] = $attribute;
                $object->$propertyName = $property;
            }
        }

        return $object;
    }

    /**
     * @param mixed   $object
     * @param Request $request
     * @param array   $options
     *
     * @return void
     *
     * @throws BadRequestHttpException
     */
    private function validate($object, Request $request, array $options): void
    {
        if ($this->validator === null || !($options['validate'] ?? true)) {
            return;
        }

        $validationHandler = $options['violations'] ?? false;
        $groups = $options['groups'] ?? null;

        $violations = $this->validator->validate($object, null, $groups);

        if (!$validationHandler && $violations->count() > 0) {
            throw new DataTransferObjectValidationException($violations);
        }

        $request->attributes->set(
            $validationHandler,
            $violations
        );
    }
}
