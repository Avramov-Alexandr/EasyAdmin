<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class DomainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Domain::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('clientId', 'Client ID'),
            TextField::new('name', 'Domain name'),
            TextField::new('smtpHost', 'SMTP Host'),
            TextField::new('smtpUser', 'SMTP User'),
            TextField::new('smtpPass', 'SMTP Password')->hideOnIndex(),
            IntegerField::new('smtpPort', 'SMTP Port'),
            BooleanField::new('useAuth', 'Use Authentication'),
            TextField::new('fromEmail', 'From Email'),
            TextField::new('fromName', 'From Name'),
            TextField::new('fromHost', 'From Host'),
        ];
    }
}
