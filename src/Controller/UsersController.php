<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('', name: 'app_users')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(Users::class)->findAll();

        $context = ['users' => $users];

        return $this->render('users/index.html.twig', $context);
    }

    #[Route('/create-user', name: 'create_user', methods: ['POST'])]
    public function create_user(Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator): Response
    {
        // $entityManager :: inject the Entity manager service into the controller method
        //                   This object is responsible for Saving, Fetching Objects from the database
        // Get data from the POST request
        dump($request->get('formData'));
        $data = json_decode($request->get('formData'), true);
        dump($data);

        $user = new Users();
        $user->setUsername($data['username']);
        $user->setFullname($data['fullname']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['passwd'], PASSWORD_DEFAULT));

        // Tell doctrine you want to (eventually) save the user (no queries yet)
        // This will manage the product object.
        $entityManager->persist($user);

        // Validating Errors for Null, Unique...
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        // actualy executes the queries (i.e the INSERT query)
        // Insert the new row
        $entityManager->flush();

        return new Response('Saved new user with id '.$user->getId(), 201);
    }

    #[Route('/{id<\d+>}', name: 'show_profile')]
    public function show_profile(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('No user found for id '.$id);
        }

        return $this->render('users/show_profile.html.twig', ['user' => $user]);
        /*
         * When you query for a particular type of object, you always use what's known as its "repository".
         * You can think of a repository as a PHP class whose only job is to help you fetch entities of a certain class
            $repository = $entityManager->getRepository(Product::class);

         * look for a single Product by its primary key (usually "id")
            $product = $repository->find($id);

          * look for a single Product by name
            $product = $repository->findOneBy(['name' => 'Keyboard']);

          * or find by name and price
            $product = $repository->findOneBy(['name' => 'Keyboard', 'price' => 1999,]);

          * look for multiple Product objects matching the name, ordered by price
            $products = $repository->findBy(['name' => 'Keyboard'], ['price' => 'ASC']);

          * look for *all* Product objects
            $products = $repository->findAll();
        */
    }

    // another method without the $manager
    // public function show_profile(ProductRepository $productRepository, int $id): Response
    // {
    // $product = $productRepository->find($id);

    // // ...
    // }

    #[Route('/edit/{id<\d+>}', name: 'update_user')]
    public function update_user(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('No User found for id '.$id);
        }
        $user->setFullname('Ismail');
        $entityManager->flush();

        return $this->redirectToRoute('show_profile', ['id' => $user->getId()]);
    }

    #[Route('/delete/{id<\d+>}', name: 'delete_user')]
    public function delete_user(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('No User found for id '.$id);
        }
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('show_profile', ['id' => $user->getId()]);
    }
}
