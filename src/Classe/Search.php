<?php
namespace App\Classe;

use App\Entity\Category;

class Search
{
    /**
     * Représentation de la recherche de l'utilisateur sous forme d'un objet
     * @var string
     */
    public $string = ''; 

    /**
     * @var Category[]
     */
    public $categories = [];
}