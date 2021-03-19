<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    
    /**
     * Fonction afin de créer un compte, un utilisateur
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User(); /* On instancie un nouvel utilisateur */
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData(); /* On récupère les données dans le formulaire */

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail()); /* On récupère l'email de l'utilisateur en bdd */

            if(!$search_email) { /* Si l'adresse email est libre, on continue */

                $password = $passwordEncoder->encodePassword($user, $user->getPassword()); /* On encode le mdp de l'utilisateur connecté */

                $user->setPassword($password); /* On associe le mdp encodé à l'utilisateur */
            
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content ="Bonjour ".$user->getFirstname()."<br>Bienvenue sur la première boutique Française MaBoutiqueEcommerce<br>";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur MaBoutiqueEcommerce', $content);

                $this->addFlash('success', "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter.");

                return $this->redirectToRoute('app_login');

            } else {                
                $this->addFlash('error', 'Cet email est déjà utilisé.');
            }   
        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
