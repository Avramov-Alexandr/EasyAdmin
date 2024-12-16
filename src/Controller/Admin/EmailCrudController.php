<?php

namespace App\Controller\Admin;

use App\Entity\Email;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Knp\Component\Pager\PaginatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use App\Service\MeiliSearchService;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Psr\Log\LoggerInterface;


class EmailCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private MeiliSearchService $meiliSearchService;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, MeiliSearchService $meiliSearchService)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->meiliSearchService = $meiliSearchService;
    }

    public static function getEntityFqcn(): string
    {
        return Email::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $verifyEmail = Action::new('verifyEmails', 'Verify Emails')
            ->addCssClass('btn btn-primary')
            ->linkToCrudAction('verifyEmails');
            //->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $verifyEmail);
    }

    public function index(AdminContext $context): Response
    {
        $repository = $this->entityManager->getRepository(Email::class);

        // Считаем количество email по категориям
        $noneCount = $repository->count(['emailVerifyResult' => 'None']);
        $validCount = $repository->count(['emailVerifyResult' => 'Valid']);
        $unknownCount = $repository->count(['emailVerifyResult' => 'Unknown']);
        $riskyCount = $repository->count(['emailVerifyResult' => 'Risky']);
        $invalidCount = $repository->count(['emailVerifyResult' => 'Invalid']);

        // Добавляем кастомные данные в шаблон
        $templateParameters = parent::index($context)->all();

        $templateParameters['noneCount'] = $noneCount;
        $templateParameters['validCount'] = $validCount;
        $templateParameters['unknownCount'] = $unknownCount;
        $templateParameters['riskyCount'] = $riskyCount;
        $templateParameters['invalidCount'] = $invalidCount;

        return $this->render('admin/pages/emails_index.html.twig', $templateParameters);
    }
    public function configureDashboard(): array
    {
        $repository = $this->entityManager->getRepository(Email::class);

        $noneCount = $repository->count(['emailVerifyResult' => 'None']);
        $validCount = $repository->count(['emailVerifyResult' => 'Valid']);
        $unknownCount = $repository->count(['emailVerifyResult' => 'Unknown']);
        $riskyCount = $repository->count(['emailVerifyResult' => 'Risky']);
        $invalidCount = $repository->count(['emailVerifyResult' => 'Invalid']);

        return [
            'noneCount' => $noneCount,
            'validCount' => $validCount,
            'unknownCount' => $unknownCount,
            'riskyCount' => $riskyCount,
            'invalidCount' => $invalidCount,
        ];
    }

    public function verifyEmails(Request $request, EmailVerificationService $verificationService): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        $repository = $this->entityManager->getRepository(Email::class);
        $emails = $repository->createQueryBuilder('e')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $results = [];

        foreach ($emails as $email) {
            $result = $verificationService->verifyEmail($email->getEmail());
            $email->setEmailVerifyResult($result);
            $this->entityManager->persist($email);

            $results[] = [
                'email' => $email->getEmail(),
                'result' => $result,
            ];
        }

        $this->entityManager->flush();

        $totalEmails = $repository->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $hasNextPage = ($page * $limit) < $totalEmails;

        return new JsonResponse([
            'success' => true,
            'results' => $results,
            'hasNextPage' => $hasNextPage,
            'nextPage' => $hasNextPage ? $page + 1 : null,
        ]);
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Email Management')
            ->overrideTemplate('crud/index', 'admin/pages/emails_index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('email', 'Email'),
            IntegerField::new('domainId', 'Domain ID')->hideOnIndex(),
            IntegerField::new('customersId', 'Customers ID')->hideOnIndex(),
            TextField::new('couponsId', 'Coupons ID')->hideOnIndex(),
            IntegerField::new('ordersId', 'Orders ID')->hideOnIndex(),
            IntegerField::new('packageId', 'Package ID')->hideOnIndex(),
            TextField::new('emailVerifyResult', 'Verification Result'),
        ];
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
            // Поиск через MeiliSearch и получение id найденных записей
            $results = $this->meiliSearchService->search('Email', $query);
            $ids = array_column($results, 'id');

            return $this->entityManager->createQueryBuilder()
                ->select('e')
                ->from(Email::class, 'e')
                ->where('e.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }


}
