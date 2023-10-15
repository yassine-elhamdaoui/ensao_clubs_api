<?php
// src/EventListener/CustomExceptionListener.php
namespace App\EventListener;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CustomExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            // Return a custom JSON response for "Not Found" errors
            $response = new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND,'message' => 'Resource not found'], JsonResponse::HTTP_NOT_FOUND);
            $event->setResponse($response);
        }
        if ($exception instanceof AccessDeniedHttpException) {
            // Return a custom JSON response for "Forbidden" errors
            $response = new JsonResponse(
                ['code' => JsonResponse::HTTP_FORBIDDEN, 'message' => 'Access denied'],
                JsonResponse::HTTP_FORBIDDEN
            );
            $event->setResponse($response);
        }
    }
}
