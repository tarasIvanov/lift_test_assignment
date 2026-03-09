<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\CreateContactRequestDTO;
use App\Service\ContactService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contacts')]
final readonly class ContactController
{
    public function __construct(
        private ContactService $contactService,
    ) {
    }

    #[Route('', name: 'api_contacts_create', methods: ['POST'])]
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
    public function create(
        #[MapRequestPayload] CreateContactRequestDTO $dto,
        Request $request,
    ): JsonResponse {
        $this->contactService->createContact($dto, $request->getClientIp() ?? '127.0.0.1');

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    #[Route('', name: 'api_contacts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/contacts',
        summary: 'Retrieve all contacts with phone numbers',
        tags: ['Contacts'],
        parameters: [
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field (lastName, firstName, createdAt)',
                in: 'query',
                schema: new OA\Schema(type: 'string', default: 'lastName'),
            ),
            new OA\Parameter(
                name: 'order',
                description: 'Sort order',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    default: 'asc',
                    enum: ['asc', 'desc'],
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of contacts',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1,
                            ),
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
                                property: 'country',
                                type: 'string',
                                example: 'United States',
                                nullable: true,
                            ),
                            new OA\Property(
                                property: 'createdAt',
                                type: 'string',
                                format: 'date-time',
                                example: '2026-03-09T12:00:00+00:00',
                            ),
                            new OA\Property(
                                property: 'phoneNumbers',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['+1234567890'],
                            ),
                        ],
                    ),
                ),
            ),
        ],
    )]
    public function list(Request $request): JsonResponse
    {
        $sort = $request->query->getString('sort', 'lastName');
        $order = $request->query->getString('order', 'asc');

        $contacts = $this->contactService->getListOfContacts($sort, $order);

        return new JsonResponse(
            array_map(static fn ($contact) => (array) $contact, $contacts),
            Response::HTTP_OK,
        );
    }
}
