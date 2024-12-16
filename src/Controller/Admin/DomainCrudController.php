<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use App\Service\DomainSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Psr\Log\LoggerInterface;

class DomainCrudController extends AbstractCrudController
{
    private DomainSearchService $domainSearchService;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(DomainSearchService $domainSearchService, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager)
    {
        $this->domainSearchService = $domainSearchService;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Domain::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;

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
            //Crud::FIELD_ACTIONS,
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'ASC'])
            ->setPageTitle('index', 'Domain Management')
            ->overrideTemplate('crud/index', 'admin/pages/domain_index.html.twig');
    }

    public function index(AdminContext $context): Response
    {
        $domains = $this->entityManager->getRepository(Domain::class)->findAll();
        $templateParameters = parent::index($context)->all();
        $templateParameters['domains'] = $domains;

        return $this->render('admin/pages/domain_index.html.twig', $templateParameters);
    }
    public function createIndexQueryBuilder(SearchDto $searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $query = $searchDto->getQuery();

        if ($query) {
            return $this->entityManager->createQueryBuilder()
                ->select('entity')
                ->from(Domain::class, 'entity')
                ->where('entity.name LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
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

        // Выполняем поиск через DomainSearchService
        $results = $this->domainSearchService->search($query);

        $logger->info('Search returned ' . count($results) . ' result(s).');

        // Передаем результаты через контекст EasyAdmin
        $this->addFlash('success', count($results) . ' result(s) found for "' . $query . '"');

        // Возвращаем стандартную страницу index с фильтрацией
        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->set('query', $query)
            ->generateUrl());
    }




}