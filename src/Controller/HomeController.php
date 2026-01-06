<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\InventoryRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(InventoryRepository $repo): Response
    {
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Latest 10 Inventories
        $latestQuery = $repo->createQueryBuilder('i')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(10);

        if (!$isAdmin) {
            $latestQuery->andWhere('i.is_public = :trueValue OR i.creator = :currentUser')
                ->setParameter('trueValue', true)
                ->setParameter('currentUser', $user);
        }
        $latest = $latestQuery->getQuery()->getResult();

        // Top 5 Popular Inventories
        $popularQB = $repo->createQueryBuilder('i')
            ->leftJoin('i.items', 'item')
            ->select('i, COUNT(item) as HIDDEN itemCount')
            ->groupBy('i.id')
            ->orderBy('itemCount', 'DESC')
            ->setMaxResults(5);

        if (!$isAdmin) {
            $popularQB->andWhere('i.is_public = :trueValue OR i.creator = :currentUser')
                ->setParameter('trueValue', true)
                ->setParameter('currentUser', $user);
        }
        $popular = $popularQB->getQuery()->getResult();

        // Tag Cloud
        $allInventoriesQB = $repo->createQueryBuilder('i');
        if (!$isAdmin) {
            $allInventoriesQB->andWhere('i.is_public = :trueValue OR i.creator = :currentUser')
                ->setParameter('trueValue', true)
                ->setParameter('currentUser', $user);
        }
        $allInventories = $allInventoriesQB->getQuery()->getResult();

        $tags = [];
        foreach ($allInventories as $inv) {
            foreach ($inv->getTags() as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }

        return $this->render('home/index.html.twig', [
            'latest' => $latest,
            'popular' => $popular,
            'tags' => $tags,
        ]);
    }
}
