<?php

namespace App\Controller;

use App\Form\CommentType;
use App\Entity\Comment;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Utils\Editable;


class CommentController extends AbstractController implements Editable
{	

	public function __construct(TokenStorageInterface $tokenStorage)
    {
    	$this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @Route("/creation_comment/{article}", name="create_comment")
     */
    public function create(Request $request, $article){

    	$comment = new Comment();
        $article = $request->attributes->get('article');
    	$form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setUser($this->user);

            $entityManager = $this->getDoctrine()->getManager();
            $articleO = $this->getDoctrine()
                ->getRepository('App:Article')
                ->find($article);

            $comment->setArticle($articleO);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();



            return $this->redirectToRoute('default_index');
        }

        return $this->render(
            'blog/comment_create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/edition_comment/{id}", name="edit_comment")
     */
    public function edit(Request $request, $id){
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $entityManager->getRepository('App:Comment')->find($id);

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('default_index');
        }

        return $this->render('blog/comment_create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/deletation_comment/{id}", name="delete_comment")
     */
    public function delete($id){
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $this->getDoctrine()
            ->getRepository('App:Comment')
            ->find($id);
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('default_index');
    }
}