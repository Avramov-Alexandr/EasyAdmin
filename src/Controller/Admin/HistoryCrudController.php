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
use App\Service\MeiliSearchService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;


class HistoryCrudController extends AbstractCrudController
{
    private MeiliSearchService $meiliSearchService;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(MeiliSearchService $meiliSearchService, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager) {
        $this->meiliSearchService = $meiliSearchService;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityManager = $entityManager;
    }
    public static function getEntityFqcn(): string
    {
        return History::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', "History ID"),
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

    /**
     * Создание QueryBuilder для поиска и фильтрации результатов.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $query = $searchDto->getQuery();

        if ($query) {
            // Поиск через MeiliSearch и получение id найденных записей
            $results = $this->meiliSearchService->search('History', $query);
            $ids = array_column($results, 'id');

            if (empty($ids)) {
                // Если ничего не найдено, возвращаем пустой результат
                return $this->entityManager->createQueryBuilder()
                    ->select('h')
                    ->from(History::class, 'h')
                    ->where('1 = 0'); // Гарантированно пустой результат
            }

            return $this->entityManager->createQueryBuilder()
                ->select('h')
                ->from(History::class, 'h')
                ->where('h.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'History Management')
            ->overrideTemplate('crud/index', 'admin/pages/history_index.html.twig');
    }
}
