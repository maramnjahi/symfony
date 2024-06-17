<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Categorie;
use App\Entity\Panier;
use App\Repository\CategorieRepository;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Repository\ProduitRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Json;

#[Route('/produit')]
class ProduitController extends AbstractController
{  


    ///////////////////// Client functions /////////////////////////
    #[Route('/Client/{id_client}/Category/{idC}', name: 'app_produit_index', methods: ['GET'])]
    public function indexProductswithcategory(EntityManagerInterface $entityManager,     Security $security, int $id_client, int $idC): Response
    {
          $user = $security->getUser();
        
        // Si l'ID du client dans l'URL ne correspond pas à l'ID de l'utilisateur actuellement authentifié, rediriger vers la page de connexion
        if ($id_client != $user->getId()) {
            // Redirection vers la page de connexion avec un message d'erreur
            $this->addFlash('error', 'Unauthorized access');
         
        }
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idC' => $idC]); // Fetch products with idC equal to $idC

        return $this->render('produit/ClientView/index.html.twig', [
            'produits' => $produits,
            'id_client' => $id_client,
            'idC' => $idC,
        ]);
    }

   
    #[Route('/Client/{id_client}/new/{idC}', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        CategorieRepository $categorieRepository,
        Security $security,
        int $id_client,
        int $idC
    ): Response {
        // Récupérer l'utilisateur actuellement authentifié
        $user = $security->getUser();
        
        // Si l'ID du client dans l'URL ne correspond pas à l'ID de l'utilisateur actuellement authentifié, rediriger vers la page de connexion
        if ($id_client != $user->getId()) {
            // Redirection vers la page de connexion avec un message d'erreur
            $this->addFlash('error', 'Unauthorized access');
         
        }
        
        $produit = new Produit();
    
        // Fetch the user object from the repository
        $client = $userRepository->find($id_client);
      
    
        // Fetch the Categorie object from the repository
        $categorie = $categorieRepository->find($idC);
        if (!$categorie) {
            throw $this->createNotFoundException('Categorie not found');
        }
    
        $produit->setIdClient($client);
        $produit->setIdC($categorie); 
    
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form['imageP']->getData();
            if ($pictureFile) {
                $filename = md5(uniqid()) . '.' . $pictureFile->guessExtension();
                $pictureFile->move(
                    $this->getParameter('pictures_directory'),
                    $filename
                );
                $produit->setImageP($filename); // Set the image property of the entity
            }
    
            $entityManager->persist($produit);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_produit_index', ['idC' => $idC, 'id_client' => $user->getId()], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('produit/ClientView/new.html.twig', [
            'form' => $form->createView(),
            'id_client' => $user->getId(),
            'idC' => $idC,
        ]);
    }

    
    #[Route('/Client/{id_client}/edit/{idP}/{idC}', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, int $id_client, int $idP, int $idC): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload manually
            $pictureFile = $form['imageP']->getData();
            if ($pictureFile) {
                // Generate a unique filename
                $filename = md5(uniqid()) . '.' . $pictureFile->guessExtension();
    
                // Move the uploaded file to the desired directory
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $filename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'An error occurred while uploading the file.');
                    return $this->redirectToRoute('app_produit_edit', ['id_client' => $id_client, 'idP' => $idP, 'idC' => $idC]);
                }
    
                // Update the entity with the new image filename
                $produit->setImageP($filename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('Client_Products_index', ['id_client' => $id_client], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
            'id_client' => $id_client,
            'idC' => $idC,
        ]);
    }
    
    

    #[Route('/Client/{id_client}/delete/{idP}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager , int $id_client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getIdP(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Client_Products_index', ['id_client' => $id_client], Response::HTTP_SEE_OTHER);
    }


 

#[Route('/Client/{id_client}/OwnProducts', name: 'Client_Products_index', methods: ['GET'])]
public function indexProductsClient(EntityManagerInterface $entityManager, Security $security, int $id_client): Response
{
    // Récupérer l'utilisateur actuellement authentifié
    $user = $security->getUser();
    
    // Si l'ID du client dans l'URL ne correspond pas à l'ID de l'utilisateur actuellement authentifié, rediriger vers la page de connexion
    if ($id_client != $user->getId()) {
        // Redirection vers la page de connexion avec un message d'erreur
        $this->addFlash('error', 'Unauthorized access');
        return $this->redirectToRoute('app_login');
    }
    
    // Récupérer les produits avec l'ID du client égal à $id_client
    $produits = $entityManager
        ->getRepository(Produit::class)
        ->findBy(['idClient' => $id_client]);
    
    // Affichage de la vue avec les données
    return $this->render('produit/ClientView/OwnProductsindex.html.twig', [
        'produits' => $produits,
        'id_client' => $id_client,
    ]);
}

    #[Route('/Client/{id_client}/shop', name: 'shop_Products_index', methods: ['GET'])]
    public function indexProductsShop(EntityManagerInterface $entityManager, int $id_client): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll(); // Fetch products with idC equal to $idC

        return $this->render('produit/ClientView/OwnProductsindex.html.twig', [
            'produits' => $produits,
            'id_client' => $id_client,
        ]);
    }

    ////////////////// Admin functions /////////////////

    #[Route('/admin/products/Category/{idC}', name: 'admin_produit_index', methods: ['GET'])]
    public function indexProductadmin(EntityManagerInterface $entityManager, int $idC): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idC' => $idC]); // Fetch products with idC equal to $idC

        return $this->render('produit/AdminView/index.html.twig', [
            'produits' => $produits,
            'idC' => $idC,
        ]);
    }


    #[Route('/Admin/Product/{idC}/delete/{idP}', name: 'admin_produit_delete', methods: ['POST'])]
    public function deleteAdmin(Request $request, Produit $produit, EntityManagerInterface $entityManager , int $idC): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getIdP(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_produit_index', ['idC' => $idC], Response::HTTP_SEE_OTHER);
    }


   
}


