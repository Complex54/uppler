<?php

namespace App\Controller;

use App\Form\ArticleType;
use App\Form\CommentType;
use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Utils\Editable;


class ArticleController extends AbstractController implements Editable
{	

	public function __construct(TokenStorageInterface $tokenStorage)
    {
    	$this->user = $tokenStorage->getToken()->getUser();
    }
	/**
     * @Route("/index", name="default_index")
     */
    public function index(): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

    	$list_comment = [];
    	$entityManager = $this->getDoctrine()->getManager();
    	$articles = $this->getDoctrine()  
		    ->getRepository('App:Article')
		    ->findAll();  //Trouve tous les articles
		foreach ($articles as $article){
            if ($entityManager->getRepository('App:Comment')->findByArticle($article->getId()) != "null") {
                //pour chaque article, trouves tous les commentaires et push dans un array
                array_push($list_comment, $entityManager->getRepository('App:Comment')->findByArticle($article->getId()));
            }
		}
		//var_dump($articles);
        //return new Response('Bonjour ' . $this->user->getUsername());
        return $this->render('blog/article.html.twig',
        	array('articles' => $articles,
        		  'comments' => $list_comment,
                  'form' => $form->createView()
            ));
    }

    /**
     * @Route("/creation_article", name="create_article")
     */
    public function create(Request $request){
    	$article = new Article();
    	$form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $article->setUser($this->user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();



            return $this->redirectToRoute('default_index');
        }

        return $this->render(
            'blog/article_create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/edition_article/{id}", name="edit_article")
     */
    public function edit(Request $request, $id){
        $article = new Article();
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository('App:Article')->find($id);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('default_index');
        }

        return $this->render('blog/article_create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/deletation_article/{id}", name="delete_article")
     */
    public function delete($id){

        $entityManager = $this->getDoctrine()->getManager();

        $comments = $entityManager->getRepository('App:Comment')->findByArticle($id);

        foreach ($comments as $comment){
            $entityManager->remove($comment);
        }

        $article = $this->getDoctrine()
            ->getRepository('App:Article')
            ->find($id);

        
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('default_index');
    }
}