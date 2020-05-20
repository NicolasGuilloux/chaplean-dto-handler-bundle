<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Serializer;

use Chaplean\Bundle\DtoHandlerBundle\DataTransferObject\DataTransferObjectInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Class DataTransferObjectDenormalizer
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Serializer
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2020 RichCongress (https://www.richcongress.com)
 */
class DataTransferObjectDenormalizer implements DenormalizerInterface
{
    public const VALIDATION_CONTEXT_KEY = '_dto_validation';

    /**
     * @var ParamConverterManager
     */
    protected $paramConverterManager;

    /**
     * DtoUtility constructor.
     *
     * @param ParamConverterManager $paramConverterManager
     */
    public function __construct(ParamConverterManager $paramConverterManager)
    {
        $this->paramConverterManager = $paramConverterManager;
    }

    /**
     * @param mixed  $data
     * @param string $type
     * @param null   $format
     * @param array  $context
     *
     * @return array|object|void
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $request = new Request(
            [],
            $data,
            [$type => 'dto']
        );

        $config = new ParamConverter([]);
        $config->setName('dto');
        $config->setClass($type);
        $config->setIsOptional(false);
        $config->setConverter('data_transfer_object_converter');
        $config->setOptions([
            'validate' => $context[self::VALIDATION_CONTEXT_KEY] ?? true
        ]);

        $this->paramConverterManager->apply($request, $config);

        return $request->get('dto');
    }

    /**
     * @param mixed  $data
     * @param string $type
     * @param null   $format
     *
     * @return bool|void
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_array($data) && is_subclass_of($type, DataTransferObjectInterface::class);
    }
}
