<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
use App\Repository\GrosmotsRepository;
use Symfony\Component\Security\Core\Security;
use App\Form\PublicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\CommentaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Grosmots;
use App\Entity\User;
use Symfony\Component\Form\FormError;

use Twilio\Rest\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/publication')]
class PublicationController extends AbstractController
{
    #[Route('/{idClient}', name: 'app_publication_index', methods: ['GET'])]
    public function index(Request $request, $idClient, PublicationRepository $publicationRepository, PaginatorInterface $paginator, Security $security): Response
    {
        // Récupérer l'utilisateur actuellement authentifié
        $user = $security->getUser();
        
        // Si l'ID du client dans l'URL ne correspond pas à l'ID de l'utilisateur actuellement authentifié, rediriger vers la page de connexion
        if ($idClient != $user->getId()) {
            // Redirection vers la page de connexion avec un message d'erreur
            $this->addFlash('error', 'Unauthorized access');
            return $this->redirectToRoute('app_login');
        }
    
        // Fetch all publications for the specified client from the repository
        $publications = $publicationRepository->findBy(['idClient' => $idClient]);
        
        // Paginate the results
        $pagination = $paginator->paginate(
            $publications, // The query to paginate
            $request->query->getInt('page', 1), // Get the page number from the request, default to 1
            3 // Number of items per page
        );
        
        return $this->render('publication/index.html.twig', [
            'pagination' => $pagination,
            'idClient' => $idClient, // Passer l'idClient au template Twig
        ]);
    }
    
    #[Route('/dislike', name: 'app_publication_dislike', methods: ['POST'])]
public function dislikePublication(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $requestData = json_decode($request->getContent(), true);
    $idPub = $requestData['idPub'];
    $idClient = $requestData['idClient'];

    // Recherche de la publication dans la base de données
    $publication = $entityManager->getRepository(Publication::class)->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);

    if (!$publication) {
        return new JsonResponse(['success' => false, 'message' => 'Publication non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Incrémenter le nombre de "Je n'aime pas"
    $publication->setNbDislike($publication->getNbDislike() + 1);

    // Mettre à jour la base de données
    $entityManager->flush();

    // Retourner la réponse JSON avec le nombre de "Je n'aime pas" mis à jour
    return new JsonResponse(['success' => true, 'dislikesCount' => $publication->getNbDislike()]);
}

   #[Route('/front_index/{idClient}', name: 'app_publication_indexfront', methods: ['GET', 'POST'])]
public function indexfront(
    Request $request,
    EntityManagerInterface $entityManager,
    CommentaireRepository $commentaireRepository,
    PaginatorInterface $paginator,
    $idClient // Ajout du paramètre $idClient
): Response {
    // Création d'une nouvelle instance de Publication
    $publication = new Publication();
    $publication->setNbLikes(0);
    $publication->setNbDislike(0);
    $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
    $publication->setDatePub($dateTime);

    // Création du formulaire de Publication
    $form = $this->createForm(PublicationType::class, $publication);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Gérer le téléchargement de la photo
        $photoFile = $form->get('photoFile')->getData();
        if ($photoFile) {
            $newFilename = $this->uploadPhoto($photoFile);
            $publication->setPhoto($newFilename);
        }

        // Associer la publication au client
        $client = $entityManager->getRepository(User::class)->find($idClient);
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé');
        }
        $publication->setIdClient($client);

        // Enregistrer la publication dans la base de données
        $entityManager->persist($publication);
        $entityManager->flush();

        // Rediriger vers la page actuelle pour éviter la soumission répétée du formulaire
        return $this->redirectToRoute('app_publication_indexfront', ['idClient' => $idClient]);
    }

    // Récupérer toutes les publications triées par date de la plus récente à la plus ancienne
    $query = $entityManager->getRepository(Publication::class)->createQueryBuilder('p')->orderBy('p.datePub', 'DESC');
    $publications = $paginator->paginate(
        $query->getQuery(), // Requête à paginer
        $request->query->getInt('page', 1), // Numéro de page par défaut
        5 // Nombre d'éléments par page
    );

    // Récupérer les commentaires pour chaque publication
    $comments = [];
    foreach ($publications as $pub) {
        $comments[$pub->getIdPub()] = $commentaireRepository->findBy(['idPub' => $pub]);
    }

    // Affichage de la vue avec les données
    return $this->render('publication/front.html.twig', [
        'publications' => $publications,
        'comments' => $comments,
        'form' => $form->createView(),
        'idClient' => $idClient // Passer l'idClient au template Twig
    ]);
}


    
    private function envoyerSms(): void
        {
            // Remplacer ces valeurs par vos identifiants Twilio
           
    
    
            // Initialisez le client Twilio
           
    
    
    
           
        }
        #[Route('/new/{idClient}', name: 'app_publication_new', methods: ['GET', 'POST'])]
        public function new(Request $request, Security $security,$idClient, EntityManagerInterface $entityManager): Response
        {
            // Récupérer l'utilisateur actuellement authentifié
            $user = $security->getUser();
         
         
            if ($idClient != $user->getId()) {
              
                
                $this->addFlash('error', 'Unauthorized access');
                return $this->redirectToRoute('app_publication_index', ['idClient' => $user->getId()]);
               
                
              
                echo "<script>alert($idClient);</script>";
            }
        
            $publication = new Publication();
            $publication->setNbLikes(0);
            $publication->setNbDislike(0);
            $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
            $publication->setDatePub($dateTime);
        
            // Associer la nouvelle publication à l'utilisateur actuellement authentifié
            $publication->setIdClient($user);
        
            $form = $this->createForm(PublicationType::class, $publication);
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
                // Gérer le téléchargement de la photo
                $photoFile = $form->get('photoFile')->getData();
                if ($photoFile) {
                    $newFilename = $this->uploadPhoto($photoFile);
                    $publication->setPhoto($newFilename);
                }
        
                $entityManager->persist($publication);
                $entityManager->flush();
        
                return $this->redirectToRoute('app_publication_index', ['idClient' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        
            return $this->render('publication/new.html.twig', [
                'publication' => $publication,
                'form' => $form->createView(), // Utilisez createView() pour obtenir l'objet FormView
                'idClient' => $user->getId(), // Passer l'idClient au template Twig
            ]);
        }        
        
        #[Route('/{idPub}/{idClient}', name: 'app_publication_show', methods: ['GET'])]
        public function show($idPub, $idClient, PublicationRepository $publicationRepository): Response
        {
            // Récupérer la publication spécifique pour le client spécifié
            $publication = $publicationRepository->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);
            if (!$publication) {
                throw $this->createNotFoundException('Publication non trouvée');
            }
        
            return $this->render('publication/show.html.twig', [
                'publication' => $publication,
                'idClient' => $idClient,
            ]);
        }
        #[Route('/like', name: 'app_publication_like', methods: ['POST'])]
        public function likePublication(Request $request, EntityManagerInterface $entityManager): JsonResponse
        {
            $requestData = json_decode($request->getContent(), true);
    
            $idPub = $requestData['idPub'];
            $idClient = $requestData['idClient'];
    
            // Recherche de la publication dans la base de données
            $publication = $entityManager->getRepository(Publication::class)->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);
    
            if (!$publication) {
                return new JsonResponse(['success' => false, 'message' => 'Publication non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
            }
    
            // Incrémenter le nombre de "J'aime"
            $publication->setNbLikes($publication->getNbLikes() + 1);
    
            // Mettre à jour la base de données
            $entityManager->flush();
    
            // Retourner la réponse JSON avec le nombre de "J'aime" mis à jour
            return new JsonResponse(['success' => true, 'likesCount' => $publication->getNbLikes()]);
        }
        #[Route('/{idPub}/{idClient}/comments', name: 'app_publication_comments', methods: ['GET'])]
public function showComments($idPub, $idClient, PublicationRepository $publicationRepository, CommentaireRepository $commentaireRepository): Response
{
    // Récupérer la publication
    $publication = $publicationRepository->find($idPub);

    if (!$publication) {
        throw $this->createNotFoundException('La publication n\'existe pas.');
    }

    // Récupérer les commentaires associés à cette publication
    $comments = $commentaireRepository->findBy(['idPub' => $idPub]);

    return $this->render('commentaire/index.html.twig', [
        'publication' => $publication,
        'commentaires' => $comments,
        'idClient' => $idClient,
    ]);
}


#[Route('/{idPub}/{idClient}/commentaires', name: 'app_publication_commentaire', methods: ['GET'])]
public function LesCommentaires($idPub, $idClient, CommentaireRepository $commentaireRepository): Response
{
    // Récupérer la publication spécifique pour le client spécifié
    $publication = $this->getDoctrine()->getRepository(Publication::class)->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);

    if (!$publication) {
        throw $this->createNotFoundException('La publication n\'existe pas.');
    }

    // Récupérer les commentaires associés à cette publication
    $comments = $commentaireRepository->findBy(['idPub' => $publication]);

    return $this->render('commentaire/Comments.html.twig', [
        'publication' => $publication,
        'commentaires' => $comments,
    ]);
}


    #[Route('/{idPub}/{idClient}/edit', name: 'app_publication_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, $idClient, Publication $publication, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PublicationType::class, $publication);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $photoFile = $form->get('photoFile')->getData();
        if ($photoFile) {
            $newFilename = $this->uploadPhoto($photoFile);
            $publication->setPhoto($newFilename);
        }

        $entityManager->flush();

        // Rediriger vers la liste des publications du client après l'édition de la publication
        return $this->redirectToRoute('app_publication_index', ['idClient' => $idClient], Response::HTTP_SEE_OTHER);
    }

    // Récupérer l'identifiant du client à partir de la publication si nécessaire
    // $idClient = $publication->getIdClient()->getId();

    return $this->renderForm('publication/edit.html.twig', [
        'publication' => $publication,
        'form' => $form,
        'idClient' => $idClient, // Passer l'idClient au template Twig
    ]);
}

#[Route('/{idPub}/{idClient}/editfront', name: 'app_publication_editfront', methods: ['GET', 'POST'])]
public function editfront(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PublicationType::class, $publication);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gérer le téléchargement de la nouvelle photo
        $photoFile = $form->get('photoFile')->getData();
        if ($photoFile) {
            $newFilename = $this->uploadPhoto($photoFile);
            $publication->setPhoto($newFilename);
        }

        // Persistez les modifications
        $entityManager->flush();

        // Rediriger vers la page d'index des publications frontales
        return $this->redirectToRoute('app_publication_indexfront', ['idClient' => $publication->getIdClient()]);
    }

    // Rendre la vue du formulaire d'édition avec les données nécessaires
    return $this->render('publication/editfront.html.twig', [
        'publication' => $publication,
        'form' => $form->createView(),
    ]);
}


#[Route('/{idPub}/{idClient}', name: 'app_publication_delete', methods: ['POST'])]
public function delete(Request $request, $idPub, $idClient, EntityManagerInterface $entityManager): Response
{
    $publication = $entityManager->getRepository(Publication::class)->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);

    if (!$publication) {
        throw $this->createNotFoundException('Publication non trouvée');
    }

    if ($this->isCsrfTokenValid('delete'.$idPub, $request->request->get('_token'))) {
        $entityManager->remove($publication);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_publication_index', ['idClient' => $idClient], Response::HTTP_SEE_OTHER);
}

#[Route('/{idPub}/{idClient}/supprimer', name: 'app_publication_supprimer', methods: ['POST'])]
public function supprimer(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('supprimer'.$publication->getIdPub(), $request->request->get('_token'))) {
        $entityManager->remove($publication);
        $entityManager->flush();
    }
    
    // Rediriger vers la page précédente
    return $this->redirectToRoute('app_publication_indexfront', ['idClient' => $publication->getIdClient()->getId()], Response::HTTP_SEE_OTHER);
}



    private function uploadPhoto($photoFile)
    {
        // Définir l'emplacement de stockage des photos
        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/assets';

        // Générer un nom de fichier unique
        $newFilename = uniqid().'.'.$photoFile->guessExtension();

        try {
            // Déplacer le fichier téléchargé vers l'emplacement de stockage
            $photoFile->move($uploadsDirectory, $newFilename);
        } catch (FileException $e) {
            // Gérer les erreurs de téléchargement de fichier
            throw new FileException('Erreur lors du téléchargement du fichier');
        }

        // Retourner le chemin complet du fichier téléchargé
        return '/assets/'.$newFilename;
    }
    
}
