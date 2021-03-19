<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Fonction appelée lors d'un échec ou d'un abandon de paiement
     * @Route("/commande/erreur/{stripeSessionId}", name="order_cancel")
     */
    public function index($stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId); /* On récupère la commande qui est sur le point d'être payée */

        if(!$order || $order->getUser() != $this->getUser()) { /* Si pas de commande ou mauvais utilisateur, on redirige vers la page d'accueil */
            return $this->redirectToRoute('home');
        }

        // Envoyer un message à l'utlisateur pour l'informer de l'échec du paiement
        $this->addFlash('error', 'Votre paiement a échoué, veuillez recommencer ou vérifier auprès de votre banque.');

        
        return $this->render('order_cancel/index.html.twig', [
            'order' => $order
        ]);
    }
}
