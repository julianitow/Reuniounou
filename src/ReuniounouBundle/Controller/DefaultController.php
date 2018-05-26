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

        //VERIFICATION DE CONNEXION
        if ($id == null)
        {
            $error = "ConnexionNeeded";
            return $this->redirect("connexion", 308);
        }
        else
        {
            $error = null;
        }

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $evenement);
        $formBuilder
            ->add('Créer Evènement', SubmitType::class, ['attr' => ['class'=> 'btn btn-primary']] )
            ->add('Gérer Evènement(s)', SubmitType::class, ['attr' => ['class'=> 'btn btn-primary']] );
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        return $this->render('@Reuniounou/Default/index.html.twig', ['form'=> $form->createView(), 'prenom' => $prenom, 'error'=> $error]);
    }
}
