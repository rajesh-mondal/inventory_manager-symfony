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
        // Latest 10 Inventories
        $latest = $repo->findBy([], ['id' => 'DESC'], 10);

        // Top 5 Popular (Based on item count)
        $popular = $repo->createQueryBuilder('i')
            ->leftJoin('i.items', 'item')
            ->select('i, COUNT(item) as HIDDEN itemCount')
            ->groupBy('i.id')
            ->orderBy('itemCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // 3. Tag Cloud (Get all unique tags)
        $allInventories = $repo->findAll();
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
