<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * Modification du mot de passe en connaissant l'ancien
     * @Route("/compte/password", name="account_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser(); /* On récupère l'utilisateur connecté */
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $old_pwd = $form->get('old_password')->getData(); /* On récupère l'ancien mot de passe dans le formulaire */

            if($passwordEncoder->isPasswordValid($user, $old_pwd)){ /* Si l'ancien mot de passe est bon */
                $new_pwd = $form->get('new_password')->getData(); /* On récupère le nouveau mot de passe dans le formulaire */
                $password = $passwordEncoder->encodePassword($user, $new_pwd); /* On encode le nouveau mdp */

                $user->setPassword($password); /* On associe le mdp à l'utilisateur */
                $this->entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                
                return $this->redirectToRoute('account');
            }
            else{
                $this->addFlash('error', "Votre mot de passe actuel n'est pas le bon");
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
