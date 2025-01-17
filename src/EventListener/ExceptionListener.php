<?php 
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // Pobierz wyjątek
        $exception = $event->getThrowable();

        // Domyślny status code
        $statusCode = 500;
        $message = 'An error occurred';

        // Obsługa różnych typów wyjątków
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } else {
            // Jeżeli to nie jest wyjątek HTTP, ustawiamy błąd ogólny
            $message = 'An internal error occurred';
        }

        // Przygotowanie odpowiedzi
        $response = new JsonResponse(
            ['message' => $message],
            $statusCode
        );

        // Ustawienie odpowiedzi w obiekcie Event
        $event->setResponse($response);
    }
}
