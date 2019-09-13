<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\EventListener;

use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DataTransferObjectValidationExceptionSubscriber
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\EventListener
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2019 RichCongress (https://www.richcongress.com)
 */
class DataTransferObjectValidationExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if (!$exception instanceof DataTransferObjectValidationException) {
            return;
        }

        $response = new JsonResponse(
            $exception->getViolationsArray(),
            $exception->getStatusCode()
        );

        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }
}
