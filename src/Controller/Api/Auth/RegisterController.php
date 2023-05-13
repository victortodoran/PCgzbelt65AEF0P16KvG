<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\DTO\Auth\NewUserDTO;
use App\Exception\ValidationException;
use App\Service\Auth\Registrant;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly Registrant $registrant
    ) {
    }

    /**
     * Register User.
     *
     * Register a new user
     */
    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: NewUserDTO::class)))]
    #[OA\Response(
        response: 201,
        description: 'Register Successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 201),
                new OA\Property(property: 'message', example: 'User Created'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Request Content Contains Invalid Data',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 400),
                new OA\Property(property: 'message', example: 'Request Content Contains Invalid Data'),
            ]
        )
    )]
    #[OA\Tag(name: 'Auth')]
    public function execute(Request $request): JsonResponse
    {
        try {
            $newUserDTO = NewUserDTO::fromRequestContent($request->getContent());
            $this->registrant->registerUser($newUserDTO);
        } catch (\JsonException|BadRequestHttpException|ValidationException) {
            return $this->json('Request Content Contains Invalid Data', 400);
        }

        return $this->json('User Created', 201);
    }
}
