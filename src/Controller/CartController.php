<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Fonction qui appelle la méthode qui affiche le panier
     * @Route("/mon-panier", name="cart")
     */
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getFull()
        ]);
    }

    /**
     * Fonction qui appele la méthode qui ajoute un produit au panier
     * @Route("/cart/add/{id}", name="add_to_cart")
     */
    public function add(Cart $cart,$id): Response
    {
        $cart->add($id);
        return $this->redirectToRoute('cart');
    }

    /**
     * Fonction qui appelle la méthode qui supprime le panier
     * @Route("/cart/remove", name="remove_my_cart")
     */
    public function remove(Cart $cart): Response
    {
        $cart->remove();
        return $this->redirectToRoute('products');
    }

    /**
     * Fonction qui appelle la méthode qui supprime juste le produit du panier
     * @Route("/cart/delete/{id}", name="delete_to_cart")
     */
    public function delete(Cart $cart, $id): Response
    {
        $cart->delete($id);
        return $this->redirectToRoute('cart');
    }

    /**
     * Fonction qui appelle la méthode qui va décrémenter la quatité du produit dans le panier
     * @Route("/cart/decrease/{id}", name="decrease_to_cart")
     */
    public function decrease(Cart $cart, $id): Response
    {
        $cart->decrease($id);
        return $this->redirectToRoute('cart');
    }
}
