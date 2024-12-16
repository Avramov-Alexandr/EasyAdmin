<?php

namespace App\Service;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface;
//use FOS\ElasticaBundle\Repository;

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
        $matchQuery->setField('name', $query);

        return $this->domainFinder->find($matchQuery);
    }
}