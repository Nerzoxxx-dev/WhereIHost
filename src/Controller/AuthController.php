<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\AppUtils;
use App\Utils\NotificationUtils;
use App\Utils\StringUtils;
use App\Utils\UserUtils;
use App\Utils\Validator\Validator;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthController extends NeedEmailVerificationController
{
    /**
     * @Route("/auth", name="auth")
     */
    public function viewAuth(): Response
    {
        return $this->redirectToRoute('viewLogin');
    }

    /**
     * @Route("/auth/register", methods={"POST"}, name="create_user")
     *
     * @return Response
     */
    public function createUser(Request $request, TranslatorInterface $trans, ValidatorInterface $symfonyValidator, MailerInterface $mailer): Response {
        $requirements = ["email", "username", "first_name", "last_name", "phone_number", "password", "password_confirm"];
        foreach($requirements as $requirement){
            if(!$request->request->has($requirement) || empty($request->request->get($requirement))) return $this->render('auth/register.html.twig', ["errors" => $trans->trans('auth.register.errors.missing', ['%field%' => $trans->trans($requirement, [], 'base')], 'auth'), "appname" => AppUtils::getAppName()]);
        }

        $validator = new Validator();
        $errors = $validator
                ->string($trans, [$request->request->get('username'), "username"], [$request->request->get('first_name'), "first_name"], [$request->request->get('last_name'), "last_name"], [$request->request->get('email'), "email"], [$request->request->get('password'), "password"])
                ->phoneNumber($trans, $request->request->get('phone_number'), 'phone_number')
                ->email($request->request->get('email'), $trans)
                ->password($trans, $request->request->get('password'), "password")
                ->validate();
        
        if(!empty($errors)) return $this->render('auth/register.html.twig', ["errors" => $errors, "appname" => AppUtils::getAppName()]);

        if($request->request->get('password') !== $request->request->get('password_confirm')) return $this->render('auth/register.html.twig', ["errors" => $trans->trans('auth.register.password_not_match', [], 'auth'), "appname" => AppUtils::getAppName()]);

        if(UserUtils::accountExists($request->request, $this->getDoctrine(), $trans)) return $this->render('auth/register.html.twig', ['errors' => $trans->trans("auth.register.account_already_exists", [], 'auth'), 'appname' => AppUtils::getAppName()]);

        $password_hash = password_hash($request->request->get('password'), PASSWORD_BCRYPT);
        $doctrine = $this->getDoctrine();
        $entityManager = $doctrine->getManager();

        $token = StringUtils::checkUserToken(StringUtils::random(40), $doctrine);

        $user = new User();
        $user->setUsername($request->request->get('username'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setLastName($request->request->get('last_name'));
        $user->setLocale('en');
        $user->setEmail($request->request->get('email'));
        $user->setPhoneNumber($request->request->get('phone_number'));
        $user->setPasswordHash($password_hash);
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setToken($token);

        $entityManager->persist($user);
        $entityManager->flush();

        $appurl = AppUtils::getUrl();

        $email = (new Email())
                ->from('no-reply@clientarea.fr')
                ->to($user->getEmail())
                ->subject($trans->trans('auth.email.title_verify_email', [], 'auth'))
                ->text($trans->trans('auth.email.text', [], 'auth'))
                ->html("<p> {$trans->trans('auth.email.p.verify', ['%username%' => $user->getUsername()], 'auth')} </p> <br/> <a href='$appurl/auth/email?token=$token'> {$trans->trans('auth.email.a.verify', [], 'auth')} </a>");
        
        $mailer->send($email);

        $session = $this->requestStack->getSession();
        $session->set('userLoginId', $user->getId());
        setCookie('lastUserConnected', $user->getEmail());
        NotificationUtils::create($trans->trans('notification.create_user', ['%username%' => $user->getUsername()], 'notification'), 'user', $this->getDoctrine());
        return $this->render('auth/register.html.twig', ["appname" => AppUtils::getAppName(), "success" => $trans->trans("auth.register.account_created", [], "auth")]);
    }

    /** @Route("/auth/register", name="viewRegister", methods="GET") */
    public function viewRegister(): Response {
        if($this->requestStack->getSession()->has('userLoginId') && UserUtils::userExists($this->requestStack->getSession()->get('userLoginId'), $this->getDoctrine())) return $this->redirectToRoute('home');
        return $this->render("auth/register.html.twig", ["appname" => AppUtils::getAppName()]);
    }

    /** @Route("/auth/email", name="viewEmail", methods="GET") */
    public function viewEmail(Request $request, TranslatorInterface $trans): Response{
        if($this->emailVerification() !== true && !$request->query->has('token')) return $this->render("auth/email.html.twig", ['appname' => AppUtils::getAppName(), 'noverified' => true]);
        if($this->emailVerification() === true) return $this->redirectToRoute('viewRegister');
        if($token = $request->query->has('token')){
            $token = $request->query->get('token');
            $doctrine = $this->getDoctrine();
            $user = $doctrine->getRepository(User::class)->findOneBy(['token' => $token]);
            if(!$user) return $this->render('auth/email.html.twig', ['appname' => AppUtils::getAppName(), 'error' => $trans->trans('auth.email.invalid_token', [], 'auth')]);
            $user->setToken(null);
            $user->setVerifiedAt(new DateTimeImmutable());
            $doctrine->getManager()->persist($user);
            $doctrine->getManager()->flush();

            return $this->render('auth/email.html.twig', ['appname' => AppUtils::getAppName(), 'success' => $trans->trans('auth.email.verfication_success', [], 'auth')]);
        }
    }

    /** @Route("/auth/password", methods="GET", name="viewPassword") */
    public function viewPassword(Request $request): Response{
        return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName()]);
    }

    /** @Route("/auth/password", methods="POST", name="postPassword") */
    public function postPassword(Request $request, TranslatorInterface $trans, MailerInterface $mailer): Response{
        $errors = (new Validator())
                  ->email($request->request->get('email'), $trans)
                  ->validate();
        
        if(!empty($errors)) return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $errors[0]]);

        if(!UserUtils::userExistsByEmail($request->request->get('email'), $this->getDoctrine(), $trans)) return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.password.unknow_user', ['%email%' => $request->request->get('email')], 'auth')]);

        $user = UserUtils::getByEmail($request->request->get('email'), $this->getDoctrine(), $trans);
        $token = StringUtils::checkUserPasswordToken(StringUtils::random(40), $this->getDoctrine());
        $appurl = AppUtils::getUrl();

        $user->setPasswordToken($token);
        $user->setPasswordResetAt(new DateTimeImmutable());

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $email = (new Email())
                ->from('no-reply@clientarea.fr')
                ->to($user->getEmail())
                ->subject($trans->trans('auth.password.email.title_email', [], 'auth'))
                ->text($trans->trans('auth.password.email.text', [], 'auth'))
                ->html("<p> {$trans->trans('auth.password.email.p.verify', ['%username%' => $user->getUsername()], 'auth')} </p> <br/> <a href='$appurl/auth/password/change?token=$token'> {$trans->trans('auth.password.email.a.verify', [], 'auth')} </a>");

        $mailer->send($email);

        return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName(), 'success' => $trans->trans('auth.password.email_send', [], 'auth')]);
    }
    
    /** @Route("/auth/password/change", methods="GET", name="passwordChange") */
    public function passwordChange(Request $request, TranslatorInterface $trans): Response {
        if(!$request->query->has('token') || !UserUtils::userExistsByPasswordToken($request->query->get('token'), $this->getDoctrine())) return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.password.invalid_token', [], 'auth')]);

        $user = UserUtils::getByPasswordToken($request->query->get('token'), $this->getDoctrine());
        if($user->getPasswordResetAt()->diff(new DateTimeImmutable())->d >= 1) return $this->render('auth/password.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.password.link_expire', [], 'auth')]);
        setcookie('passwordtoken', base64_encode($request->query->get('token')));
        return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName()]);
    }

    /** @Route("/auth/password/change", methods="POST", name="postPasswordChange") */
    public function postPasswordChange(Request $request, TranslatorInterface $trans): Response {
        if(!isset($_COOKIE['passwordtoken'])) return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.password.error', [], 'auth')]);

        $errors = (new Validator())->password($trans, $request->request->get('password'), $trans->trans('password', [], 'base'))->validate();
        if(!empty($errors)) return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $errors[0]]);

        if($request->request->get('password') !== $request->request->get('password_confirm')) return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.register.password_not_match', [], 'auth')]);

        $user = UserUtils::getByPasswordToken(base64_decode($_COOKIE['passwordtoken']), $this->getDoctrine());
        if(!$user) return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.password.error', [], 'auth')]);

        $user->setPasswordResetAt(null);
        $user->setPasswordToken(null);
        $user->setPasswordHash(password_hash($request->request->get('password'), PASSWORD_BCRYPT));

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($user);
        $entityManager->flush();

        setCookie('passwordtoken', null);
        
        $session = $this->requestStack->getSession();
        if($session->has('userLoginId')) $session->remove('userLoginId');

        return $this->render('auth/passwordchange.html.twig', ['appname' => AppUtils::getAppName(), 'success' => $trans->trans('auth.password.changed', [], 'auth')]);
    }

    /** @Route("/auth/logout", methods="GET", name="logout") */
    public function logout(): Response {
        $session = $this->requestStack->getSession();
        if(!$session->has('userLoginId')) return $this->redirectToRoute('viewLogin');
        $session->remove('userLoginId');
        return $this->redirectToRoute('viewLogin');
    }

    /** @Route("/auth/login", methods="GET", name="viewLogin") */
    public function viewLogin(): Response {
        $lastEmail = "";
        if(isset($_COOKIE['lastUserConnected'])) $lastEmail = $_COOKIE['lastUserConnected'];
        if($this->requestStack->getSession()->has('userLoginId') && UserUtils::userExists($this->requestStack->getSession()->get('userLoginId'), $this->getDoctrine())) return $this->redirectToRoute('home');
        return $this->render("auth/login.html.twig", ["appname" => AppUtils::getAppName(), "lastEmail" => $lastEmail]);
    }

    /** @Route("/auth/login", methods="POST", name="postLogin") */
    public function postLogin(Request $request, TranslatorInterface $trans){
        $lastEmail = "";
        if(isset($_COOKIE['lastUserConnected'])) $lastEmail = $_COOKIE['lastUserConnected'];
        $requirements = ["email", "password"];
        foreach($requirements as $requirement){
            if(!$request->request->has($requirement) || empty($request->request->get($requirement))) return $this->render('auth/login.html.twig', ["errors" => $trans->trans('auth.register.errors.missing', ['%field%' => $trans->trans($requirement, [], 'base')], 'auth'), "appname" => AppUtils::getAppName(), "lastEmail" => $lastEmail]);
        }

        $errors = (new Validator())
                  ->email($request->request->get('email'), $trans)
                  ->password($trans, $request->request->get('password'), "password")
                  ->validate();
        
        if(!empty($errors)) return $this->render('auth/login.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $errors[0], 'lastEmail' => $lastEmail]);

        if(!UserUtils::userExistsByEmail($request->request->get('email'), $this->getDoctrine(), $trans)) return $this->render('auth/login.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.login.not_exists_by_email', [], 'auth'), 'lastEmail' => $lastEmail]);

        $user = UserUtils::getByEmail($request->request->get('email'), $this->getDoctrine(), $trans);
        $password_hash = $user->getPasswordHash();
        if(!password_verify($request->request->get('password'), $password_hash)) return $this->render('auth/login.html.twig', ['appname' => AppUtils::getAppName(), 'errors' => $trans->trans('auth.login.invalid_password', [], 'auth'), 'lastEmail' => $lastEmail]);

        ($session = $this->requestStack->getSession())->set('userLoginId', $user->getId());
        setCookie('lastUserConnected', $user->getEmail());

        if(!UserUtils::isVerified($user->getId(), $this->getDoctrine())) return $this->redirectToRoute('pleaseVerifyEmail');

        return $this->render('auth/login.html.twig', ['appname' => AppUtils::getAppName(), 'success' => $trans->trans('auth.login.connected', [], 'auth')]);
    }

    /** @Route("/auth/email/verify", methods="GET", name="pleaseVerifyEmail") */
    public function viewEmailVerifyDemand(): Response {
        if(!$this->requestStack->getSession()->has('userLoginId') || !UserUtils::userExists($this->requestStack->getSession()->get('userLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewLogin');
        if(UserUtils::isVerified($this->requestStack->getSession()->get('userLoginId'), $this->getDoctrine())) return $this->redirectToRoute('home');
        return $this->render('auth/emaildemand.html.twig', ['appname' => AppUtils::getAppName()]);
    }
}
