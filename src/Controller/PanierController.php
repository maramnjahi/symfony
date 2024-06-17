<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detailscommande;
use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Repository\PanierProduitRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use TCPDF;

#[Route('/panier')]
class PanierController extends AbstractController
{
   
   

    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository, Security $security, UserRepository $userRepository, ProduitRepository $produitRepository, PanierProduitRepository $panierProduitRepository): Response
    {
        $userE = $security->getUser();
    
        if (!$userE) {
            // Gérer le cas où l'utilisateur n'est pas authentifié
            // Rediriger vers la connexion ou le gérer de manière appropriée
        }
    
        $user = $userRepository->findOneBy(['email' => $userE->getEmail()]);
    
        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            // Rediriger, afficher une erreur ou le gérer de manière appropriée
        }
    
        $panier = $panierRepository->findByUser($userE->getId());
    
        if (!$panier) {
            // Si le panier est null, définir un tableau vide de produits
            $produits = [];
        } else {
            // Si le panier n'est pas null, obtenir les produits du panier
            $produits = $panierProduitRepository->findBy(['panier' => $panier]);
        }
    
        $products = [];
        foreach ($produits as $product) {
            $product = $produitRepository->find($product->getProduitId());
    
            if ($product) {
                $products[] = $product;
            } else {
                // Gérer le cas où le produit n'est pas trouvé
                // Peut-être journaliser une erreur ou ignorer ce produit
            }
        }
    
        return $this->render('panier/index.html.twig', [
            'produits' => $products,
            'totalPrix' => $panier ? $panier->getPrix() : 0, // Si le panier est null, afficher 0 comme prix total
        ]);
    }
    

    #[Route('/new/{idP}/', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function add(
        $idP, 
        UserRepository $userRepository, 
        Security $security, 
        ProduitRepository $produitRepository, 
        PanierRepository $panierRepository, 
        PanierProduitRepository $panierProduitRepository, 
        EntityManagerInterface $entityManager
    ): RedirectResponse { 
        $userE = $security->getUser();
        $panier = $panierRepository->findByUser($userE->getId());
        $produit = $produitRepository->find($idP);
        
        // Si le panier n'existe pas pour cet utilisateur, créez-en un nouveau
        if ($panier == null) {
            $panier = new Panier();
            $panier->setUser($userE); 
            $panier->setPrix(0.0);
             $panier->setNomArticle($produit->getNomP());
            // Persistez le nouveau panier
            $entityManager->persist($panier);
        }
        
        // Ajoutez le prix du produit au prix du panier
        $panier->setPrix($panier->getPrix() + $produit->getPrixP());
        
        // Créez un nouveau PanierProduit et associez-le au panier et au produit
        $panierProduits = new PanierProduit();
        $panierProduits->setPanier($panier);
        $panierProduits->setProduitId($produit->getIdP());
        
        // Persistez le PanierProduit
        $entityManager->persist($panierProduits);
        
        // Enregistrez les changements
        $entityManager->flush();
        
        return $this->redirectToRoute('app_panier_index');
    }
    

    #[Route('/delete/{id}', name: 'app_panier_delete')]
    public function delete($id, Security $security,UserRepository $userRepository, ProduitRepository $produitRepository, PanierRepository $panierRepository, PanierProduitRepository $panierProduitRepository, EntityManagerInterface $entityManager): Response
    {
        $userE = $security->getUser();

        
        
        $panier=$panierRepository->findByUser($userE->getId());
       
        $produit=$produitRepository->find($id);
        $produitPanier=$panierProduitRepository->findOneBy(['produitId'=>$id, 'panier'=>$panier]);
        $panier->setPrix($panier->getPrix()-$produit->getPrixP());
        $entityManager->persist($panier);
        $entityManager->remove($produitPanier);
        $entityManager->flush();
      
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/passerCommande', name: 'app_panier_passer_command')]
    public function deleteAll(MailerInterface $mailer, ProduitRepository $produitRepository, PanierProduitRepository $panierProduitRepository, PanierRepository $panierRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $userE=$this->getUser()->getUserIdentifier();
        $user=$userRepository->findOneBy(['email'=>$userE]);
        $panier=$panierRepository->findByUser($user->getId());
        $paniers=$panierProduitRepository->findBy(['panier'=>$panier]);
        $products=[];
        $prixTotal = $panier->getPrix();
        foreach ($paniers as $product) {
            $product=$produitRepository->find($product->getProduitId());
            
            $products[]=$product;   
        }
        //ajouter commamde
        $dateSysteme = new \DateTime();
        $commande= new Commande();
        $commande->setUser($user);
        $commande->setEtat("en cours");
        $commande->setPanier($panier);
        $commande->setCmdClient(0);
        $entityManager->persist($commande);
       
       
      /////////////////////////////////////////////////////////////////////
        
        
        $entityManager->flush();

        ///////////////////////////////////////////////////////////////
        $detailsCommande= new Detailscommande();
        $detailsCommande->setCommande($commande);
        $detailsCommande->setIdCom($commande);
        $detailsCommande->setNomArticle("");
       // dd( $detailsCommande->getNomArticle());
    
        $detailsCommande->setQuantite(count($products));
        $detailsCommande->setSousTotal($panier->getPrix());
        $detailsCommande->setNumArticle(0);
        $detailsCommande->setPrixUnitaire(0);
        $entityManager->persist($detailsCommande);




        foreach ($paniers as $produit) {
            $entityManager->remove($produit);
        }
        $panier->setPrix(0);
        $entityManager->persist($panier);
        $entityManager->flush();
        $email = (new Email())
        ->from('karat6657@gmail.com')
        ->to($user->getEmail())
        //->cc('Affariety')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject('Confirmation commande')
        ->text('Cher Client(e)
            Votre commande a ete validee avec succes ')
        ->html( $this->renderView('panier/pdf_Facture.html.twig', [
            'products' => $products,'totalPrix' => $prixTotal,'user'=>$user,'dateSysteme' => $dateSysteme,  
        ]));

        $mailer->send($email);

        return $this->redirectToRoute('app_panier_index');
    }
    
    #[Route('/exportProduct/pdf', name: 'export_product_to_pdf', methods: ['GET'])]
    public function exportProductsToPdf(PanierProduitRepository $panierProduitRepository, UserRepository $userRepository, PanierRepository $panierRepository, ProduitRepository $produitRepository): Response
    {
         // Retrieve products from the cart, assuming you have a method to get them from your repository
         $userE = $this->getUser()->getUserIdentifier();
         $user = $userRepository->findOneBy(['email' => $userE]);
         $panier = $panierRepository->findByUser($user->getId());
         $produits = $panierProduitRepository->findBy(['panier' => $panier]);
         
         $products = [];
         foreach ($produits as $product) {
             $product = $produitRepository->find($product->getProduitId());
             $products[] = $product;
         }
         
         $dateSysteme = new \DateTime();
 
         // Create a new TCPDF instance
         $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
 
         // Set document information
         $pdf->SetCreator('Your Name');
         $pdf->SetAuthor('Your Name');
         $pdf->SetTitle('Cart Products');
         $pdf->SetSubject('Cart Products PDF');
         $pdf->SetKeywords('TCPDF, PDF, example, sample');
         $pdf->setImageScale(1.53);
         
         $imagePath = $this->getParameter('kernel.project_dir') . '/public/images/logo.jpg'; // Update the image path accordingly
         $pdf->Image($imagePath, 10, 10, 50, 0, 'JPG', '', '', true, 300, '', false, false, 0, false, false, false);
 
         // Add a page
         $pdf->AddPage();
 
         // Set font
         $pdf->SetFont('helvetica', '', 12);
 
         // Write content to PDF
         $html = $this->renderView('panier/pdf_Facture.html.twig', [
             'products' => $products,
             'totalPrix' => $panier->getPrix(),
             'user' => $user,
             'dateSysteme' => $dateSysteme,
         ]);
         $pdf->writeHTML($html);
 
         // Output PDF as response
         $pdfContent = $pdf->Output('cart_products.pdf', 'S');
         $response = new Response($pdfContent);
         $response->headers->set('Content-Type', 'application/pdf');
         $response->headers->set('Content-Disposition', 'inline; filename="cart_products.pdf"');
 
         return $response;
        // Retrieve products from the cart, assuming you have a method to get them from your repository
        /*$userE=$this->getUser()->getUserIdentifier();
        $user=$userRepository->findOneBy(['email'=>$userE]);
        $panier=$panierRepository->findByUser($user->getId());
        $produits=$panierProduitRepository->findBy(['panier'=>$panier ]);
        
        $products=[];
        foreach ($produits as $product) {
            $product=$produitRepository->find($product->getProduitId());
            
            $products[]=$product;   
        }
        $dateSysteme = new DateTime();
        // Generate HTML content for the PDF using Twig templates or plain PHP
        $html = $this->renderView('panier/pdf_Facture.html.twig', [
            'products' => $products,'totalPrix'=>$panier->getPrix(), 'user'=>$user, 'dateSysteme' => $dateSysteme,
        ]);
    
        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        
        // Instantiate Dompdf
        
        $options->set('debugLogOutputFile', '/path/to/dompdf/log.txt');
        $options->set('isPhpEnabled', true); // Enable PHP execution for debugging
        $options->set('debugKeepTemp', true); // Keep temporary files for debugging
        $dompdf = new Dompdf($options);
        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);
    
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the PDF
        $dompdf->render();
    
        // Output the generated PDF to the browser
        $pdfOutput = $dompdf->output();
        $response = new Response($pdfOutput);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="cart_products.pdf"');
    
        return $response;*/
    }
    
    #[Route('/test/pdf', name: 'app_test_pdf')]
    public function test( ProduitRepository $produitRepository, PanierProduitRepository $panierProduitRepository, PanierRepository $panierRepository, UserRepository $userRepository): Response
    {
        $userE=$this->getUser()->getUserIdentifier();
        $user=$userRepository->findOneBy(['email'=>$userE]);
        $panier=$panierRepository->findByUser($user->getId());
        $produits=$panierProduitRepository->findBy(['panier'=>$panier ]);
        
        $products=[];
        foreach ($produits as $product) {
            $product=$produitRepository->find($product->getProduitId());
            
            $products[]=$product;   
        }
        $dateSysteme = new DateTime();
        // Generate HTML content for the PDF using Twig templates or plain PHP
        return $this->render('panier/pdf_Facture.html.twig', [
            'products' => $products,'totalPrix'=>$panier->getPrix(), 'user'=>$user, 'dateSysteme' => $dateSysteme,
        ]);
    }


}
