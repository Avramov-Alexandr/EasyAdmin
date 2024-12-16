<?php

namespace App\Command;

use App\Service\MeiliSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:meilisearch:configure')]
class ConfigureMeiliSearchCommand extends Command
{
    protected static $defaultName = 'app:meilisearch:configure';

    private MeiliSearchService $meiliSearchService;

    public function __construct(MeiliSearchService $meiliSearchService)
    {
        parent::__construct();
        $this->meiliSearchService = $meiliSearchService;
    }

    protected function configure(): void
    {
        $this->setDescription('Configures searchable fields for MeiliSearch indexes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Поля для поиска в сущности History
        $this->meiliSearchService->configureIndex('History', [
            'id',
            'message',
            'domain',
            'date',
            'email',
        ]);

        $output->writeln('MeiliSearch index "History" configured successfully.');

        return Command::SUCCESS;
    }
}
