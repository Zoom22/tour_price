<?php

namespace App\EventListener;

use App\Model\Discount;
use App\Model\Price;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

#[AsEventListener(event: ViewEvent::class, method: 'onKernelView')]
class KernelViewEventListener
{
    public function onKernelView(ViewEvent $event): void
    {
        $value = $event->getControllerResult();

        if ($value instanceof Price) {
            $event->setResponse(new JsonResponse([
                'success'   => true,
                'price'     => (float)$value->getPrice(),
                'discounts' => array_map(
                    fn (Discount $discount) => $discount->getDescription(),
                    $value->getAppliedDiscounts()
                ),
            ]));
        }
    }
}
