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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($request->isMethod('POST')) {
            $inventory = new Inventory();
            $inventory->setTitle($request->request->get('title'));
            $inventory->setCategory($request->request->get('category'));
            $inventory->setDescription($request->request->get('description'));
            $inventory->setCreator($this->getUser());

            // Custom ID & Access
            $inventory->setIdPattern($request->request->get('id_pattern'));
            $inventory->setIsPublic($request->request->has('is_public'));

            $rawTags = $request->request->get('tags');

            if ($rawTags) {
                $tagArray = array_map('trim', explode(',', $rawTags));
                $tagArray = array_filter($tagArray);
                $inventory->setTags($tagArray);
            } else {
                $inventory->setTags([]);
            }

            $types = ['String', 'Int', 'Bool', 'Text'];

            foreach ($types as $type) {
                for ($i = 1; $i <= 3; $i++) {
                    $lowerType = strtolower($type);

                    // Match Twig name: custom_string1_state
                    $isChecked = $request->request->has("custom_{$lowerType}{$i}_state");
                    // Match Twig name: custom_string1_name
                    $labelName = $request->request->get("custom_{$lowerType}{$i}_name");

                    $setterState = "setCustom" . $type . $i . "State";
                    $setterName = "setCustom" . $type . $i . "Name";

                    $inventory->$setterState($isChecked);
                    $inventory->$setterName($labelName);
                }
            }

            $entityManager->persist($inventory);
            $entityManager->flush();

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
