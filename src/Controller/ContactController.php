<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {


            $mail = new Mail();
            $content = 'Message de '.$form->getFirstname()->getData().' '.$form->getLastname()->getData().' :'.'<br>'.$form->getContent()->getData();
            $mail->send('gaucheguillaume@gmail.com', 'gauche guillaume','Message reçu du formulaire de contact de MaBoutiqueEcommerce', $content);

            $this->addFlash('success', 'Votre message a bien été envoyé. Notre équipe vous répondra dans les meilleurs délais.');

            return $this->redirectToRoute('home');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
