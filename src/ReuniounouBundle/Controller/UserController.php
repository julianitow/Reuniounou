<?php

namespace ReuniounouBundle\Controller;

use ReuniounouBundle\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

  /**
   * @Route("/inscription")
   */
  public function inscriptionAction(Request $request)
  {

    return $this->render('@Reuniounou/User/inscription.html.twig');
  }

  /**
   * @Route("/connexion")
   */
  public function connexionAction(Request $request)
  {
      $error = null; // pour éviter le "undefined variable error"
      $user = new Utilisateur(); //création d' un objet utilisater vide
      $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user); // Initisalisation du form builder
      //CREATION DU FORMULAIRE
      $formBuilder
          ->add('email', EmailType::class, ['label'=> false, 'attr' => ['placeholder' => "Adresse e-mail"]])
          ->add('motDePasseClair', PasswordType::class, ['label'=> false, 'attr' => ['placeholder' => "Mot de Passe"]])
          ->add('Se connecter', SubmitType::class, ['attr' => ['class'=> 'btn btn-primary']]);
      $form = $formBuilder->getForm();
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
          $user = $form->getData();
          $motDePasseSaisie = $user->getMotDePasseClair();
          $manager = $this->getDoctrine()->getManager();
          $repositoryUsers = $manager->getRepository('ReuniounouBundle:Utilisateur');
          //VERIFICATION HASH PASSWORD
          $passwordEncoder = $this->container->get('security.password_encoder');
          //Récupération du mit de passe crypté
          $hashedPassword = $repositoryUsers->findByEmail($user->getEmail()/*, $user->getMotDePasseClair()*/);
          //verification du resultat de la requete
          if ($hashedPassword != "NoResultException")
          {
              $user->setMotDePasse($hashedPassword["motDePasse"]);
          }
          else
          {
              $error = "NoResultException";
          }
          //Verification du mot de passe récupéré avec celui saisie
          if ($passwordEncoder->isPasswordValid($user, $motDePasseSaisie))
          {
              $user = $repositoryUsers->findOneByEmail($user->getEmail());
              $error = "NoError";
              //passage de l'utilisateur dans une session
              $session = new session();
              $session->set('id', $user->getId());
              $session->set('prenom', $user->getPrenom());
              $session->set('nom', $user->getNom());
              $session->set('email', $user->getEmail());
              $session->set('utilisateur', $user);
              return $this->redirectToRoute('application_homepage');
          }
          else
          {
              $error = "NoResultException";
          }
      }
      return $this->render('@Reuniounou/User/connexion.html.twig', ['form'=> $form->createView(), 'utilisateur' => $user, 'error' => $error]);
  }
}
