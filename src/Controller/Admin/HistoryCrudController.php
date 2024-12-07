<?php

namespace App\Controller\Admin;

use App\Entity\History;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class HistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return History::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('clientId', 'Client ID'),
            AssociationField::new('message', 'Message ID'),
            AssociationField::new('domain', 'Домен'),
            IntegerField::new('ordersId', 'Order ID')->hideOnIndex(),
            IntegerField::new('packageId', 'Package ID')->hideOnIndex(),
            DateTimeField::new('date', 'Date'),
            TextField::new('email', 'Email'),
        ];
    }

    public function configureActions(\EasyCorp\Bundle\EasyAdminBundle\Config\Actions $actions): \EasyCorp\Bundle\EasyAdminBundle\Config\Actions
    {
        return $actions
            ->disable(\EasyCorp\Bundle\EasyAdminBundle\Config\Action::NEW)
            ->disable(\EasyCorp\Bundle\EasyAdminBundle\Config\Action::EDIT)
            ->disable(\EasyCorp\Bundle\EasyAdminBundle\Config\Action::DELETE);
    }
}
