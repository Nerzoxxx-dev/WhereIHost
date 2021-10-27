<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\AppUtils;
use App\Utils\Validator\Validator;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function createUser(Request $request, TranslatorInterface $trans): Response {
        $requirements = ["username", "first_name", "last_name", "password", "phone_number", "email"];
        foreach($requirements as $requirement){
            if(!$request->request->has($requirement) || empty($request->request->get($requirement))) return $this->render('auth/register.html.twig', ["errors" => $trans->trans('auth.register.errors.missing', ['%field%' => $trans->trans($requirement, [], 'base')], 'auth'), "appname" => AppUtils::getAppName()]);
        }

        $validator = new Validator();
        $errors = $validator
                ->string($trans, [$request->request->get('username'), "username"], [$request->request->get('first_name'), "first_name"], [$request->request->get('last_name'), "last_name"], [$request->request->get('email'), "email"], [$request->request->get('password'), "password"])
                ->phoneNumber($trans, $request->request->get('phone_number'), 'phone_number')
                ->validate();
        
        if(!empty($errors)) return $this->render('auth/register.html.twig', ["errors" => $errors, "appname" => AppUtils::getAppName()]);

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

    /** @Route("/auth/register", name="viewRegister", methods="GET") */
    public function viewRegister(): Response {
        return $this->render("auth/register.html.twig", ["appname" => AppUtils::getAppName()]);
    }
}
