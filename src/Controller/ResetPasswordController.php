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
     * Fonction qui envoie un email à l'utilisateur s'il ne connait plus son mdp
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if($this->getUser()) { /* Si l'utilisateur est déja connecté, on le redirige vers la page home*/ 
            return $this->redirectToRoute('home');
        }

        if($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email')); /* On récupère l'email de l'utilisateur */
            
            if($user) { /* On vérifie que cet utilisateur, sous cet email, est déjà connu */
                // étape 1 : enregistrer la demande de reset_password en bdd

                $reset_password = new ResetPassword(); /* On instancie un nouveau reset mdp */
                $reset_password->setUser($user); /* On associe l'utilisateur via son email */
                $reset_password->setToken(uniqid()); /* On génère et associe un un token unique */
                $reset_password->setCreatedAt(new DateTime()); /* On associe ula date du moment */
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // étape 2 : envoyer un email à l'utilisateur permettant de changer son mdp

                $url = $this->generateUrl('update_password', [ /* On génère une url sécurisée via le token pour la réinitialisation du mdp */
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
     * Fonction qui après le mail va modifier le mdp de l'utilisateur
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request,$token, UserPasswordEncoderInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token); /* On récupère la demande via le token */

        if(!$reset_password) { /* Si pas de demande ou demande inconnue, on redirige vers la réinitialisation du mdp */
            return $this->redirectToRoute('reset_password');
        }

        // Vérification de la demande de mot de passe avec - 1 heure
        $now = new DateTime(); /* On définit une heure */
        if($now > $reset_password->getCreatedAt()->modify('+ 1 hour')) { /* Si la demande a expiré */
            
            $this->addFlash('error', 'Votre demande de mot de passe a expiré. Merci de renouveller.');
            return $this->redirectToRoute('reset_password');
        }
        
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $new_pwd = $form->get('new_password')->getData(); /* On récupère le nouveau mdp */

            $password = $encoder->encodePassword($reset_password->getUser(), $new_pwd); /* On encode le nouveau mdp */
            $reset_password->getUser()->setPassword($password); /* on associe le nouveau mdp à l'utilisateur */

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
