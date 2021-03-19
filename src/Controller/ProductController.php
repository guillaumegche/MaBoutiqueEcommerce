<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Affichage des produits du site
     * @Route("/nosProduits", name="products")
     */
    public function index(Request $request): Response
    {          

        $search = new Search(); /* On instancie notre recherche dans la variable $search */
        $form = $this->createForm(SearchType::class, $search); /* On crée notre formulaire à partir des données de notre recherche */

        $form->handleRequest($request); /* On écoute le formulaire, on fait une pause */

        if($form->isSubmitted() && $form->isValid() ) {
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search); /* On appelle la méthode créée dans le repository */
        }else {
            $products = $this->entityManager->getRepository(Product::class)->findAll(); /* Méthode qui récupère tous les produits */
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView() 
        ]);
    }

    /**
     * Affichage des données d'un produit
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug); /* On récupère le produit en question par son slug */
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1); /* On récupère les meilleurs produits du moment */

        if (!$product){ /* Si le produit n'existe pas, on redirige. Sécurité afin d'éviter un message d'erreur si on rentre un produit non existant */
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]);
    }
}
