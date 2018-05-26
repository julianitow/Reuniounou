<?php

namespace ReuniounouBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use ReuniounouBundle\Entity\Evenement;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="application_homepage")
     */
    public function indexAction(Request $request)
    {
        $evenement = new Evenement;
        $session = $request->getSession();
        $id = $session->get('id');
        $prenom = $session->get('prenom');
        $error = null;

        //VERIFICATION DE CONNEXION
        return $this->render('@Reuniounou/Default/index.html.twig', ['prenom' => $prenom, 'error'=> $error]);
    }
}
