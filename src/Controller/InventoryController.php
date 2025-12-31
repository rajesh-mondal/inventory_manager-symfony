<?php

namespace App\Controller;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InventoryController extends AbstractController
{
    #[Route('/inventory/new', name: 'app_inventory_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Only allow logged-in users
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if the form was submitted
        if ($request->isMethod('POST')) {
            $inventory = new Inventory();

            // Get data from the HTML form names
            $inventory->setTitle($request->request->get('title'));
            $inventory->setCategory($request->request->get('category'));
            $inventory->setDescription($request->request->get('description'));

            // Set the creator as the currently logged-in user
            $inventory->setCreator($this->getUser());

            // Save to MySQL
            $entityManager->persist($inventory);
            $entityManager->flush();

            // Redirect to the list page
            return $this->redirectToRoute('app_my_inventories');
        }

        return $this->render('inventory/new.html.twig');
    }

    #[Route('/my-inventories', name: 'app_my_inventories')]
    public function index(\App\Repository\InventoryRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $myInventories = $repo->findBy(
            ['creator' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('inventory/index.html.twig', [
            'inventories' => $myInventories,
        ]);
    }

    #[Route('/inventory/{id}', name: 'app_inventory_show')]
    public function show(Inventory $inventory): Response
    {
        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }
}
