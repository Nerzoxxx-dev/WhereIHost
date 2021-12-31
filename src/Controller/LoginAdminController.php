<?php

namespace App\Controller;

use App\Entity\Admins;
use App\Utils\AdminDashboardUtils;
use App\Utils\AdminUtils;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginAdminController extends AbstractController {

    protected $requestStack;

    public function __construct(RequestStack $requestStack){
        $this->requestStack = $requestStack;
    }
    
    /** @Route("/admin/login", methods="GET", name="viewAdminLogin") */
    public function viewAdminLogin(): Response {
        $session = $this->requestStack->getSession();
        if($session->has('adminLoginId') && AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminDashboard');
        return $this->render('admin/login/login.html.twig', ['datas' => AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession())]);
    }

    /** @Route("/admin/login", methods="POST", name="postAdminLogin") */
    public function postAdminLogin(Request $request, TranslatorInterface $trans) {
        if(!AdminUtils::adminExistsByEmail($request->request->get('id'), $this->getDoctrine(), $trans)) {
            $this->requestStack->getSession()->set('message', $trans->trans('admin.not_exists', [], 'admin'));
            return $this->redirectToRoute('viewAdminLogin');
        }

        $user = AdminUtils::getAdminByEmail($request->request->get('id'), $this->getDoctrine());
        if(is_null($user)){ 
            $this->requestStack->getSession()->set('message', $trans->trans('admin.not_exists', [], 'admin'));
            return $this->redirectToRoute("viewAdminLogin");
        }
        if(!password_verify($request->request->get('password'), $user->getPasswordHash())){
            $this->requestStack->getSession()->set('message', $trans->trans('admin.password_does_not_matches', [], 'admin'));
            return $this->redirectToRoute("viewAdminLogin");
        }
        $this->requestStack->getSession()->set('adminLoginId', $user->getId());
        return $this->redirectToRoute('viewAdminDashboard');
    }

    /** @Route("/admin/logout", methods="GET", name="adminLogout") */
    public function adminLogout(TranslatorInterface $trans): Response {
        if(!($session = $this->requestStack->getSession())->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute("viewAdminLogin");
        $session->remove('adminLoginId');
        $session->set('message', $trans->trans('admin.disconnected', [], 'admin'));
        return $this->redirectToRoute('viewAdminLogin');
    }
}