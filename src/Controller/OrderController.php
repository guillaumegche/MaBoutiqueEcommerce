<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     */
    public function add(Cart $cart, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = new \DateTime(); /* On instancie une nouvelle date */
            $carriers = $form->get('carriers')->getData(); /* On récupère les données du transporteur du formulaire */
            $delivery = $form->get('addresses')->getData(); /* On récupère l'adresse de livraison de la commande */

            $delivery_content = $delivery->getFirstname() . ' ' . $delivery->getLastname();
            $delivery_content .= '<br>' . $delivery->getPhone();

            if ($delivery->getCompany()) {
                $delivery_content .= '<br>' . $delivery->getCompany();
            }

            $delivery_content .= '<br>' . $delivery->getAddress();
            $delivery_content .= '<br>' . $delivery->getPostal() . ' ' . $delivery->getCity();
            $delivery_content .= '<br>' . $delivery->getCountry();

            // Enregistrer ma commande Order()
            $order = new Order(); /* On instancie une nouvelle commande */
            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser()); /* On lui affecte l'utilisateur courrant */
            $order->setCreatedAt($date); /* On lui affecte la date $date */
            $order->setCarrierName($carriers->getName()); /* On lui affecte le nom du transporteur */
            $order->setCarrierPrice($carriers->getPrice()); /* On lui affecte le prix du transporteur */
            $order->setDelivery($delivery_content); /* On lui affecte des données de livraison appartenant à $delivery */
            $order->setState(0); /* On définit que la commande n'est pas payée au départ */

            $this->entityManager->persist($order);


            // Enregistrer mes produits OrderDetails()
            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails(); /* On instancie une nouvelle commandeDetails */
                $orderDetails->setMyOrder($order); /* On affecte la commande en question */
                $orderDetails->setProduct($product['product']->getName()); /* On affecte le nom du produit */
                $orderDetails->setQuantity($product['quantity']); /* On affecte la quantité du produit */
                $orderDetails->setPrice($product['product']->getPrice()); /* On affecte le prix du produit */
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']); /* On affecte le total en multipliant la quantité par le prix */
                $this->entityManager->persist($orderDetails);
            }
            
            $this->entityManager->flush();


            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
                'reference' => $order->getReference()
            ]);
        }

        return $this->redirectToRoute('cart');
        
    }
}
