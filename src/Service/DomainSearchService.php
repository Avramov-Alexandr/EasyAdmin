<?php

namespace App\Service;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface;
use Psr\Log\LoggerInterface;

class DomainSearchService
{
    private FinderInterface $domainFinder;
    private LoggerInterface $logger;
    public function __construct(FinderInterface $domainFinder, LoggerInterface $logger)
    {
        $this->domainFinder = $domainFinder;
        $this->logger = $logger;
    }

    public function search(string $query): array
    {
        $this->logger->info('Elastica search query: ' . $query);

        // MultiMatchQuery для поиска по нескольким полям
        $multiMatchQuery = new Query\MultiMatch();
        $multiMatchQuery->setQuery($query);
        $multiMatchQuery->setFields([
            'name',
            'smtpHost',
            'smtpUser',
            'smtpPass',
            'fromEmail',
            'fromName',
            'fromHost'
        ]);

        $results = $this->domainFinder->find($multiMatchQuery);

        return array_map(fn($result) => [
            'id' => $result->getId(),
            'name' => $result->getName(),
        ], $results);
    }
}