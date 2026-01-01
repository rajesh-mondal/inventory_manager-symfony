<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    #[Route('/inventory/{id}/item/new', name: 'app_item_new')]
    public function new(Inventory $inventory, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($request->isMethod('POST')) {
            $item = new Item();
            $item->setInventory($inventory);
            $item->setName($request->request->get('name'));

            // Dynamic Custom Fields
            $types = ['String', 'Int', 'Bool', 'Text'];
            foreach ($types as $type) {
                for ($i = 1; $i <= 3; $i++) {
                    $lowerType = strtolower($type);
                    $stateMethod = "isCustom{$type}{$i}State";

                    // Only process if the template has this field enabled
                    if ($inventory->$stateMethod()) {
                        $setter = "set" . $type . "Val" . $i;

                        if ($type === 'Bool') {
                            $item->$setter($request->request->has("custom_{$lowerType}{$i}"));
                        } else {
                            $item->$setter($request->request->get("custom_{$lowerType}{$i}"));
                        }
                    }
                }
            }

            // Generate Custom ID based on Pattern
            $pattern = $inventory->getIdPattern() ?: 'ITEM-{SEQ}';

            // Get the current number of items in this inventory from the database
            $itemCount = $em->getRepository(Item::class)->count(['inventory' => $inventory]);
            $nextSeq = $itemCount + 1;

            // Replace placeholders
            $customId = str_replace(
                ['{SEQ}', '{YEAR}'],
                [str_pad($nextSeq, 4, '0', STR_PAD_LEFT), date('Y')],
                $pattern
            );

            $item->setCustomId($customId);

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
        }

        return $this->render('item/new.html.twig', [
            'inventory' => $inventory,
        ]);
    }
}