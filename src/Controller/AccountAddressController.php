<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Fonction qui affiche les adresses de la personne connectée
     * @Route("/compte/adresses", name="account_address")
     */
    public function index(): Response
    {        
        return $this->render('account/address.html.twig');
    }

    /**
     * Fonction qui permet d'ajouter une adresse
     * @Route("/compte/ajouter-adresse", name="account_address_add")
     */
    public function add(Cart $cart ,Request $request): Response
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() ) {
            $address->setUser($this->getUser());
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            if($cart->get()) { /* On vérifie si il y a quelque chose dans le panier */
                return $this->redirectToRoute('order'); /* Si oui, on redirige vers la validation de la commande */
            }else {
                return $this->redirectToRoute('account_address'); /* Si non, on redirige vers l'affichage des adresses */
            }

        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Fonction qui permet de modifier l'adresse en question
     * @Route("/compte/modifier-adresse/{id}", name="account_address_edit")
     */
    public function edit(Request $request, $id): Response
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        if(!$address || $address->getUser() != $this->getUser() ) { /* On vérifie que l'adresse existe bien ou que l'utilisateur est bien le bon */
            return $this->redirectToRoute('account_address'); /* Sinon on redirige vers l'affichage des adresses */ 
        }

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() ) {
            $this->entityManager->flush();

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Fonction qui permet de supprimer l'adresse en question
     * @Route("/compte/supprimer-adresse/{id}", name="account_address_delete")
     */
    public function delete($id): Response
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        if($address && $address->getUser() == $this->getUser() ) { /* On vérifie que l'adresse existe bien et que l'utilisateur est bien le bon */
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        }        

        return $this->redirectToRoute('account_address');
        
    }
}
