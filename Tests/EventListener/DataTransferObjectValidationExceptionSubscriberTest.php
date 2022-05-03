<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Tests\EventListener;

use Chaplean\Bundle\DtoHandlerBundle\EventListener\DataTransferObjectValidationExceptionSubscriber;
use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class DataTransferObjectValidationExceptionSubscriberTest
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Tests\EventListener
 * @author    Nicolas Guilloux <nicolas.guilloux@protonmail.com>
 */
class DataTransferObjectValidationExceptionSubscriberTest extends MockeryTestCase
{
    /**
     * @var DataTransferObjectValidationExceptionSubscriber
     */
    protected $dataTransferObjectValidationExceptionSubscriber;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->dataTransferObjectValidationExceptionSubscriber = new DataTransferObjectValidationExceptionSubscriber();

        parent::setUp();
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\EventListener\DataTransferObjectValidationExceptionSubscriber::getSubscribedEvents()
     *
     * @return void
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey(
            KernelEvents::EXCEPTION,
            DataTransferObjectValidationExceptionSubscriber::getSubscribedEvents()
        );

        self::assertContains(
            'onKernelException',
            DataTransferObjectValidationExceptionSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\EventListener\DataTransferObjectValidationExceptionSubscriber::onKernelException()
     *
     * @return void
     */
    public function testOnKernelExceptionWithAnotherException(): void
    {
        $exception = new BadRequestHttpException();
        $kernel = new HttpKernel(
            new EventDispatcher(),
            new ControllerResolver()
        );

        $event = new ExceptionEvent(
            $kernel,
            new Request(),
            0,
            $exception
        );

        $this->dataTransferObjectValidationExceptionSubscriber
            ->onKernelException($event);

        self::assertNull($event->getResponse());
    }

    /**
     * @covers \Chaplean\Bundle\DtoHandlerBundle\EventListener\DataTransferObjectValidationExceptionSubscriber::onKernelException()
     *
     * @return void
     */
    public function testOnKernelException(): void
    {
        $violation1 = new ConstraintViolation('Bad Value 1', null, [], 'badValue1', 'violation1', 'badValue1');
        $violation2 = new ConstraintViolation('Bad Value 2', null, [], 'badValue2', 'violation2', 'badValue2');

        $violations = new ConstraintViolationList(
            [
                $violation1,
                $violation2
            ]
        );

        $exception = new DataTransferObjectValidationException($violations);

        $kernel = new HttpKernel(
            new EventDispatcher(),
            new ControllerResolver()
        );

        $event = new ExceptionEvent(
            $kernel,
            new Request(),
            0,
            $exception
        );

        $this->dataTransferObjectValidationExceptionSubscriber->onKernelException($event);

        self::assertNotNull($event->getResponse());
    }
}
