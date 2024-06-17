<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;

class StatistiqueController extends AbstractController
{
    #[Route('/statistique/{idClient}', name: 'app_statistique_index')]
    public function statistiqueProduitPrix(EntityManagerInterface $manager, $idClient): Response
    {
        $publications = $manager->getRepository(Publication::class)->findAll();
        $produitsPrixQuantite = [];
        foreach ($publications as $publication) {
            $produitsPrixQuantite[] = [
                'contenu' => $publication->getContenu(),
                'nbLikes' => $publication->getNbLikes(),
                'nbDislikes' => $publication->getNbDislike(), // Corrected property name
            ];
        }
        return $this->render('statistique/index.html.twig', [
            'produitsPrixQuantite' => $produitsPrixQuantite,
            'idClient' => $idClient,
        ]);
    }
}
