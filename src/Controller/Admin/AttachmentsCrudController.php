<?php

namespace App\Controller\Admin;

use App\Entity\Attachments;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class AttachmentsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Attachments::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Имя файла'),
            FormField::addPanel('Загрузка файла'),
            TextField::new('fileFile', 'Файл')
                ->setFormType(FileType::class)
                ->onlyOnForms(),
        ];
    }
}
