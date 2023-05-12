<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\DTO\Auth\NewUserDTO;
use App\Exception\ValidationException;
use App\Service\Auth\Registrant;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly Registrant $registrant
    ) {
    }

    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function execute(Request $request): JsonResponse
    {
        try {
            $newUserDTO = NewUserDTO::fromRequestContent($request->getContent());
            $this->registrant->registerUser($newUserDTO);
        } catch (JsonException) {
            return $this->json('Request contains invalid JSON', 400);
        } catch (ValidationException $exception) {
            return $this->json($exception->getMessage(), 400);
        }

        return $this->json('Success');
    }
}
