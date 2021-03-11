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
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail()); /* On récupère l'email de l'utilisateur en bdd */

            if(!$search_email) {

                $password = $passwordEncoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);
            
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content ="Bonjour ".$user->getFirstname()."<br>Bienvenue sur la première boutique Française MaBoutiqueEcommerce<br>";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur MaBoutiqueEcommerce', $content);

                $this->addFlash('success', "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter.");

                return $this->redirectToRoute('app_login');

            } else {                
                $this->addFlash('error', 'Cet email est déjà utlisé.');
            }   
        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
