<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * @Route("/auth", name="auth")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/auth/register", methods={"POST"}, name="create_user")
     *
     * @return Response
     */
    public function createUser(Request $request): Response {
        $requirements = ["username", "first_name", "last_name", "password", "phone_number", "email"];
        foreach($requirements as $requirement){
            if(!$request->request->has($requirement)) return new Response("Unable to create account: null $requirement");
        }
        $password_hash = password_hash($request->request->get('password'), PASSWORD_BCRYPT);
        $entityManager = $this->getDoctrine()->getManager();

        $user = new User();
        $user->setUsername($request->request->get('username'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setLocale('en');
        $user->setEmail($request->request->get('email'));
        $user->setPhoneNumber($request->request->get('phone_number'));
        $user->setPasswordHash($password_hash);
        $user->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response("Account created!");
    }
}
