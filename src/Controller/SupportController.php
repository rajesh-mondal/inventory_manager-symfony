<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SupportController extends AbstractController
{
    #[Route('/support/ticket', name: 'app_support_ticket', methods: ['POST'])]
    public function createTicket(Request $request, HttpClientInterface $client): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $ticketData = [
            'reported_by'  => $user ? $user->getUserIdentifier() : 'Anonymous',
            'inventory'    => $data['inventory_title'] ?? 'N/A',
            'link'         => $data['current_url'] ?? 'N/A',
            'priority'     => $data['priority'] ?? 'Average',
            'summary'      => $data['summary'] ?? '',
            'admin_emails' => ['admin@yourdomain.com'],
            'timestamp'    => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        $jsonContent = json_encode($ticketData, JSON_PRETTY_PRINT);
        $fileName = 'ticket_' . time() . '.json';

        try {
            $accessToken = $this->getDropboxToken($client);

            $response = $client->request('POST', 'https://content.dropboxapi.com/2/files/upload', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Dropbox-API-Arg' => json_encode([
                        'path' => "/$fileName",
                        'mode' => 'add',
                        'autorename' => true,
                        'mute' => false
                    ]),
                    'Content-Type' => 'application/octet-stream',
                ],
                'body' => $jsonContent
            ]);

            if ($response->getStatusCode() === 200) {
                return new JsonResponse(['status' => 'success']);
            }

            return new JsonResponse([
                'status' => 'error',
                'details' => $response->getContent(false)
            ], $response->getStatusCode());

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'details' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDropboxToken(HttpClientInterface $client): string
    {
        $appKey = $this->getParameter('kernel.dropbox_app_key');
        $appSecret = $this->getParameter('kernel.dropbox_app_secret');
        $refreshToken = $this->getParameter('kernel.dropbox_refresh_token');

        $response = $client->request('POST', 'https://api.dropbox.com/oauth2/token', [
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $appKey,
                'client_secret' => $appSecret,
            ],
        ]);

        $data = $response->toArray();
        return $data['access_token'];
    }
}