<?php


namespace G4NReact\MsCatalogSolr\Spellcheck;

use Solarium\Client;

/**
 * Class SpellcheckResponse
 * @package G4NReact\MsCatalogSolr
 */
class SpellcheckRequest
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var $array
     */
    protected $options;


    /**
     * SpellcheckRequest constructor.
     * @param Client $client
     * @param array $options
     */
    public function __construct(Client $client, array $options = [])
    {
        $this->client = $client;
        $this->options = $options;
    }

    /**
     * @param string $text
     * @return SpellcheckResponse
     */
   public function execute(string $text): SpellcheckResponse
   {
       $query = $this->client->createSpellcheck();
       $query->setQuery($text);
       $query->setCount($this->getOption('count', 5));
       $query->setBuild($this->getOption('build', true));
       $query->setCollate($this->getOption('collate', true));
       $query->setExtendedResults($this->getOption('extended_results', true));
       $query->setCollateExtendedResults($this->getOption('collate_extended_results', true));
       $query->setOnlyMorePopular($this->getOption('only_more_popular', true));
       return new SpellcheckResponse($this->client->spellcheck($query));
   }

    /**
     * @param string $option
     * @param mixed $defaultValue
     * @return mixed
     */
   protected function getOption(string $option, $defaultValue)
   {
       return $this->options[$option] ?? $defaultValue;
   }
}
