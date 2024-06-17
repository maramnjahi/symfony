<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Publication;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();
    
        // Exemple de récupération de l'identifiant du client à partir du premier commentaire
        $idClient = null;
        if (!empty($commentaires)) {
            $idClient = $commentaires[0]->getIdClient()->getId();
        }
    
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaires,
            'idClient' => $idClient, // Passer l'identifiant du client au modèle Twig si nécessaire
        ]);
    }
    

   #[Route('/{idPub}/{idClient}/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, int $idPub, int $idClient): Response
{
    // Récupérer la publication associée à l'identifiant $idPub
    $publication = $entityManager->getRepository(Publication::class)->find($idPub);

    // Vérifier si la publication existe
    if (!$publication) {
        throw $this->createNotFoundException('Publication non trouvée.');
    }

    // Créer une nouvelle instance de Commentaire
    $commentaire = new Commentaire();

    // Initialiser la date du commentaire
    $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
    $commentaire->setDateCom($dateTime);

    // Créer le formulaire de création de commentaire avec l'identifiant de la publication
    $form = $this->createForm(CommentaireType::class, $commentaire);

    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Associer le commentaire à la publication et au client
        $commentaire->setIdPub($publication);
        $client = $entityManager->getRepository(User::class)->find($idClient);
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé');
        }
        $commentaire->setIdClient($client);

        // Enregistrer le commentaire dans la base de données
        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Rediriger vers une page appropriée après la création du commentaire
        return $this->redirectToRoute('app_publication_comments', [    'idPub' => $idPub,
        'idClient' => $idClient
    ]);
    }

    // Rendre le formulaire dans le template Twig
    return $this->renderForm('commentaire/new.html.twig', [
        'publication' => $publication,
        'commentaire' => $commentaire,
        'form' => $form,
        'idClient' => $idClient,
    ]);
}

    #[Route('/{idPub}/{idClient}/newfront', name: 'app_commentaire_newfront', methods: ['GET', 'POST'])]
    public function newfront(Request $request, $idPub, $idClient, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la publication associée au commentaire
        $publication = $entityManager->getRepository(Publication::class)->findOneBy(['idPub' => $idPub, 'idClient' => $idClient]);
    
        // Vérifier si la publication existe
        if (!$publication) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }
    
        // Créer une nouvelle instance de l'entité Commentaire
        $commentaire = new Commentaire();
        $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
        $commentaire->setDateCom($dateTime);
        $commentaire->setIdPub($publication); // Associer le commentaire à la publication
    
        // Associer également le client au commentaire
        // Vous pouvez utiliser l'identifiant du client ou récupérer l'entité client à partir de son identifiant
        // Dans cet exemple, nous supposerons que vous avez déjà récupéré l'entité client à partir de son identifiant
    
        // Récupérer l'entité client à partir de son identifiant
        $client = $entityManager->getRepository(User::class)->find($idClient);
    
        // Associer le client au commentaire
        $commentaire->setIdClient($client);
    
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            // Redirection vers la route app_publication_commentaire avec l'IDPub
            return $this->redirectToRoute('app_publication_commentaire', ['idPub' => $idPub, 'idClient' => $idClient], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('commentaire/newfrontcomment.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
            'idClient' => $idClient,
        ]);
    }
    
    
    
    #[Route('/newcomment', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function newcomment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setDateCom(new \DateTime());
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_publication_commentaire', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{idCom}/{idClient}', name: 'app_commentaire_show', methods: ['GET'])]
public function show(Commentaire $commentaire, $idClient): Response
{
    return $this->render('commentaire/show.html.twig', [
        'commentaire' => $commentaire,
        'idClient' => $idClient, // Passer idClient au template Twig si nécessaire
    ]);
}

#[Route('/{idCom}/{idClient}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager, $idClient): Response
{
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $idPub = $commentaire->getIdPub()->getIdPub();

        // Redirection vers la route 'app_publication_comments' avec les paramètres nécessaires
        return $this->redirectToRoute('app_publication_comments', [
            'idPub' => $idPub,
            'idClient' => $idClient,
        ]);
    }

    return $this->renderForm('commentaire/edit.html.twig', [
        'commentaire' => $commentaire,
        'form' => $form,
        'idClient' => $idClient,
        'idPub' => $commentaire->getIdPub()->getIdPub(), // Passer idPub au template Twig
    ]);
}



#[Route('/{idCom}/{idClient}/editfront', name: 'app_commentaire_editfront', methods: ['GET', 'POST'])]
public function editfront(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
{
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    // Récupérer l'ID du client associé au commentaire
    $idClient = $commentaire->getIdClient()->getId();

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        // Récupérer l'IDPub associé au commentaire
        $idPub = $commentaire->getIdPub()->getIdPub();

        // Rediriger vers la route app_publication_commentaire avec l'IDPub et l'IDClient
        return new RedirectResponse($urlGenerator->generate('app_publication_commentaire', ['idPub' => $idPub, 'idClient' => $idClient]));
    }

    return $this->renderForm('commentaire/editcomfront.html.twig', [
        'commentaire' => $commentaire,
        'form' => $form,
        'idClient' => $idClient,
    ]);
}


#[Route('/{idCom}/{idClient}/supprimer', name: 'app_commentaire_supprimer', methods: ['POST'])]
public function supprimer(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
{
    if ($this->isCsrfTokenValid('delete'.$commentaire->getIdCom(), $request->request->get('_token'))) {
        $idPub = $commentaire->getIdPub()->getIdPub(); // Récupérer l'ID de la publication associée au commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();
    }

    // Rediriger vers la route app_publication_commentaire avec l'IDPub et l'IDClient
    return $this->redirectToRoute('app_publication_comments', [
        'idPub' => $idPub,
        'idClient' => $commentaire->getIdClient()->getId()
    ]);
}

#[Route('/{idCom}/{idClient}/delete', name: 'app_commentaire_delete', methods: ['POST'])]
public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
{
    if ($this->isCsrfTokenValid('delete'.$commentaire->getIdCom(), $request->request->get('_token'))) {
        $idPub = $commentaire->getIdPub()->getIdPub(); // Récupérer l'ID de la publication associée au commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();
    }

    // Rediriger vers la route app_publication_commentaire avec l'IDPub et l'IDClient
    return new RedirectResponse($urlGenerator->generate('app_publication_commentaire', ['idPub' => $idPub, 'idClient' => $commentaire->getIdClient()->getId()]), Response::HTTP_SEE_OTHER);
}
}
