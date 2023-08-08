<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    public function form(Request $globals, EntityManagerInterface $manager, Article $article = null, SluggerInterface $slugger): Response
    {
        if($article == null)
        {
            $article = new Article;
        }
        $editMode = ($article->getId() !== null);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($globals);
        // * handleRequest() permet de récuperer toutes les données de mes inputs

        if($form->isSubmitted() && $form->isValid())
        {
            // ! Début traitement de l'image
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                // * Permet de récuperer le nom de notre fichier de base
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                // * On enleve se qui gene dans le nom du fichier si on l'utilise en URL
                $safeFilename = $slugger->slug($originalFilename);

                // * On crée un nouveau nom de fichier pour notre image qui sera : nomSafeDuFichier-idUnique.extensionDuFichier
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('img_upload'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $article->setImage($newFilename);
            }
            // ! fin du traitement de l'image
            $article->setCreatedAt(new \DateTime);
            // dd($article);
            // * persist() va permettre de préparer ma requete sql a envoyer par rapport à l'objet donné en argument
            $manager->persist($article);
            // * flush() va permettre d'executer tous les persist précédent
            $manager->flush();
            if($editMode)
            {
                $this->addFlash('success', "L'article a bien été modifié");                
            } else {
                $this->addFlash('success', "L'article a bien été ajouté");
            }
            // * redirectToRoute() permet de rediriger vers une autre page de notre site à l'aide du nom de la route (name)
            return $this->redirectToRoute('blog_gestion');

        }

        return $this->render('admin/article/form.html.twig', [
            'form' => $form,
            'editMode' => $editMode,
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
