<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class NeedEmailVerificationController extends AbstractController {

    protected $requestStack;

    public function __construct(RequestStack $requestStack){
        $this->requestStack = $requestStack;
    }

    public function emailVerification(){
        if(!$this->requestStack->getSession()->has('userLoginId')) return $this->redirectToRoute('viewRegister');
        $userid = $this->requestStack->getSession()->get('userLoginId');
        
        $user = $this->getDoctrine()
                                    ->getRepository(User::class)
                                    ->find($userid);

        if(!$user) return $this->redirectToRoute('logout');
        if(is_null($user->getVerifiedAt())) return $this->redirectToRoute('viewEmail');
        return true;
    }
}