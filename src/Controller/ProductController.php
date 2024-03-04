<?php

namespace App\Controller;

use App\Entity\DrivingSchool;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        $productFiltredLess = $request->query->get('productFiltredLess');
        $productFiltredGreater = $request->query->get('productFiltredGreater');
        if ($productFiltredLess) {
            $products = $productRepository->findProductsLessThanPrice($productFiltredLess, $schoolSelected);
        } elseif ($productFiltredGreater) {
            $products = $productRepository->findProductsGreaterThanPrice($productFiltredGreater, $schoolSelected);
        } else {
            $products = $productRepository->findByDrivingSchoolId($schoolSelected);
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'drivingSchool' => $schoolSelected,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $drivingSchool = $entityManager->getRepository(DrivingSchool::class)->findOneById($schoolSelected);
            $product->setDrivingSchool($drivingSchool);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form,
            'drivingSchool' => $schoolSelected,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    #[Security('is_granted("ROLE_ADMIN") || (is_granted("ROLE_BOSS") && user.getDrivingSchools().contains(product.getDrivingSchool()))')]
    public function show(Product $product, Request $request): Response
    {

        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'drivingSchool' => $schoolSelected,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_ADMIN") || (is_granted("ROLE_BOSS") && user.getDrivingSchools().contains(product.getDrivingSchool()))')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected'); 

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'drivingSchool' => $schoolSelected,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN") || (is_granted("ROLE_BOSS") && user.getDrivingSchools().contains(product.getDrivingSchool()))')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {   
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->get('_token'))) {
            dd('hello');
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
