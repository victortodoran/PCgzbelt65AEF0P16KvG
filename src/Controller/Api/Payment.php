<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Payment\PaymentDTO;
use App\Service\Payment\Processor as PaymentProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Payment extends AbstractController
{
    public function __construct(
        private readonly PaymentProcessor $paymentProcessor
    ) {
    }

    #[Route('/api/payment', name: 'app_payment', methods: ['POST'])]
    public function execute(Request $request): JsonResponse
    {
        try {
            return $this->paymentProcessor->processPayment(
                PaymentDTO::createFromRequestContent($request->getContent())
            ) ? $this->json('Success') : $this->json('Payment Failed', 422);
        } catch (\Exception) {
            return $this->json('Payment Failed', 422);
        }
    }
}
