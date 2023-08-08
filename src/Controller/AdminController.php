<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/article/modifier/{id}', name:"blog_modifier")]
    #[Route('/article/ajout', name:'blog_ajout')]
    public function form(Request $globals, EntityManagerInterface $manager, Article $article = null): Response
    {
        if($article == null)
        {
            $article = new Article;
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($globals);
        // * handleRequest() permet de récuperer toutes les données de mes inputs

        if($form->isSubmitted() && $form->isValid())
        {
            $article->setCreatedAt(new \DateTimeImmutable);
            // dd($article);
            // * persist() va permettre de préparer ma requete sql a envoyer par rapport à l'objet donné en argument
            $manager->persist($article);
            // * flush() va permettre d'executer tous les persist précédent
            $manager->flush();
            // * redirectToRoute() permet de rediriger vers une autre page de notre site à l'aide du nom de la route (name)
            return $this->redirectToRoute('blog_gestion');

        }

        return $this->render('admin/article/form.html.twig', [
            'form' => $form,
            'editMode' => $article->getId() !== null
        ]);
    }
    #[Route('/article/gestion', name: 'blog_gestion')]
    public function gestion(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();
        return $this->render('admin/article/gestion.html.twig', [
            'articles' => $articles,
        ]);        
    }

    #[Route('/article/supprimer/{id}', name:'blog_supprimer')]
    public function supprimer(Article $article, EntityManagerInterface $manager)
    {
       $manager->remove($article);
       $manager->flush();
       return $this->redirectToRoute('blog_gestion');
    }
}
