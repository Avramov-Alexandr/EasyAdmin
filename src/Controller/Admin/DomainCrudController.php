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
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;



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
        $searchAction = Action::new('search', 'Search')
            ->linkToUrl(function () {
                return $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('search')
                    ->generateUrl();
            })
            ->setCssClass('btn btn-primary');

        return $actions->add(Crud::PAGE_INDEX, $searchAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('clientId', 'Client ID')->hideOnIndex(),
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
        dump('Using custom createIndexQueryBuilder');
        $query = $searchDto->getQuery();

        if ($query) {
            dump('Search query:', $query);

            $results = $this->domainSearchService->search($query);
            $ids = array_map(fn($result) => $result['id'], $results);

            dump('Elastica Results IDs:', $ids);

            if (empty($ids)) {
                return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            }

            return $this->entityManager->createQueryBuilder()
                ->select('entity')
                ->from(Domain::class, 'entity')
                ->where('entity.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

}