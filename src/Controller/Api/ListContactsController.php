<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ContactRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListContactsController
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
    ) {
    }

    #[Route('/api/contacts', name: 'api_contacts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/contacts',
        summary: 'Retrieve all contacts with phone numbers',
        tags: ['Contacts'],
        parameters: [
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Sort field (lastName, firstName, createdAt)',
                schema: new OA\Schema(type: 'string', default: 'lastName'),
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'Sort order',
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
    public function __invoke(Request $request): JsonResponse
    {
        $sort = $request->query->getString('sort', 'lastName');
        $order = $request->query->getString('order', 'asc');

        $contacts = $this->contactRepository->findAllSorted($sort, $order);

        $result = [];
        foreach ($contacts as $contact) {
            $phoneNumbers = [];
            foreach ($contact->getPhoneNumbers() as $phone) {
                $phoneNumbers[] = $phone->getNumber();
            }

            $result[] = [
                'id' => $contact->getId(),
                'firstName' => $contact->getFirstName(),
                'lastName' => $contact->getLastName(),
                'ip' => $contact->getIp(),
                'country' => $contact->getCountry(),
                'phoneNumbers' => $phoneNumbers,
            ];
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
