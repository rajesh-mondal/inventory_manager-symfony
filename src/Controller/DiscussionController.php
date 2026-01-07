<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Inventory;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory/{id}/comments', name: 'app_discussion_')]
final class DiscussionController extends AbstractController
{
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Inventory $inventory, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Login required'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? null;

        if (!$content) {
            return new JsonResponse(['error' => 'Message cannot be empty'], 400);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($user);
        $comment->setInventory($inventory);

        $em->persist($comment);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'comment' => [
                'id' => $comment->getId(),
                'author' => $user->getUserIdentifier(),
                'content' => $comment->getContent(),
                'date' => $comment->getCreatedAt()->format('H:i')
            ]
        ]);
    }

    #[Route('/updates', name: 'updates', methods: ['GET'])]
    public function getUpdates(Inventory $inventory, Request $request, CommentRepository $repo): JsonResponse
    {
        $lastId = $request->query->getInt('lastId', 0);
        $newComments = $repo->findNewComments($inventory, $lastId);

        $data = [];
        foreach ($newComments as $c) {
            $data[] = [
                'id' => $c->getId(),
                'author' => $c->getAuthor()->getUserIdentifier(),
                'content' => $c->getContent(),
                'date' => $c->getCreatedAt()->format('d M Y, H:i')
            ];
        }
        return new JsonResponse($data);
    }
}
