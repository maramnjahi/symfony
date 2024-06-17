<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackUserController extends AbstractController
{
    #[Route('/back/user', name: 'app_back_user')]
    public function index(): Response
    {
        if($this->getUser()  && in_array('ADMIN', $this->getUser()->getRoles())){
            $users = $this->getDoctrine()->getRepository(User::class)->findAll();
           
            return $this->render('back_user/index.html.twig', [
                'users' => $users,
            ]);
        }else{
            return $this->redirectToRoute('app_login');
        }
    }
    #[Route(path: '/toggle-active/{id}', name: 'toggle_user_active')]
    public function toggleUserActive($id): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
 
        // Toggle isActive status
        $user->setStatus(!$user->isStatus());
        $entityManager->flush();

        return $this->redirectToRoute('app_back_user');
    }
    #[Route(path: '/search-users', name: 'search_users')]
    public function fetchUsers(Request $request):Response
    {
        // Handle search request
        $search = $request->request->get('searchValue');
        
        // Fetch users based on search query
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepository->searchUsers($search); 
      
        return $this->render('back_user/index.html.twig', [
            'users' => $users,
           
        ]);
        
       
    }
}
