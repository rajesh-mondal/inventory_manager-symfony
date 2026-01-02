<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ItemRepository;

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
    public function index(\App\Repository\InventoryRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $query = $repo->createQueryBuilder('i')
            ->where('i.creator = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('i.id', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('inventory/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/inventory/{id<\d+>}', name: 'app_inventory_show')]
    public function show(Inventory $inventory, Request $request, PaginatorInterface $paginator, EntityManagerInterface $em): Response
    {
        $queryBuilder = $em->getRepository(Item::class)
        ->createQueryBuilder('i')
        ->where('i.inventory = :inv')
        ->setParameter('inv', $inventory)
        ->orderBy('i.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/inventory/bulk-edit', name: 'app_inventory_bulk_edit', methods: ['POST'])]
    public function bulkEdit(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        if (!$ids) return $this->redirectToRoute('app_inventory_index');

        $inventories = $em->getRepository(Inventory::class)->findBy(['id' => $ids]);
        $isSingle = (count($inventories) === 1);

        return $this->render('inventory/bulk_edit.html.twig', [
            'inventories' => $inventories,
            'isSingle' => $isSingle,
            'inventoryData' => $isSingle ? $inventories[0] : null,
        ]);
    }

    #[Route('/inventory/bulk-update-save', name: 'app_inventory_bulk_update_save', methods: ['POST'])]
    public function bulkUpdateSave(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        $category = $request->request->get('category');
        $tagsString = $request->request->get('tags');
        $isPublic = $request->request->get('is_public');
        $description = $request->request->get('description');

        if ($ids) {
            $inventories = $em->getRepository(Inventory::class)->findBy(['id' => $ids]);

            foreach ($inventories as $inv) {
                if (!empty($category)) $inv->setCategory($category);
                if (!empty($description)) $inv->setDescription($description);

                if (!empty($tagsString)) {
                    $tagsArray = array_map('trim', explode(',', $tagsString));
                    $inv->setTags($tagsArray);
                }

                if ($isPublic !== "" && $isPublic !== null) {
                    $inv->setIsPublic((bool)$isPublic);
                }
            }

            $em->flush();
            $this->addFlash('success', count($ids) . ' inventories updated successfully.');
        }

        return $this->redirectToRoute('app_my_inventories');
    }

    #[Route('/inventory/bulk-delete', name: 'app_inventory_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');

        if ($ids) {
            $inventories = $em->getRepository(Inventory::class)->findBy(['id' => $ids]);
            foreach ($inventories as $inv) {
                $em->remove($inv);
            }
            $em->flush();
            $this->addFlash('success', count($ids) . ' inventories deleted.');
        }

        return $this->redirectToRoute('app_my_inventories');
    }
}
