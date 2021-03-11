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
     * @Route("/nosProduits", name="products")
     */
    public function index(Request $request): Response
    {          

        $search = new Search(); /* on instancie notre recherche dans la variable $search */
        $form = $this->createForm(SearchType::class, $search); /* on crée notre formulaire à partir des données de notre recherche */

        $form->handleRequest($request); /* on écoute le formulaire, on fait une pause */

        if($form->isSubmitted() && $form->isValid() ) {
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search); /* on appelle la méthode créée dans le repository */
        }else {
            $products = $this->entityManager->getRepository(Product::class)->findAll(); /* méthode qui récupère tous les produits */
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView() 
        ]);
    }

    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug); 
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);    

        if (!$product){
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]);
    }
}
