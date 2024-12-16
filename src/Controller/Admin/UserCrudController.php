<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\MeiliSearchService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class UserCrudController extends AbstractCrudController
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
        return User::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $query = $searchDto->getQuery();

        if ($query) {
            // Поиск через MeiliSearch
            $results = $this->meiliSearchService->search('User', $query);
            $ids = array_column($results, 'id');

            return $this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('u.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'User Management')
            ->overrideTemplate('crud/index', 'admin/pages/user_index.html.twig');
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

}
