<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/bulk-action', name: 'app_admin_users_bulk', methods: ['POST'])]
    public function bulkAction(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('ids');
        $action = $request->request->get('action');
        $users = $userRepository->findBy(['id' => $ids]);

        foreach ($users as $user) {
            if ($user === $this->getUser() && $action === 'delete') continue;

            switch ($action) {
                case 'block': $user->setRoles(['ROLE_BLOCKED']); break;
                case 'unblock': $user->setRoles(['ROLE_USER']); break;
                case 'make_admin':
                    $roles = $user->getRoles();
                    if (!in_array('ROLE_ADMIN', $roles)) $roles[] = 'ROLE_ADMIN';
                    $user->setRoles($roles);
                    break;
                case 'remove_admin':
                    $user->setRoles(['ROLE_USER']);
                    break;
                case 'delete': $em->remove($user); break;
            }
        }

        $em->flush();
        $this->addFlash('success', 'User actions processed.');
        return $this->redirectToRoute('app_admin_users');
    }
}
