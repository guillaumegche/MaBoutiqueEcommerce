<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Requête qui permet de récupérer les produits en fonction de la recherche de l'utilisateur
     * Fonction appellée dans la fonction Index du ProductController
     * @return Product[]
    */
    public function findWithSearch(Search $search)
    {
        $query = $this
            ->createQueryBuilder('p') /* création de la requete à partir de l'entity Product */
            ->select('c', 'p') /* on sélection la Catégorie et le Produit */
            ->join('p.category', 'c'); /* on joint les catégories du Produit à la Catégorie */

        /* partie de la requete filtre sur les checkbox */
        if(!empty($search->categories)) { /* si la requete n'est pas vide */
            $query = $query 
                ->andWhere('c.id IN (:categories)') /* les id des catégories soient dans la liste envoyée en paramètre*/
                ->setParameter('categories', $search->categories); /* on donne une valeur au paramètre categories */
        }

        /* partie de la requete filtre sur l'input */
        if(!empty($search->string)) { /* si la requete n'est pas vide */
            $query = $query
                ->andWhere('p.name LIKE :string') /* le nom du produit doit correspondre à la donnée du paramètre */
                ->setParameter('string', "%{$search->string}%"); /* on donne une valeur au paramètre */
        }


        return $query->getQuery()->getResult(); 
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
