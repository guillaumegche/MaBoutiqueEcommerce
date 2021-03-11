<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if($this->getUser()) { /* Si l'utilisateur est déja connecté, on le redirige vers la page home*/ 
            return $this->redirectToRoute('home');
        }

        if($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            
            if($user) {
                // étape 1 : enregistrer la demande de reset_password en bdd

                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // étape 2 : envoyer un email à l'utilisateur permettant de changer son mdp

                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname()."<br>Vous avez demandé à réinitialiser votre mot de passe sur le site MaBoutiqueEcommerce.<br>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>";
                $mail = new Mail();
                
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), "Réinitialiser votre mot de passe sur MaBoutiqueEcommerce.", $content);

                $this->addFlash('success', 'Vous allez recevoir dans quelques secondes un email avec la procédure de réinitialisation de votre mot de passe.');

            } else {
                $this->addFlash('error', 'Cette adresse email est inconnue.');
            }
        }
        

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request,$token, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        // Vérification de la demande de mot de passe avec - 1 heure
        $now = new DateTime();
        if($now > $reset_password->getCreatedAt()->modify('+ 1 hour')) { /* Si la demande a expiré */
            
            $this->addFlash('error', 'Votre demande de mot de passe a expiré. Merci de renouveller.');
            return $this->redirectToRoute('reset_password');
        }
        
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $new_pwd = $form->get('new_password')->getData();

            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
