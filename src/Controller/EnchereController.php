<?php

namespace App\Controller;

use App\Entity\Enchere;
use App\Form\EnchereType;
use App\Entity\TicketEnchere;
use App\Entity\Ticketp;
use App\Repository\UserRepository;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Repository\EnchereRepository;
use Symfony\UX\Chartjs\Model\Chart;

use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/enchere')]
class EnchereController extends AbstractController
{
    #[Route('/', name: 'app_enchere_index', methods: ['GET'])]
    public function index(Request $request, EnchereRepository $enchereRepository): Response
        {
    if($this->getUser()){

        $minAmount = $request->query->get('minAmount');
        $search = $request->query->get('search');
    
        if ($minAmount !== null) {
            $encheres = $enchereRepository->findByMinAmount($minAmount);
        } elseif ($search !== null) {
            $encheres = $enchereRepository->searchByName($search);
        } else {
            $encheres = $enchereRepository->findAll();
        }
            return $this->render('enchere/index.html.twig', [
            'encheres' => $encheres,
        ]);
    }
    else{
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/new', name: 'app_enchere_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
    if($this->getUser()){

        $email = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneByEmail($email);
        $id = $user->getId();
        $enchere = new Enchere();
        $form = $this->createForm(EnchereType::class, $enchere);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $enchere->setIdclcreree($id);
            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('public_images_auctions'), // Specify the directory where images should be uploaded
                        $safeFilename
                    );
                } catch (FileException $e) {
                    // Handle the file exception, if necessary
                $error = $e->getMessage();
                }
    
                $enchere->setImage($safeFilename);
            }
    
            // Persist the enchere first
            $entityManager->persist($enchere);
            $entityManager->flush();
            //get enchereid in varaible int
            $enchereId = $enchere->getEnchereId();
            // Create 10 tickets for the enchere
            
                $ticketEnchere = new TicketEnchere();
                $ticketEnchere->setEnchereId($enchereId);
                $ticketEnchere->setPrix(10); // Prices range from 1 to 10
                $entityManager->persist($ticketEnchere);
            
            $entityManager->flush();
    
            return $this->redirectToRoute('app_enchere_index');
        }
    
        return $this->renderForm('enchere/new.html.twig', [
            'enchere' => $enchere,
            'form' => $form,
        ]);
    }
    else{
        return $this->redirectToRoute('app_login');
    }
    }

    #[Route('/{enchereId}', name: 'app_enchere_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Enchere $enchere, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if($this->getUser()){
            $email = $this->getUser()->getUserIdentifier();
            $user = $userRepository->findOneByEmail($email);
            $id = $user->getId();
        // If the "Join to Bid" button is clicked, add a ticket
        if ($request->isMethod('POST') && $request->request->has('join_bid')) {
            // Assuming you have access to the current user ID, replace 1 with the actual user ID
            
            // Create a new Ticketp entity
            $enchereId = $enchere->getEnchereId();
//chercher  ticket id  with enchereid
$ticketEnchereRepository = $entityManager->getRepository(TicketEnchere::class);
$ticketEnchere = $ticketEnchereRepository->findOneBy(['enchereId' => $enchereId]);
$ticketId = $ticketEnchere->getTicketId();

            $ticketp = new Ticketp();

            $ticketp ->setTicketId($ticketId);
            $ticketp->setEnchereId($enchereId);
            $ticketp->setClientId($id);
            
            // Save the Ticketp entity to the database
            $entityManager->persist($ticketp);
            $entityManager->flush();
            
            // Redirect back to the show page after adding the ticket
            return $this->redirectToRoute('app_enchere_show', ['enchereId' => $enchere->getEnchereId()]);
        }

        // Render the show page
        return $this->render('enchere/show.html.twig', [
            'enchere' => $enchere,
        ]);}
        else{
            return $this->redirectToRoute('app_login');
        }
    }
    #[Route('/{enchereId}/edit', name: 'app_enchere_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enchere $enchere, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if($this->getUser()){
            $email = $this->getUser()->getUserIdentifier();
            $user = $userRepository->findOneByEmail($email);
            $iduser = $user->getId();
            $encherecId = $enchere->getIdclcreree();

        if($iduser==$encherecId){            
        $form = $this->createForm(EnchereType::class, $enchere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_enchere_index', [], Response::HTTP_SEE_OTHER);
        }
    }

        return $this->renderForm('enchere/edit.html.twig', [
            'enchere' => $enchere,
            'form' => $form,
        ]);
    }
        else{
            return $this->redirectToRoute('app_login');
     
    }
    }
    #[Route('/{enchereId}', name: 'app_enchere_delete', methods: ['POST'])]
    public function delete(Request $request, Enchere $enchere, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if($this->getUser()){
            $email = $this->getUser()->getUserIdentifier();
            $user = $userRepository->findOneByEmail($email);
            $id = $user->getId();
            $encherecId = $enchere->getIdclcreree();
            if($id==$encherecId){
            //find id user in table ticketp
             $ticketpRepository = $entityManager->getRepository(Ticketp::class);
             $ticketp = $ticketpRepository->findOneBy(['client_id' => $id]);
            if($ticketp){
                  $this->addFlash('error', 'you cant dellet this auction there are user participe now');

                     }}
        else{ //delete ticketenchere
            $ticketEnchereRepository = $entityManager->getRepository(TicketEnchere::class);
            $ticketenchere = $ticketEnchereRepository->findOneBy(['enchere_id' => $enchere->getEnchereId()]);
            $entityManager->remove($ticketenchere);
            $entityManager->remove($enchere);
            $entityManager->flush();
        
            if ($this->isCsrfTokenValid('delete'.$enchere->getEnchereId(), $request->request->get('_token'))) {
            $entityManager->remove($enchere);
            $entityManager->flush();
            return $this->redirectToRoute('app_enchere_index', [], Response::HTTP_SEE_OTHER);
        }
        }
    
     }
    else{
        return $this->redirectToRoute('app_login');
    }
} 
#[Route('/{enchereId}/bid', name: 'app_enchere_bid', methods: ['GET', 'POST'])]
public function joinBid(Request $request, EntityManagerInterface $entityManager, Enchere $enchere,UserRepository $userRepository): Response
{
    if($this->getUser()){
        $currentDateTime = new \DateTime();
        $enchereEndDateTime = \DateTime::createFromFormat('Y-m-d H:i', $enchere->getDateFin().' '.$enchere->getHeuref());
       
         if ($currentDateTime >= $enchereEndDateTime) {

        $email = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneByEmail($email);
        $id = $user->getId();
        $encherecId = $enchere->getIdclcreree();
        
        if ($request->getMethod() === 'POST') {
            
                // Get the final bid amount from the form
                $finalBid = $request->request->get('finalBid');
                $finaBid = $enchere->getMontantFinal();

                if($finaBid == null){
                    $initBid = $enchere->getMontantInitial();
                    $prevFinalBid =$finalBid+$initBid;
                    $email = $this->getUser()->getUserIdentifier();
                    $user = $userRepository->findOneByEmail($email);
                    $id = $user->getId();
            
                    // Validate if final bid is greater than initial bid and previous final bid test 
                    if ($prevFinalBid < $initBid ) {
                        $this->addFlash('error', 'The final bid amount must be greater than both the initial bid amount and the previous final bid amount.');
                        return $this->redirectToRoute('app_enchere_bid', ['enchereId' => $enchere->getEnchereId()]);
                    }
            
                    // Update the final bid amount in the Enchere entity
                    $enchere->setMontantFinal($prevFinalBid);
                    $enchere->setIdclenchere($id);
            
                    // Persist the changes to the database
                    $entityManager->flush();
                
                } else {
                    // finaBid +  finalBid
                    $prevFinalBid =$finalBid+$finaBid;
                    $email = $this->getUser()->getUserIdentifier();
                    $user = $userRepository->findOneByEmail($email);
                    $id = $user->getId();
                    $enchere->setMontantFinal($prevFinalBid);
                    $enchere->setIdclenchere($id);
            
                    // Persist the changes to the database
                    $entityManager->flush();
                
                }
            } else {
                return $this->redirectToRoute('app_enchere_index');
            }
        }
    
        return $this->render('enchere/bid.html.twig', [
            'enchere' => $enchere,
        ]);
    } else {
        return $this->redirectToRoute('app_login');
    }
}

}