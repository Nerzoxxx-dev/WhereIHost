<?php

namespace App\Controller;

use App\Entity\Hosts;
use App\Entity\Notification;
use App\Repository\HostsRepository;
use App\Utils\AdminDashboardUtils;
use App\Utils\AdminUtils;
use App\Utils\AppUtils;
use App\Utils\HostUtils;
use App\Utils\UserUtils;
use DateTimeImmutable;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardAdminController extends AbstractController {

    protected $requestStack;

    public function __construct(RequestStack $requestStack){
        $this->requestStack = $requestStack;
    }

    /** @Route("/admin", methods="GET", name="adminHome") */
    public function adminHome(): Response{
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        return $this->redirectToRoute('viewAdminDashboard');
    }

    /** @Route("/admin/dashboard", methods="GET", name="viewAdminDashboard") */
    public function viewAdminDashboard(): Response {
        if(!($session = $this->requestStack->getSession())->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');

        $data = AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession());
        return $this->render('admin/dashboard/home.html.twig', ["appname" => AppUtils::getAppName(), "datas" => $data]);
    }

    /** @Route("/admin/notification/deleteall", methods="GET", name="deleteAllNotifications") */
    public function deleteAllNotification(TranslatorInterface $trans): Response {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        $notifications = $this->getDoctrine()->getRepository(Notification::class)->findAll();

        foreach($notifications as $notification){
            $this->getDoctrine()->getManager()->remove($notification);
            $this->getDoctrine()->getManager()->flush();
        }

        $this->requestStack->getSession()->set('message', $trans->trans('admin.notifications_deleted', [], 'admin'));
        return $this->redirectToRoute("viewAdminDashboard");
    }

    /** @Route("/admin/notification/delete/{id}", methods="GET", name="deleteNotification") */
    public function deleteNotification(TranslatorInterface $trans, $id): Response {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        $notification = $this->getDoctrine()->getRepository(Notification::class)->find($id);

        $this->getDoctrine()->getManager()->remove($notification);
        $this->getDoctrine()->getManager()->flush();

        $this->requestStack->getSession()->set('message', $trans->trans('admin.notification_deleted', [], 'admin'));
        return $this->redirectToRoute("viewAdminDashboard");
    }

    /** @Route("/admin/hosts", methods="GET", name="viewHosts") */
    public function viewHosts(): Response {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        return $this->render('admin/dashboard/hosts.html.twig', ['appname' => AppUtils::getAppName(), 'datas' => AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession())]);
    }

    /** @Route("/admin/hosts/{id}/manage", methods="GET", name="manageHost") */
    public function manageHost($id, TranslatorInterface $trans): Response{
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }

        $img = [];
        $host = HostUtils::getHost($id, $this->getDoctrine());
        $i = 0;
        foreach($host->getVerificationProofs() as $verificationProof){
            $link = "/hosts/{$host->getId()}/verification/{$i}";
            $img[] = ['name' => $verificationProof, 'link' => $link]; 
            $i++;
        }

        if($host->getIsSuspend()){
            return $this->render("admin/dashboard/host/manage.html.twig", ['appname' => AppUtils::getAppName(), 'datas' => AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession()), 'host' => HostUtils::getHost($id, $this->getDoctrine()), 'proofImg' => $img, 'host_author' => UserUtils::getById(HostUtils::getHost($id, $this->getDoctrine())->getAuthorId(), $this->getDoctrine()), 'suspend_by' => AdminUtils::getAdmin($host->getSuspendBy(), $this->getDoctrine())]);
        }

        return $this->render("admin/dashboard/host/manage.html.twig", ['appname' => AppUtils::getAppName(), 'datas' => AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession()), 'host' => HostUtils::getHost($id, $this->getDoctrine()), 'proofImg' => $img, 'host_author' => UserUtils::getById(HostUtils::getHost($id, $this->getDoctrine())->getAuthorId(), $this->getDoctrine())]);
    }

    /** @Route("/admin/hosts/{id}/unverify", methods="GET", name="unverifyHost") */
    public function unverifyHost($id, TranslatorInterface $trans): Response{
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }
        $host = HostUtils::getHost($id, $this->getDoctrine());
        if(!$host->getIsVerified()){
            $session->set('message', $trans->trans('admin.host_not_verified', [], 'admin'));
            return $this->redirectToRoute('manageHost', ['id' => $id]);
        }
        $host->setIsVerified(false);
        $host->setVerifiedAt(null);
        $this->getDoctrine()->getManager()->persist($host);
        $this->getDoctrine()->getManager()->flush();

        $session->set('message', $trans->trans('admin.host_unverified', [], 'admin'));
        return $this->redirectToRoute("manageHost", ['id' => $id]);
    }

    /** @Route("/admin/hosts/{id}/verify", methods="GET", name="verifyHost") */
    public function verifyHost($id, TranslatorInterface $trans): Response{
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }
        $host = HostUtils::getHost($id, $this->getDoctrine());
        if($host->getIsVerified()){
            $session->set('message', $trans->trans('admin.host_already_verified', [], 'admin'));
            return $this->redirectToRoute('manageHost', ['id' => $id]);
        }
        $host->setIsVerified(true);
        $host->setVerifiedAt(new DateTimeImmutable());
        $this->getDoctrine()->getManager()->persist($host);
        $this->getDoctrine()->getManager()->flush();

        $session->set('message', $trans->trans('admin.host_verified', [], 'admin'));
        return $this->redirectToRoute("manageHost", ['id' => $id]);
    }

    /** @Route("/admin/hosts/{id}/edit", methods="POST", name="editHostInfos") */
    public function editHostInfos($id, Request $request, TranslatorInterface $trans): Response{

        $token = $request->request->get('token');
        if(!$this->isCsrfTokenValid('authenticate_admin', $token)){
            return $this->redirectToRoute('viewAdminLogin');
        }

        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }
        $host = HostUtils::getHost($id, $this->getDoctrine());
        if(!empty($request->request->get('host_name')) && $request->request->has('host_name')){
            $host->setName($request->request->get('host_name'));
        }
        if(!empty($request->request->get('host_description')) && $request->request->has('host_description')){
            $host->setDescription($request->request->get('host_description'));
        }

        $host->setLegalNumber($request->request->get('host_legal_number'));
        $host->setWebsite($request->request->get('host_website'));

        $image = $request->files->get('logo');
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if(!is_null($image)){
            if(!is_readable($image)){
                $session->set('message', $trans->trans('file_not_readable', [], 'base'));
                return $this->redirectToRoute('manageHost', ['id' => $id]);
            }
            if(filesize($image) > 8000000){
                $session->set('message', $trans->trans('file_too_heavy', [], 'base'));
                return $this->redirectToRoute('manageHost', ['id' => $id]);
            }
            $extension = $image->guessExtension();
            if(!in_array($extension, $allowed_extensions)){
                $session->set('message', $trans->trans('extension_not_allowed', [], 'base'));
                return $this->redirectToRoute('manageHost', ['id' => $id]);
            }
            $filename = md5($id) . '.' . $extension;
            if(file_exists('../public/img/hosts/' . $host->getLogoFilename()) && !is_dir('../public/img/hosts/' . $host->getLogoFilename())){
                unlink('../public/img/hosts/' . $host->getLogoFilename());
            }
            $image->move('../public/img/hosts/', $filename);
            $host->setLogoFilename($filename);
        }

        $this->getDoctrine()->getManager()->persist($host);
        $this->getDoctrine()->getManager()->flush();

        $session->set('message', $trans->trans('admin.host_edit', [], 'admin'));
        return $this->redirectToRoute('manageHost', ['id' => $id]);
    }

    /** @Route("/admin/hosts/{id}/delete", methods="GET", name="deleteHost") */
    public function deleteHost($id, TranslatorInterface $trans) {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }
        $host = HostUtils::getHost($id, $this->getDoctrine());
        $this->getDoctrine()->getManager()->remove($host);
        $this->getDoctrine()->getManager()->flush();
        $session->set('message', $trans->trans('admin.host_delete', [], 'admin'));
        return $this->redirectToRoute('viewHosts');
    }

    /** @Route("/admin/announcements", methods="GET", name="viewAnnonceManager") */
    public function viewAnnonceManager() {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        return $this->render('admin/announcements.html.twig', ['datas' => AdminDashboardUtils::getDatas($this->getDoctrine(), $this->requestStack->getSession()), 'appname' => AppUtils::getAppName()]);
    }

    /** @Route("/admin/hosts/search", methods="POST", name="postSearchHost") */
    public function postSearchHost(Request $request) {

        $token = $request->request->get('token');
        if(!$this->isCsrfTokenValid('authenticate_admin', $token)){
            return $this->redirectToRoute('viewAdminLogin');
        }

        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');

        $session->set('searchHosts', $this->getDoctrine()->getManager()->getRepository(Hosts::class)->search($request->request->get('search')));
        return $this->redirectToRoute('viewHosts');
    }

    /** @Route("/admin/hosts/{id}/suspend", methods="GET", name="suspendHost") */
    public function suspendHost($id, TranslatorInterface $trans) {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }

        if(HostUtils::isSuspend($id, $this->getDoctrine())){
            $session->set('message', $trans->trans('host_is_already_suspend', [], 'host'));
            return $this->redirectToRoute('manageHost', ['id' => $id]);
        }

        $host = HostUtils::getHost($id, $this->getDoctrine());
        $host->setIsSuspend(true);
        $host->setSuspendAt(new DateTimeImmutable());
        $host->setSuspendBy($session->get('adminLoginId'));

        $this->getDoctrine()->getManager()->persist($host);
        $this->getDoctrine()->getManager()->flush();

        $session->set('message', $trans->trans('host_suspend', [], 'host'));
        return $this->redirectToRoute('manageHost', ['id' => $id]);
    }
    /** @Route("/admin/hosts/{id}/unsuspend", methods="GET", name="unsuspendHost") */
    public function unsuspendHost($id, TranslatorInterface $trans) {
        $session = $this->requestStack->getSession();
        if(!$session->has('adminLoginId') || !AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())) return $this->redirectToRoute('viewAdminLogin');
        
        if(!HostUtils::exists($id, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }

        if(!HostUtils::isSuspend($id, $this->getDoctrine())){
            $session->set('message', $trans->trans('host_is_already_unsuspend', [], 'host'));
            return $this->redirectToRoute('manageHost', ['id' => $id]);
        }

        $host = HostUtils::getHost($id, $this->getDoctrine());
        $host->setIsSuspend(false);
        $host->setSuspendAt(null);
        $host->setSuspendBy(null);

        $this->getDoctrine()->getManager()->persist($host);
        $this->getDoctrine()->getManager()->flush();

        $session->set('message', $trans->trans('host_unsuspend', [], 'host'));
        return $this->redirectToRoute('manageHost', ['id' => $id]);
    }
}
