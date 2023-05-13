<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\Payment\PaymentDTO;
use App\Service\Payment\Processor as PaymentProcessor;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Payment extends AbstractController
{
    private const PAYMENT_FAILED_CODE = 422;

    public function __construct(
        private readonly PaymentProcessor $paymentProcessor
    ) {
    }

    /**
     * Payment API.
     */
    #[Route('/api/payment', name: 'app_payment', methods: ['POST'])]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: PaymentDTO::class)))]
    #[OA\Response(
        response: 200,
        description: 'Payment Successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 200),
                new OA\Property(property: 'message', example: 'Payment Successful'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Payment Failed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 422),
                new OA\Property(property: 'message', example: 'Payment Failed'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorised Response',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 401),
                new OA\Property(property: 'message', example: 'Invalid JWT token'),
            ]
        )
    )]
    #[OA\Tag(name: 'Payment')]
    #[Security(name: 'Bearer')]
    public function execute(Request $request): JsonResponse
    {
        try {
            $paymentSuccess = $this->paymentProcessor->processPayment(
                PaymentDTO::createFromRequestContent($request->getContent())
            );
        } catch (\Exception) {
            return $this->json($this->getPaymentFailedData(), self::PAYMENT_FAILED_CODE);
        }

        return $paymentSuccess ?
            $this->json(['message' => 'Payment Successful', 'code' => 200]) :
            $this->json($this->getPaymentFailedData(), self::PAYMENT_FAILED_CODE)
        ;
    }

    /**
     * @return array<string, string|int>
     */
    private function getPaymentFailedData(): array
    {
        return [
            'message' => 'Payment Failed',
            'code' => self::PAYMENT_FAILED_CODE,
        ];
    }
}
