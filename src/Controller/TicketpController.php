<?php

namespace App\Controller;

use App\Entity\Ticketp;
use App\Form\TicketpType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Enchere;
use App\Repository\UserRepository;

#[Route('/ticketp')]
class TicketpController extends AbstractController
{
    #[Route('/', name: 'app_ticketp_index', methods: ['GET'])]
    
public function index(EntityManagerInterface $entityManager, UserRepository $userRepository): Response
{
    $ticketps = $entityManager
        ->getRepository(Ticketp::class)
        ->findAll();

    // Fetch additional data for each Ticketp
    foreach ($ticketps as $ticketp) {
        $email = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneByEmail($email);
      
        // Fetch the user associated with the Ticketp
        $user = $user->getid();
        $enchere =new enchere;
        $enchere =$enchere->getEnchereId();

        // Fetch the name of the enchere
        $enchere = $entityManager
        ->getRepository(Enchere::class)
        ->find($enchere);
        
        // Add the fetched data to the Ticketp entity
        $ticketp->userName = $user ? $user->getName() : null;
        $ticketp->userPrenom = $user ? $user->getPrenom() : null;
        $ticketp->enchereName = $enchere ? $enchere->getName() : null;
    }

    return $this->render('admin/tickets/tables.html.twig', [
        'ticketps' => $ticketps,
    ]);
}
}