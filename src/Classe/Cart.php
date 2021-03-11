<?php
namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager ,SessionInterface $session) /* On initialise la class avec le SessionInterface pour ne plus à avoir à l'appeler */
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function add($id) /* Fonction d'ajout d'un produit au panier */
    {
        $cart = $this->session->get('cart', []); /* On récupère le panier de la session */

        if(!empty($cart[$id])) { /* Si c'est différent de vide et que le produit est déjà dans le pânier, on incrémente de 1 */
            $cart[$id]++;
        }else {
            $cart[$id] = 1; /* Sinon, on ajoute le produit avec 1 comme quantité */
        }

        $this->session->set('cart', $cart);
    }

    public function get() /* Fonction qui récupère, qui le retourne le panier de la session */
    {
        return $this->session->get('cart');
    }

    public function remove() /* Fonction qui supprime le panier de la session = remise à zéro */
    {
        return $this->session->remove('cart');
    }

    public function delete($id) /* Fonction qui supprime le produit du panier */
    {
        $cart = $this->session->get('cart', []); /* On récupère le panier de la session */
        unset($cart[$id]); /* On supprime le produit via son id de l'entrée du tableau */

        return $this->session->set('cart', $cart); /* On retourne le nouveau panier mis à jour */
    }

    public function decrease($id) /* Fonction qui désincrémente le produit du panier */
    {
        $cart = $this->session->get('cart', []); /* On récupère le panier de la session */

        if($cart[$id] > 1) { /* Si la quantité du produit est supérieur à 1 */
            $cart[$id]--; /* On désincrémente */
        }else { /* Sinon (donc < 1 ) */
            unset($cart[$id]); /* On supprimer la ligne du tableau */
        }

        return $this->session->set('cart', $cart); /* On retourne le nouveau panier mis à jour */
    }

    public function getFull()
    {
        $cartComplete = [];

        if($this->get()) { /* Si on a un panier, on l'affiche */ /* Ca évite d'avoir une erreur qui essaye d'afficher un panier vide */
            foreach($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

                if(!$product_object) {  /* Méthode qui sert à éviter l'injection de donnée */
                    $this->delete($id); /* exemple : si quelqu'un rentre dans l'url cart/add/777777, cette action sera refusée */
                    continue; /* On sort de la boucle et on passe au produit suivant */
                }

                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartComplete;
    }
}