<?php

namespace App\Controller;

use App\Entity\Detailscommande;
use App\Entity\Commande;
use App\Form\DetailscommandeType;
use App\Repository\DetailscommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/detailscommande')]
class DetailscommandeController extends AbstractController
{
    #[Route('/', name: 'app_detailscommande_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $detailscommandes = $entityManager
            ->getRepository(Detailscommande::class)
            ->findAll();

        return $this->render('detailscommande/index.html.twig', [
            'detailscommandes' => $detailscommandes,
        ]);
    }

    #[Route('/new', name: 'app_detailscommande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $detailscommande = new Detailscommande();
        $form = $this->createForm(DetailscommandeType::class, $detailscommande);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $detailscommande->setIdCom($detailscommande->getCommande()->getId());
            $entityManager->persist($detailscommande);
            $entityManager->flush();

            return $this->redirectToRoute('app_detailscommande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('detailscommande/new.html.twig', [
            'detailscommande' => $detailscommande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_detailscommande_show', methods: ['GET'])]
    public function show(Detailscommande $detailscommande): Response
    {
        return $this->render('detailscommande/show.html.twig', [
            'detailscommande' => $detailscommande,
        ]);
    }
    #[Route('/detail_commande/{id}', name: 'app_detailscommande_show_by_commande', methods: ['GET'])]
    public function showByCommande($id,EntityManagerInterface $em): Response
    {
        
        $commande= $em->getRepository(Commande::class)->find($id);
        
        
        $detailscommande=$em->getRepository(Detailscommande::class)->findBy(["commande"=>$commande]);
        $commande=$detailscommande[0];
       
        $pdfContent=$commande->getNomArticle();
        
        
 
         return $$pdfContent;
        
        /*return $this->render('detailscommande/show.html.twig', [
            'detailscommande' => $detailscommande[0],
        ]);*/
    }

    #[Route('/{id}/edit', name: 'app_detailscommande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Detailscommande $detailscommande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetailscommandeType::class, $detailscommande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($detailscommande);
            $entityManager->flush();

            return $this->redirectToRoute('app_detailscommande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('detailscommande/edit.html.twig', [
            'detailscommande' => $detailscommande,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_detailscommande_delete')]
    public function delete($id,DetailscommandeRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $detailscommande=$repo->find($id);
        $entityManager->remove($detailscommande);
        $entityManager->flush();
        

        return $this->redirectToRoute('app_detailscommande_index');
    }
}
