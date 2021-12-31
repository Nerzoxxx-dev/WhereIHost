<?php

namespace App\Controller;

use App\Utils\AdminUtils;
use App\Utils\HostUtils;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HostController extends AbstractController {

    protected $requestStack;

    public function __construct(RequestStack $requestStack){
        $this->requestStack = $requestStack;
    }

    /** @Route("/hosts/{hostId}/verification/{imageId}", methods="GET", name="verificationImage") */
    public function verificationImage($hostId, $imageId, TranslatorInterface $trans){
        $session = $this->requestStack->getSession();
        if(!$session->has('userLoginId') && !$session->has('adminLoginId')){
            return $this->redirectToRoute('viewLogin');
        }
        if(!HostUtils::canAccessToHost($hostId, $session->get('userLoginId'), $this->getDoctrine(), $session->get('adminLoginId'))){
            return $this->redirectToRoute('viewLogin');
        }
        
        if(!HostUtils::exists($hostId, $this->getDoctrine())) {
            $session->set('message', $trans->trans('admin.host_not_exists', [], 'admin'));
            return $this->redirectToRoute('viewHosts');
        }

        $host = HostUtils::getHost($hostId, $this->getDoctrine());
        if(!array_key_exists($imageId, $host->getVerificationProofs()) && $session->has('adminLoginId') && AdminUtils::adminExists($session->get('adminLoginId'), $this->getDoctrine())){
            $session->set('message', $trans->trans('document_not_exists', [], 'base'));
            return $this->redirectToRoute('viewHosts');
        }

        $verificationProof = $host->getVerificationProofs()[$imageId];
        $path = '../public/img/hosts/verifications/' . $verificationProof;
        $encode = Image::make($path)->encode();
        $response = new Response($encode);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $verificationProof
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $encode->response();
    }
}