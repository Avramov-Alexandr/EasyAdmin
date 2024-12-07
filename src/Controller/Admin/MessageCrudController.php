<?php
namespace App\Controller\Admin;

use App\Entity\Message;
use App\Entity\Domain;
use App\Form\AttachmentType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('subject', 'Тема'),
            TextField::new('body', 'Сообщение'),
            AssociationField::new('domain', 'Домен')->setRequired(true),
            BooleanField::new('active', 'Активно')->setRequired(true),
            CollectionField::new('attachments', 'Вложения')
                ->setEntryType(AttachmentType::class)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setTemplatePath('admin/fields/attachments.html.twig'),
        ];
    }
}



