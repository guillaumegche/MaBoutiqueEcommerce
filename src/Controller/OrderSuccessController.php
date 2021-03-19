<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Fonction appelée après la réussite d'un paiement
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId); /* On récupère la commande qui est sur le point d'être payée */

        if(!$order || $order->getUser() != $this->getUser()) {  /* Si pas de commande ou mauvais utilisateur, on redirige vers la page d'accueil */
            return $this->redirectToRoute('home');
        }

        if($order->getState() == 0) { /* Si le statut du panier est à 0 ,donc non payé */
            // On vide la session cart(panier)
            $cart->remove();

            // On modifie le statue isPaid de notre commande à 1, donc payé
            $order->setState(1);
            $this->entityManager->flush();

            //on envoie un mail de confirmation à notre client
            $mail = new Mail();
            $content ="Bonjour ".$order->getUser()->getFirstname()."<br>Merci de votre commande sur MaBoutiqueEcommerce<br>";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur MaboutiqueEcommerce est bien validée', $content);
        }
        

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
