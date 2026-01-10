<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Category;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ItemRepository;
use App\Repository\InventoryRepository;
use App\Repository\CategoryRepository;

class InventoryController extends AbstractController
{
    #[Route('/inventory/new', name: 'app_inventory_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            $inventory = new Inventory();

            $inventory->setTitle($request->request->get('title'));

            $categoryId = $request->request->get('category');
            if ($categoryId) {
                $category = $entityManager->getRepository(Category::class)->find($categoryId);
                $inventory->setCategory($category);
            }

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

        return $this->render('inventory/new.html.twig', [
            'categories' => $entityManager->getRepository(Category::class)->findAll(),
        ]);
    }

    #[Route('/my-inventories', name: 'app_my_inventories')]
    public function index(\App\Repository\InventoryRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        // Owned Inventories
        $queryOwned = $repo->createQueryBuilder('i')
            ->where('i.creator = :user')
            ->setParameter('user', $user)
            ->orderBy('i.id', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $queryOwned,
            $request->query->getInt('page', 1),
            10
        );

        // Shared Inventories
        $sharedInventories = $repo->createQueryBuilder('i')
            ->innerJoin('i.writeAccessUsers', 'u')
            ->where('u.id = :userId')
            ->andWhere('i.creator != :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('inventory/index.html.twig', [
            'pagination' => $pagination,
            'sharedInventories' => $sharedInventories,
        ]);
    }

    #[Route('/inventory/{id<\d+>}', name: 'app_inventory_show')]
    public function show(Inventory $inventory, Request $request, CategoryRepository $categoryRepository, PaginatorInterface $paginator, EntityManagerInterface $em): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Block Standard Users from viewing Private Inventories
        $this->denyAccessUnlessGranted('INVENTORY_VIEW', $inventory);

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

        $items = $inventory->getItems();
        $totalItems = count($items);
        $totalLikes = 0;
        $sumNumericField = 0;
        $numericFieldCount = 0;

        foreach ($items as $item) {
            $totalLikes += count($item->getLikes());

            if ($item->getIntVal1() !== null) {
                $sumNumericField += $item->getIntVal1();
                $numericFieldCount++;
            }
        }

        $avgNumericField = $numericFieldCount > 0 ? ($sumNumericField / $numericFieldCount) : 0;

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
            'pagination' => $pagination,
            'categories' => $categoryRepository->findAll(),
            'stats' => [
                'totalItems' => $totalItems,
                'totalLikes' => $totalLikes,
                'avgPrice' => $avgNumericField,
                'lastUpdated' => $totalItems > 0 ? $items->last()->getCreatedAt() : null,
            ],
        ]);
    }

    #[Route('/inventory/{id}/settings', name: 'app_inventory_settings', methods: ['POST'])]
    public function editSettings(Inventory $inventory, Request $request, EntityManagerInterface $em): Response
    {
        // Base security: Must have write access
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        $currentUser = $this->getUser();
        $isCreatorOrAdmin = ($inventory->getCreator() === $currentUser || $this->isGranted('ROLE_ADMIN'));
        $activeTab = 'settings-pane';

        if ($request->request->has('grant_access_email') || $request->request->has('revoke_access_id')) {
            if (!$isCreatorOrAdmin) {
                $this->addFlash('danger', 'Only the owner or admin can manage permissions.');
            } else {
                $this->handleAccessManagement($inventory, $request, $em);
                $activeTab = 'access-pane';
            }
        }

        else {
            $categoryId = $request->request->get('category');
            $category = $categoryId ? $em->getRepository(Category::class)->find($categoryId) : null;
            $inventory->setCategory($category);

            $inventory->setDescription($request->request->get('description'));

            if ($isCreatorOrAdmin) {
                $inventory->setIsPublic($request->request->get('is_public') === '1');
            }

            $tagsString = $request->request->get('tags', '');
            $tagsArray = array_filter(array_map('trim', explode(',', $tagsString)));
            $inventory->setTags($tagsArray);

            $this->addFlash('success', 'General settings updated.');
        }

        $em->flush();

        return $this->redirectToRoute('app_inventory_show', [
            'id' => $inventory->getId(),
            '_fragment' => $activeTab
        ]);
    }

    private function handleAccessManagement(Inventory $inventory, Request $request, EntityManagerInterface $em): void
    {
        // Grant access logic
        if ($email = $request->request->get('grant_access_email')) {
            $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => $email]);
            if ($user) {
                if ($user === $inventory->getCreator()) {
                    $this->addFlash('warning', 'This user is already the owner.');
                } else {
                    $inventory->addWriteAccessUser($user);
                    $this->addFlash('success', "Access granted to $email");
                }
            } else {
                $this->addFlash('danger', 'User with that email not found.');
            }
        }

        // Revoke access logic
        if ($revokeId = $request->request->get('revoke_access_id')) {
            $user = $em->getRepository(\App\Entity\User::class)->find($revokeId);
            if ($user) {
                $inventory->removeWriteAccessUser($user);
                $this->addFlash('success', 'Access revoked successfully.');
            }
        }
    }

    #[Route('/inventory/bulk-edit', name: 'app_inventory_bulk_edit', methods: ['GET', 'POST'])]
    public function bulkEdit(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        if (!$ids) return $this->redirectToRoute('app_inventory_index');

        $inventories = $em->getRepository(Inventory::class)->findBy(['id' => $ids]);

        // Verify every selected inventory is editable by current user
        foreach ($inventories as $inv) {
            $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inv);
        }

        $isSingle = (count($inventories) === 1);

        return $this->render('inventory/bulk_edit.html.twig', [
            'inventories' => $inventories,
            'isSingle' => $isSingle,
            'inventoryData' => $isSingle ? $inventories[0] : null,
            'categories' => $em->getRepository(Category::class)->findAll(),
        ]);
    }

    #[Route('/inventory/bulk-update-save', name: 'app_inventory_bulk_update_save', methods: ['POST'])]
    public function bulkUpdateSave(Request $request, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        $categoryId = $request->request->get('category');
        $tagsString = $request->request->get('tags');
        $isPublic = $request->request->get('is_public');
        $description = $request->request->get('description');

        if ($ids) {
            $inventories = $em->getRepository(Inventory::class)->findBy(['id' => $ids]);

            $categoryObject = null;
            if (!empty($categoryId)) {
                $categoryObject = $em->getRepository(Category::class)->find($categoryId);
            }

            foreach ($inventories as $inv) {
                // Security check before saving data
                $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inv);

                if ($categoryObject) {
                    $inv->setCategory($categoryObject);
                }

                // if (!empty($category)) $inv->setCategory($category);
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
                // Standard Users cannot delete (even if public)
                $this->denyAccessUnlessGranted('INVENTORY_DELETE', $inv);
                $em->remove($inv);
            }
            $em->flush();
            $this->addFlash('success', count($ids) . ' inventories deleted.');
        }

        return $this->redirectToRoute('app_my_inventories');
    }
}
