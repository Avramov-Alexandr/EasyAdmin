<?php
namespace App\Controller\Admin;

use App\Entity\Message;
use App\Entity\User;
use App\Service\MeiliSearchService;
use App\Entity\Domain;
use App\Form\AttachmentType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class MessageCrudController extends AbstractCrudController
{
    private MeiliSearchService $meiliSearchService;
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    public function __construct(MeiliSearchService $meiliSearchService, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->meiliSearchService = $meiliSearchService;
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;

    }
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Message Management')
            ->overrideTemplate('crud/index', 'admin/pages/message_index.html.twig');
    }

    public function search(Request $request, LoggerInterface $logger): Response
    {
        $query = $request->query->get('q', '');

        // Если поисковый запрос пустой
        if (empty($query)) {
            return $this->redirect($this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl());
        }

        $logger->info('Received search query: ' . $query);

        // Возвращаем стандартную страницу index с фильтрацией через параметр query
        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->set('query', $query) // Параметр для createIndexQueryBuilder
            ->generateUrl());
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $query = $searchDto->getQuery();

        if ($query) {
            // Поиск через MeiliSearch
            $results = $this->meiliSearchService->search('Message', $query);
            $ids = array_column($results, 'id');

            return $this->entityManager->createQueryBuilder()
                ->select('m')
                ->from(Message::class, 'm')
                ->where('m.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }
}



