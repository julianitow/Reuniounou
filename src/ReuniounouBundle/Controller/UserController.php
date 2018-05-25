<?php

namespace ReuniounouBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{

  /**
   * @Route("/inscription")
   */
  public function inscriptionAction()
  {
    return $this->render('@Reuniounou/User/inscription.html.twig');
  }

  /**
   * @Route("/connexion")
   */
  public function connexionAction()
  {
    return $this->render('@Reuniounou/User/connexion.html.twig');
  }
}
