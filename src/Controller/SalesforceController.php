<?php

namespace App\Controller;

use App\Form\SalesforceSyncType;
use App\Service\SalesforceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SalesforceController extends AbstractController
{
    #[Route('/profile/salesforce-sync', name: 'app_salesforce_sync')]
    #[IsGranted('ROLE_USER')]
    public function sync(
        Request $request,
        SalesforceService $sfService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(SalesforceSyncType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if user is already synced to avoid duplicates
            if ($user->getSalesforceContactId()) {
                $this->addFlash('info', 'This profile is already linked to Salesforce.');

                return $this->render('salesforce/sync.html.twig', [
                    'syncForm' => $form->createView(),
                ]);
            }

            // Save names to local DB
            $entityManager->persist($user);
            $entityManager->flush();

            $companyName = $form->get('companyName')->getData();

            try {
                $sfService->syncUserToSalesforce($user, $companyName);

                $this->addFlash('success', 'Successfully synchronized with Salesforce CRM!');
                // return $this->redirectToRoute('app_my_inventories');
            } catch (\Exception $e) {
                $message = "An error occurred during synchronization.";

                if (str_contains($e->getMessage(), 'DUPLICATES_DETECTED')) {
                    $message = "A matching contact already exists in Salesforce.";
                }

                $this->addFlash('error', $message);
            }
        }

        return $this->render('salesforce/sync.html.twig', [
            'syncForm' => $form->createView(),
        ]);
    }
}