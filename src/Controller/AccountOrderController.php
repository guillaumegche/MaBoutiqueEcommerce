<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountOrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Affichage des commandes payées du client
     * @Route("/compte/mes-commandes", name="account_order")
     */
    public function index(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findSuccessOrders($this->getUser()); /* Requête qui récupère les commandes payées de l'utilisateur*/

        return $this->render('account/order.html.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * Affichage des données d'une commande
     * @Route("/compte/mes-commandes/{reference}", name="account_order_show")
     */
    public function show($reference): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByReference($reference); /* On récupère la requête par sa référence */

        if(!$order || $order->getUser() != $this->getUser()) { /* Si pas de commande ou mauvais utilisateur, on redirige vers l'affichage des commandes */
            return $this->redirectToRoute('account_order');
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }
}

