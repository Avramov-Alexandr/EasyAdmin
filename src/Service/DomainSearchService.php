<?php

namespace App\Service;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface;

class DomainSearchService
{
    private FinderInterface $domainFinder;

    public function __construct(FinderInterface $domainFinder)
    {
        $this->domainFinder = $domainFinder;
    }

    public function search(string $query): array
    {
        $matchQuery = new Query\MatchQuery();
        $matchQuery->setFieldQuery('name', $query);

        return $this->domainFinder->find($matchQuery);
    }
}