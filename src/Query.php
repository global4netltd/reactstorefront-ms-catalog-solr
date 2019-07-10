<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\AbstractQuery;
use Solarium\Core\Query\Result\ResultInterface;

/**
 * Class Query
 * @package G4NReact\MsCatalogSolr
 */
class Query extends AbstractQuery
{
    /** text, sort, filter, page size, query text */
    /**
     * @return ResultInterface
     */
    public function buildQuery()
    {
        /** @var \G4NReact\MsCatalogSolr\Client\Client $client */
        $client = $this->getClient();
        $query = $client
            ->getSelect()
            ->setFields($this->fields)
            ->addSorts($this->sort);

        return $client->query($query);
    }
}
