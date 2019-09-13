<?php declare(strict_types=1);

namespace Chaplean\Bundle\DtoHandlerBundle\Tests\EventListener;

use Chaplean\Bundle\DtoHandlerBundle\EventListener\DataTransferObjectValidationExceptionSubscriber;
use Chaplean\Bundle\DtoHandlerBundle\Exception\DataTransferObjectValidationException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class DataTransferObjectValidationExceptionSubscriberTest
 *
 * @package   Chaplean\Bundle\DtoHandlerBundle\Tests\EventListener
 * @author    Nicolas Guilloux <nguilloux@richcongress.com>
 * @copyright 2014 - 2019 RichCongress (https://www.richcongress.com)
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
    public function setUp()
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

        $event = \Mockery::mock(GetResponseForExceptionEvent::class);
        $event->shouldNotReceive('setResponse');
        $event->shouldReceive('getException')
            ->once()
            ->andReturn($exception);

        $this->dataTransferObjectValidationExceptionSubscriber
            ->onKernelException($event);
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

        $event = \Mockery::mock(GetResponseForExceptionEvent::class);

        $event->shouldReceive('getException')
            ->once()
            ->andReturn($exception);

        $event->shouldReceive('setResponse')
            ->once();

        $this->dataTransferObjectValidationExceptionSubscriber
            ->onKernelException($event);
    }
}
