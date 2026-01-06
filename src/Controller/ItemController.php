<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ItemController extends AbstractController
{
    #[Route('/inventory/{id}/item/new', name: 'app_item_new')]
    public function new(Inventory $inventory, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // Authenticated users can add items only if inventory is public.
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        if ($request->isMethod('POST')) {
            $item = new Item();
            $item->setInventory($inventory);
            $item->setName($request->request->get('name'));

            $types = ['String', 'Int', 'Bool', 'Text'];
            foreach ($types as $type) {
                for ($i = 1; $i <= 3; $i++) {
                    $lowerType = strtolower($type);
                    $stateMethod = "isCustom{$type}{$i}State";

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

            $pattern = $inventory->getIdPattern() ?: 'ITEM-{SEQ}';

            $itemCount = $em->getRepository(Item::class)->count(['inventory' => $inventory]);
            $nextSeq = $itemCount + 1;

            $customId = str_replace(
                ['{SEQ}', '{YEAR}'],
                [str_pad($nextSeq, 4, '0', STR_PAD_LEFT), date('Y')],
                $pattern
            );

            $item->setCustomId($customId);

            $imageFile = $request->files->get('image');

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('items_directory'),
                        $newFilename
                    );
                    $item->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Upload failed: ' . $e->getMessage());

                    return $this->render('item/new.html.twig', [
                        'inventory' => $inventory,
                    ]);
                }
            }

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
        }

        return $this->render('item/new.html.twig', [
            'inventory' => $inventory,
        ]);
    }

    #[Route('/item/bulk-delete', name: 'app_item_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request, EntityManagerInterface $em): Response
    {
        $itemIds = $request->request->all('item_ids');

        if ($itemIds) {
            $items = $em->getRepository(Item::class)->findBy(['id' => $itemIds]);
            foreach ($items as $item) {
                // Only creators/admins can delete items, even in public inventories.
                $this->denyAccessUnlessGranted('INVENTORY_DELETE', $item->getInventory());

                if ($item->getImage()) {
                    $imagePath = $this->getParameter('items_directory') . '/' . $item->getImage();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $em->remove($item);
            }
            $em->flush();
            $this->addFlash('success', count($items) . ' items deleted.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/item/bulk-edit', name: 'app_item_bulk_edit', methods: ['POST'])]
    public function bulkEdit(Request $request, EntityManagerInterface $em): Response
    {
        $itemIds = $request->request->all('item_ids');
        if (!$itemIds) return $this->redirect($request->headers->get('referer'));

        $items = $em->getRepository(Item::class)->findBy(['id' => $itemIds]);
        $inventory = $items[0]->getInventory();

        // Authenticated users can EDIT items if inventory is public.
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        $isSingle = count($items) === 1;
        $itemData = $isSingle ? $items[0] : null;

        return $this->render('item/bulk_edit.html.twig', [
            'items' => $items,
            'inventory' => $inventory,
            'isSingle' => $isSingle,
            'itemData' => $itemData,
        ]);
    }

    #[Route('/item/bulk-update', name: 'app_item_bulk_update', methods: ['POST'])]
    public function bulkUpdate(Request $request, EntityManagerInterface $em): Response
    {
        $itemIds = $request->request->all('item_ids');
        $items = $em->getRepository(Item::class)->findBy(['id' => $itemIds]);

        foreach ($items as $item) {
            // Ensure every item updated belongs to an inventory the user is allowed to edit.
            $this->denyAccessUnlessGranted('INVENTORY_EDIT', $item->getInventory());
            $types = ['String', 'Int', 'Bool', 'Text'];
            foreach ($types as $type) {
                for ($i = 1; $i <= 3; $i++) {
                    $fieldName = "custom_" . strtolower($type) . $i;
                    $value = $request->request->get($fieldName);

                    if ($value !== null && $value !== '') {
                        $setter = "set" . $type . "Val" . $i;

                        if ($type === 'Bool') {
                            $item->$setter($value === '1');
                        } else {
                            $item->$setter($value);
                        }
                    }
                }
            }
        }

        $em->flush();
        $this->addFlash('success', count($items) . ' items updated.');

        return $this->redirectToRoute('app_inventory_show', ['id' => $items[0]->getInventory()->getId()]);
    }
}