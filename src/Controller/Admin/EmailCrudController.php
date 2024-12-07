<?php

namespace App\Controller\Admin;

use App\Entity\Email;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class EmailCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Email::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $verifyEmail = Action::new('verifyEmails', 'Verify Emails')
            ->addCssClass('btn btn-primary')
            ->linkToCrudAction('verifyEmails')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $verifyEmail);
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
}
