<?php

namespace App\EventListener;

use App\Exception\FormValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(event: ExceptionEvent::class, method: 'onKernelException')]
class FormValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = new JsonResponse([
            'success' => false,
            'errors'  => [
                'В настоящее время рассчитать стоимость путешествия не возможно. Попробуйте оправить запрос позже.',
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        if ($exception instanceof FormValidationException) {
            $response = new JsonResponse(
                ['success' => false, 'errors' => $exception->getErrors()],
                Response::HTTP_BAD_REQUEST
            );
        }

        //TODO: добавить логирование
        $event->setResponse($response);
    }
}
