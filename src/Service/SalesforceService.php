<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SalesforceService
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private string $clientId,
        private string $clientSecret,
        private string $username,
        private string $password,
        private string $loginUrl
    ) {}

    public function syncUserToSalesforce(User $user, string $companyName): void
    {
        try {
            // Get OAuth2 Access Token
            $authResponse = $this->client->request('POST', $this->loginUrl . '/services/oauth2/token', [
                'body' => [
                    'grant_type'    => 'password',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username'      => $this->username,
                    'password'      => $this->password,
                ]
            ]);

            $authData = $authResponse->toArray(false);

            if (isset($authData['error'])) {
                throw new \Exception("Salesforce Auth Failed: " . ($authData['error_description'] ?? $authData['error']));
            }

            $accessToken = $authData['access_token'];
            $instanceUrl = $authData['instance_url'];

            // Create the Salesforce Account
            $accResponse = $this->client->request('POST', $instanceUrl . '/services/data/v60.0/sobjects/Account', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => ['Name' => $companyName]
            ]);

            $accData = $accResponse->toArray(false);
            if (!isset($accData['id'])) {
                throw new \Exception("Account Creation Failed: " . json_encode($accData));
            }

            $accountId = $accData['id'];
            $user->setSalesforceAccountId($accountId);

            // Create the Salesforce Contact linked to the Account
            $conResponse = $this->client->request('POST', $instanceUrl . '/services/data/v60.0/sobjects/Contact', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'FirstName' => $user->getFirstName(),
                    'LastName'  => $user->getLastName(),
                    'Email'     => $user->getEmail(),
                    'AccountId' => $accountId
                ]
            ]);

            $conData = $conResponse->toArray(false);
            if (!isset($conData['id'])) {
                throw new \Exception("Contact Creation Failed: " . json_encode($conData));
            }

            $user->setSalesforceContactId($conData['id']);

            // Update local database
            $this->entityManager->persist($user);
            $this->entityManager->flush();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}