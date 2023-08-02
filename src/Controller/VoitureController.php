<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
    #[Route('/voiture', name: 'voiture')]
    public function index(VoitureRepository $repo): Response
    {
        $voitures = $repo->findAll();
        return $this->render('voiture/index.html.twig', [
            'items' => $voitures,
        ]);
    }

    #[Route('/voiture/ajout', name:'voiture_ajout')]
    public function ajout(Request $request, EntityManagerInterface $manager): Response
    {
        $voiture = new Voiture;


        $form = $this->createForm(VoitureType::class, $voiture);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // * persist() permet de préparer les requetes SQL par rapport à l'objet qu'on lui donne en parametre
            $manager->persist($voiture);
            // * flush() permet d'executer toutes les requetes
            $manager->flush();
            return $this->redirectToRoute('voiture');
        }

        return $this->render('voiture/ajout.html.twig', [
            'formVoiture' => $form,
        ]);
    }

}
