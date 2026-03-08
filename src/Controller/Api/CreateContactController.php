<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Message\CreateContactMessage;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateContactController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/contacts', name: 'api_contacts_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/contacts',
        summary: 'Submit a new contact for async processing',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['firstName', 'lastName', 'phoneNumbers'],
                properties: [
                    new OA\Property(
                        property: 'firstName',
                        type: 'string',
                        example: 'John',
                    ),
                    new OA\Property(
                        property: 'lastName',
                        type: 'string',
                        example: 'Doe',
                    ),
                    new OA\Property(
                        property: 'phoneNumbers',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['+1234567890', '+0987654321'],
                    ),
                ],
            ),
        ),
        tags: ['Contacts'],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Contact accepted for processing',
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
            ),
        ],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $errors = $this->validate($data);

        if (count($errors) > 0) {
            return new JsonResponse(
                ['errors' => $errors],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $this->messageBus->dispatch(new CreateContactMessage(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            phoneNumbers: $data['phoneNumbers'],
            ip: $request->getClientIp() ?? '127.0.0.1',
        ));

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    private function validate(array $data): array
    {
        $constraint = new Assert\Collection([
            'firstName' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(max: 255),
            ],
            'lastName' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(max: 255),
            ],
            'phoneNumbers' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new Assert\Count(min: 1),
                new Assert\All([
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                ]),
            ],
        ]);

        $violations = $this->validator->validate($data, $constraint);
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': '
                . $violation->getMessage();
        }

        return $errors;
    }
}
