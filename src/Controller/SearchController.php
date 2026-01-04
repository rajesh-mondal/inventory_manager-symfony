<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_inventory_search')]
    public function search(InventoryRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $tag = $request->query->get('tag');

        $qb = $repo->createQueryBuilder('i')
            ->orderBy('i.id', 'DESC');

        if ($tag) {
            $qb->andWhere('i.tags LIKE :tag')
               ->setParameter('tag', '%"' . $tag . '"%');
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('search/results.html.twig', [
            'pagination' => $pagination,
            'currentTag' => $tag
        ]);
    }
}