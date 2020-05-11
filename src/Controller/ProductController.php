<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Product1Type;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="user_product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findBy(['userid'=> $user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(Product1Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $product->setUserid($user->getId());
            /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /** @var file $file */
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
                $product->setImage($filename);
            }
            /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            $product->setStatus("New");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('user_product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(Product1Type::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /** @var file $file */
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
                $product->setImage($filename);
            }
            /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            /// /////*****FİLEUPLOAD****/////
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_product_index');
    }
    private function generateUniqueFileName(){
        return md5(uniqid());
    }
}
