<?php

namespace App\Command;

use App\Service\MeiliSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:meilisearch:populate')]
class PopulateMeiliSearchCommand extends Command
{
    // Имя команды
    protected static $defaultName = 'app:meilisearch:populate';

    private EntityManagerInterface $em;
    private MeiliSearchService $meiliSearchService;

    public function __construct(EntityManagerInterface $em, MeiliSearchService $meiliSearchService)
    {
        parent::__construct();
        $this->em = $em;
        $this->meiliSearchService = $meiliSearchService;
    }

    // Описание команды
    protected function configure(): void
    {
        $this
            ->setDescription('Populates MeiliSearch indexes with data from the database.')
            ->setHelp('This command allows you to populate MeiliSearch indexes with data from your Doctrine entities.')
            ->addOption(
                'entities',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Specify entities to index (default: all)',
                []
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entities = [
            'Domain' => 'App\Entity\Domain',
            'User' => 'App\Entity\User',
            'Email' => 'App\Entity\Email',
            'Message' => 'App\Entity\Message',
            'History' => 'App\Entity\History',
        ];

        $selectedEntities = $input->getOption('entities');
        if (!empty($selectedEntities)) {
            // Отфильтровать только указанные сущности
            $entities = array_filter($entities, function ($key) use ($selectedEntities) {
                return in_array($key, $selectedEntities, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        foreach ($entities as $indexName => $entityClass) {
            try {
                $repository = $this->em->getRepository($entityClass);
                $data = $repository->findAll();

                // Обрабатываем индексацию через сервис
                if ($indexName === 'History') {
                    $this->meiliSearchService->addAllDocumentsForHistory($data);
                }
                elseif ($indexName === 'Email') {
                    $this->meiliSearchService->addAllDocumentsForEmail($data);
                }
                elseif ($indexName === 'User') {
                    $this->meiliSearchService->addAllDocumentsForUser($data);
                }
                elseif ($indexName === 'Message') {
                    $this->meiliSearchService->addAllDocumentsForMessage($data);
                }
                elseif ($indexName === 'Domain') {
                    $this->meiliSearchService->addAllDocumentsForDomain($data);
                }
                else {
                    // Обрабатываем остальные сущности
                    $documents = array_map(function ($item) {
                        return [
                            'id' => $item->getId(),
                            'name' => method_exists($item, 'getName') ? $item->getName() : '',
                            'description' => method_exists($item, 'getDescription') ? $item->getDescription() : '',
                        ];
                    }, $data);

                    $this->meiliSearchService->addDocuments($indexName, $documents);
                }
                $output->writeln("Successfully added documents for index: $indexName");
            } catch (\Exception $e) {
                $output->writeln("<error>Error processing index $indexName: {$e->getMessage()}</error>");
            }
        }

        return Command::SUCCESS;
    }
}
