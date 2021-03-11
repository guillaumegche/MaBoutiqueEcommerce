<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du produit'),
            SlugField::new('slug', "Nom d'url")->setTargetFieldName('name'),
            ImageField::new('illustration', 'Image')
                ->setBasePath('uploads/productPicture/')
                ->setUploadDir('public/uploads/productPicture/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ,
            TextField::new('subtitle', 'Sous-titre du produit'),
            TextareaField::new('description', 'Description'),
            BooleanField::new('isBest', 'Produits homePage'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            AssociationField::new('category', 'Catégorie')
        ];
    }
    
}
