<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\User;
use App\Form\Admin\CommentType;
use App\Form\UserType;
use App\Repository\Admin\CommentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
        //echo $user->getId();
        //die();
        $comments = $commentRepository->getAllCommentsUser($user->getId());
        //dump($comments);
        //die();

        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,
        ]);
    }

    /**
     * @Route("/products", name="user_products", methods={"GET"})
     */
    public function products(): Response
    {
        return $this->render('user/products.html.twig');
    }

    /**
     * @Route("/orders", name="user_orders", methods={"GET"})
     */
    public function orders(): Response
    {
        return $this->render('user/orders.html.twig');
    }


    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            ///*** File Upload ***///
            $file = $form['image']->getData();
            if($file){
                $filename = $this->generateUniqueFileName() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                } catch (FileException $e){
                }
                $user->setImage($filename);
            }
            ///*** File Upload ***///
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,$id, User $user,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        if($user->getId() != $id)
        {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        /// *** File Upload *** ///
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if($file){
                $filename = $this->generateUniqueFileName() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                } catch (FileException $e){
                }
                $user->setImage($filename);
            }
            /// *** File Upload *** ///
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    private function generateUniqueFileName(){
        return md5(uniqid());
    }

    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function newcomment(Request $request,$id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');
        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('comment', $submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setProductid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());
                $entityManager->persist($comment);
                $entityManager->flush();
                $this->addFlash('success','Your Comment has been send successfully');
                return $this->redirectToRoute('product_show', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('product_show', ['id' => $id]);
    }
}
