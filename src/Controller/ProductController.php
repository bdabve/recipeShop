<?php

namespace App\Controller;

// Database Settings
use App\Entity\Product;
use App\Form\ProductType;
// Forms
use Doctrine\ORM\EntityManagerInterface;
// Symfony request and return
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Attribute\Route;

/*
 * FIXME:  
 *  ----| Login and Register users
 *  ----| Keyup search problem
 *
 * TODO:
 *  ----| Add a github Repo
 */

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('', name: 'product_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $navbar = 'Product';
        $products = $entityManager->getRepository(Product::class)->findAll();

        $context = ['navbar' => $navbar, 'products' => $products];
        return $this->render('product/index.html.twig', $context);
    }
    // -------------------------------------------------------------------------

    #[Route('/product-detail/{id<\d+>}', name: 'product_detail', methods: ['GET'])]
    public function detail(EntityManagerInterface $entityManager, int $id): Response
    {
        $navbar = 'Product';
        $product = $entityManager->getRepository(Product::class)->find($id);
        if ( !$product ){
            //throw $this->createNotFoundException('No product found for id ' . $id);
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        };

        $context = ['navbar' => $navbar, 'product' => $product];
        return $this->json($product);
    }
    // -------------------------------------------------------------------------

    #[Route('/creat-product', name: 'create_product')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Set created date
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('product_list');
        }

        $navbar = 'Product';
        $context = [
            'navbar' => $navbar,
            'form' => $form->createView(),
        ];

        return $this->render('product/create.html.twig', $context);
    }
    # -------------------------------------------------------------------------

    #[Route('/edit-product/{id<\d+>}', name: 'edit_product')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if( !$product ){
            throw $this->createNotFoundException('Product not found');
        }
        return $this->render('product/edit.html.twig', ['product' => $product]);
    }
    # -------------------------------------------------------------------------

    #[Route('/update-product/{id<\d+>}', name: 'update_product', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // Fetch the product from the database
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        // Update product properties based on form submission
        $product->setName($request->request->get('name'));
        $product->setRef($request->request->get('ref'));
        $product->setPackaging($request->request->get('packaging'));
        $product->setQte($request->request->get('qte'));
        $product->setPrice($request->request->get('price'));
        $product->setTotal($request->request->get('total'));

        $product->setUpdated(new \DateTimeImmutable());

        // Save the updated product
        $entityManager->flush();

        // Redirect to product details page or another route
        return $this->redirectToRoute('product_list');
    }
    # -------------------------------------------------------------------------

    #[Route('/delete-product/{id<\d+>}', name: 'delete_product')]
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        /*
         * This function work with jquery
         */
        $product = $entityManager->getRepository(Product::class)->find($id);
        if( !$product ){
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        if ( $request->isXmlHttpRequest()) {
            $entityManager->remove($product);
            $entityManager->flush();
            return new JsonResponse(['success' => true]);
        }
        // Redirect to a failure page if the request is not AJAX
        return new JsonResponse(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
    # -------------------------------------------------------------------------

    #[Route('/search-product', name: 'search_product')]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Perform product search logic
        $query = $request->query->get('search_word');
        $queryBuilder = $entityManager->createQueryBuilder();

        $products = $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where($queryBuilder->expr()->like('p.name', ':search'))
            ->setParameter('search', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $context = ['navbar' => 'Product', 'products' => $products];
        return $this->render('product/index.html.twig', $context);
    }
    # -------------------------------------------------------------------------
}
