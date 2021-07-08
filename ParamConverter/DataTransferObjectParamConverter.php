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

use Chaplean\Bundle\DtoHandlerBundle\ConfigurationExtractor\PropertyConfigurationExtractor;
use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Doctrine\Common\Annotations\AnnotationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DataTransferObjectParamConverter.
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\ParamConverter
 * @author    Nicolas - Chaplean <nicolas@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class DataTransferObjectParamConverter implements ParamConverterInterface
{
    public const MULTIPART_FORM_DATA = 'multipart/form-data';
    public const JSON_MIME_TYPE = ['application/json', 'text/json'];
    public const SUB_REQUEST_HEADER = 'subRequestDtoHandler';

    /**
     * @var array
     */
    protected $bypassParamConverterExceptionClasses;

    /**
     * @var array
     */
    protected $httpValidationGroups;

    /**
     * @var ParamConverterManager
     */
    protected $manager;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var array
     */
    protected $taggedDtoClasses;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * DataTransferObjectParamConverter constructor.
     *
     * @param array                   $bypassParamConverterExceptionClasses
     * @param array                   $httpValidationGroups
     * @param ParamConverterManager   $paramConverterManager
     * @param TranslatorInterface     $translator
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        array $bypassParamConverterExceptionClasses,
        array $httpValidationGroups,
        ParamConverterManager $paramConverterManager,
        TranslatorInterface $translator,
        ValidatorInterface $validator = null
    ) {
        $this->bypassParamConverterExceptionClasses = $bypassParamConverterExceptionClasses;
        $this->manager = $paramConverterManager;
        $this->validator = $validator;
        $this->taggedDtoClasses = [];
        $this->translator = $translator;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        usort(
            $httpValidationGroups,
            static function ($group1, $group2) {
                return ($group1['priority'] ?? 0) < ($group2['priority'] ?? 0);
            }
        );

        $this->httpValidationGroups = $httpValidationGroups;
    }

    /**
     * @param array $taggedServices
     *
     * @return self
     */
    public function setTaggedDtoServices(array $taggedServices): self
    {
        $this->taggedDtoClasses = \array_keys($taggedServices);

        return $this;
    }

    /**
     * @param Request        $originalRequest
     * @param ParamConverter $configuration
     *
     * @return boolean
     */
    public function apply(Request $originalRequest, ParamConverter $configuration): bool
    {
        $isSubDto = $originalRequest->headers->has(static::SUB_REQUEST_HEADER);

        if (!$isSubDto) {
            $this->extractDataFromMultipartBody($originalRequest);
        } else if ($originalRequest->attributes->get($configuration->getName()) === null) {
            return false;
        }

        $request = self::cloneRequest($originalRequest);
        $options = (array) $configuration->getOptions();
        $reflectionClass = new \ReflectionClass($configuration->getClass());
        $uuid = \uniqid('', false);

        // Null when the processed DTO is at the top level in case of nested DTO
        $actualDtoName = $request->attributes->get($configuration->getName())
            ? $configuration->getName()
            : null
        ;

        $config = $this->autoConfigure($reflectionClass, $request, $uuid, $actualDtoName);

        // Validate raw input only the top level DTO
        if (!$isSubDto) {
            $object = $this->buildObject($request, $configuration, $uuid, true);

            $preValidationOptions = $options;
            $preValidationOptions['groups'] = ['dto_raw_input_validation'];

            $this->validate($object, $request, $preValidationOptions);
        }

        $this->applyParamConverters($request, $config);

        $object = $this->buildObject($request, $configuration, $uuid);
        $originalRequest->attributes->set($configuration->getName(), $object);

        // Validate only the top level DTO and load it to the real request
        if (!$isSubDto) {
            $this->validate($object, $originalRequest, $options);
        }

        return true;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return boolean
     */
    public function supports(ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();

        if ($class === null) {
            return false;
        }

        return is_subclass_of($class, DataTransferObjectInterface::class);
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
    protected function autoConfigure(\ReflectionClass $reflectionClass, Request $request, string $prefix, ?string $dtoName): array
    {
        $paramConfiguration = [];
        $properties = $reflectionClass->getProperties();

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
    protected function autoConfigureProperty(
        Request $request,
        array $paramConfiguration,
        string $prefix,
        ?string $dtoName,
        PropertyConfigurationExtractor $propertyConfigurationModel
    ): array {
        $field =  $propertyConfigurationModel->getField();
        $content = $dtoName
            ? ($request->attributes->get($dtoName)[$field] ?? null)
            : self::getValueFromRequest($request, $field)
        ;

        if ($propertyConfigurationModel->getParamConverterAnnotation() !== null) {
            $name = $prefix . '_#_' . $propertyConfigurationModel->getName();
            $request->attributes->set($name, $content);
            $paramConfiguration[$name] = $propertyConfigurationModel->getParamConverterAnnotation();
            $paramConfiguration[$name]->setName($name);

            return $paramConfiguration;
        }

        if ($propertyConfigurationModel->isCollection()) {
            $content = ($content !== '') ? $content ?? [] : [];

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
    protected function autoConfigureOne(
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
        $config->setIsOptional(true);

        if (!\in_array($propertyConfigurationModel->getType(), $this->bypassParamConverterExceptionClasses, true)) {
            $config->setIsOptional($propertyConfigurationModel->isOptional());
        }

        if ($propertyConfigurationModel->getMapTo() !== null) {
            $config->setOptions(
                [
                    'strip_null' => true,
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
     * @param Request $request
     * @param array   $configurations
     *
     * @return void
     */
    protected function applyParamConverters(Request $request, array $configurations): void
    {
        $errors = new ConstraintViolationList();

        /** @var ParamConverter $paramConverterConfiguration */
        foreach ($configurations as $paramConverterConfiguration) {
            try {
                $this->manager->apply($request, $paramConverterConfiguration);
            } catch (DataTransferObjectValidationException $e) {
                $errors->addAll($e->getViolations());
            } catch (\Exception $e) {
                $errors->add($this->getViolationFromException($request, $e, $paramConverterConfiguration));
            }
        }

        if ($errors->count() !== 0) {
            throw new DataTransferObjectValidationException($errors, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param Request        $request
     * @param \Exception     $exception
     * @param ParamConverter $paramConverter
     *
     * @return ConstraintViolation
     */
    protected function getViolationFromException(Request $request, \Exception $exception, ParamConverter $paramConverter): ConstraintViolation
    {
        $name = $paramConverter->getName();
        $message = $exception->getMessage();
        $value = $request->attributes->get($name);
        $keyParts = \explode('_', $name);
        $propertyName = \implode('_', \array_slice($keyParts, 2));

        if ($exception instanceof NotFoundHttpException) {
            $message = $this->translator->trans('dto_handler.entity_not_found', ['%value%' => $value]);
        }

        return new ConstraintViolation(
            $message,
            null,
            [],
            null,
            $propertyName,
            $value,
            null,
            (string) $exception->getCode()
        );
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     * @param string         $prefix
     * @param bool           $keepAttributes
     *
     * @return mixed
     */
    protected function buildObject(Request $request, ParamConverter $configuration, string $prefix, bool $keepAttributes = false)
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

            if (!$keepAttributes) {
                $request->attributes->remove($key);
            }

            try {
                if ($prefixes[1] === '#') {
                    $this->propertyAccessor->setValue($object, $propertyName, $attribute);
                } else {
                    $property = $this->propertyAccessor->getValue($object, $propertyName) ?? [];
                    $property[] = $attribute;
                    $this->propertyAccessor->setValue($object, $propertyName, $property);
                }
            } catch (NoSuchPropertyException $e) {
                // Cannot write into the property, skip it
                continue;
            }
        }

        return $object;
    }

    /**
     * Searches for a value at $key in $request, if not found returns $default.
     *
     * Search order is:
     *   1) $request->request
     *   2) $request->attributes
     *   3) $request->query
     *   4) $request->cookies
     *
     * @param Request    $request
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    protected static function getValueFromRequest(Request $request, string $key, $default = null)
    {
        if ($request !== $result = $request->request->get($key, $request)) {
            return $result;
        }

        if ($request !== $result = $request->get($key, $request)) {
            return $result;
        }

        return $request->cookies->get($key, $default);
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
    protected function validate($object, Request $request, array $options): void
    {
        if ($this->validator === null || !($options['validate'] ?? true)) {
            return;
        }

        $violations = new ConstraintViolationList();
        $violationsHandler = $options['violations'] ?? '';
        $groups = $options['groups'] ?? null;

        if ($groups !== null) {
            $groups = [
                [
                    'validation_group' => $groups,
                    'http_status_code' => null
                ]
            ];
        }

        foreach ($groups ?? $this->httpValidationGroups as $group) {
            $violations->addAll(
                $this->validator->validate($object, null, $group['validation_group'])
            );

            if ($violationsHandler === '' && $violations->count() > 0) {
                throw new DataTransferObjectValidationException($violations, $group['http_status_code']);
            }
        }

        $request->attributes->set(
            $violationsHandler,
            $violations
        );
    }

    /**
     * @param Request $request
     */
    private function extractDataFromMultipartBody(Request $request): void
    {
        if (!$this->isMultipart($request)) {
            return;
        }

        foreach ($request->files->all() as $key => $file) {
            if (!$this->isJson($file)) {
                continue;
            }

            $json = json_decode(file_get_contents($file->getPathname()), true) ?? [];

            $request->attributes->add($json);
            $request->files->remove($key);
        }
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isMultipart(Request $request): bool
    {
        $contentType = $request->headers->get('CONTENT_TYPE', '');
        $prefix = explode(';', $contentType)[0];

        return $prefix === self::MULTIPART_FORM_DATA;
    }

    /**
     * @param UploadedFile $file
     *
     * @return bool
     */
    private function isJson($file): bool
    {
        return in_array($file->getClientMimeType(), self::JSON_MIME_TYPE, true);
    }

    /**
     * @param Request $originalRequest
     *
     * @return Request
     */
    private static function cloneRequest(Request $originalRequest): Request
    {
        $request = clone $originalRequest;
        $request->headers->set(static::SUB_REQUEST_HEADER, true);

        return $request;
    }
}
