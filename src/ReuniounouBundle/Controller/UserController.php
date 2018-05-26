<?php

namespace ReuniounouBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ReuniounouBundle\Entity\Utilisateur;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller
{

  /**
   * @Route("/inscription")
   */
  public function inscriptionAction(Request $request)
  {
    $error = null;
    $user = new Utilisateur();
    $date = new \DateTime();
    $date->format('\O\n Y-m-d');
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);
    $formBuilder
        ->add('nom', TextType::class, ['label'=> false, 'attr' => ['placeholder'=> "Nom"]])
        ->add('prenom', TextType::class, ['label'=> false, 'attr' => ['placeholder'=> "Prenom"]])
        ->add('email', EmailType::class, ['label'=> false, 'attr' => ['placeholder'=> "Adresse e-mail"]])
        ->add('MotDePasseClair', RepeatedType::class, ['type' => PasswordType::class, 'first_options' => ['label'=> "Mot de passe", 'attr' => ['placeholder' => "Mot de Passe"]], 'second_options' => ['label'=> "Répetez mot de passe", 'attr' => ['placeholder' => "Vérification"]]])
        ->add('Inscription', SubmitType::class, ['attr' => ['class'=> 'btn btn-primary']] );
    $form = $formBuilder->getForm();
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid())
    {
        $user = $form->getData();
        //HASHAGE
        $passwordEncoder = $this->get('security.password_encoder');
        $motDePasse = $passwordEncoder->encodePassword($user, $user->getMotDePasseClair());
        $user->setPassword($motDePasse);
        $user->setMotDePasseClair(null);
        $manager = $this->getDoctrine()->getManager();
        $repositoryUsers = $manager->getRepository('ReuniounouBundle:Utilisateur');

        $manager->persist($user);

        try
        {
            $manager->flush();
            //return $this->redirectToRoute('connexion');
        }
        catch (PDOException $e)
        {
            $error = "UniqueConstraintViolationException";
        }
        catch (UniqueConstraintViolationException $e)
        {
            $error = "UniqueConstraintViolationException";
        }
    }

    return $this->render('@Reuniounou/User/inscription.html.twig', ['form'=> $form->createView(), 'error'=> $error]);
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
          $hashedPassword = $repositoryUsers->findByEmail($user->getEmail());
          //verification du resultat de la requete
          if ($hashedPassword != "NoResultException")
          {
              $user->setPassword($hashedPassword["password"]);
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

  /**
   * @Route("/deconnexion")
   */
  public function deconnexionAction()
  {
    return $this->render('@Reuniounou/User/deconnexion.html.twig');
  }


}
