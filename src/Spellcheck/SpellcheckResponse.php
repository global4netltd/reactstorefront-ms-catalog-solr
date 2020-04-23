<?php


namespace G4NReact\MsCatalogSolr\Spellcheck;

use G4NReact\MsCatalog\Spellcheck\SpellcheckResponseInterface;
use Solarium\QueryType\Spellcheck\ResponseParser;
use Solarium\QueryType\Spellcheck\Result\Result as SpellcheckResult;

/**
 * Class SpellcheckResponse
 * @package G4NReact\MsCatalogSolr
 */
class SpellcheckResponse implements SpellcheckResponseInterface
{

    /**
     * @var SpellcheckSuggestion[]
     */
    protected $suggestions;

    /**
     * SpellCheckResponse constructor.
     * @param SpellcheckResult $spellcheckResult
     */
    public function __construct(SpellcheckResult $spellcheckResult)
    {
       $this->suggestions = $this->pareseResponse($spellcheckResult);
    }


    /**
     * @return SpellcheckSuggestion[]
     */
    public function getSpellCorrectSuggestions(): array
    {
        return $this->suggestions;
    }


    /**
     * We have to use own parser becouse default solarium parser do not provide 'origiFreq' value
     *
     * @param SpellcheckResult $spellcheckResult
     * @return SpellcheckSuggestion[]
     */
    protected function pareseResponse(SpellcheckResult $spellcheckResult): array
    {
        $result = [];
        if(!$resultObject = json_decode($spellcheckResult->getResponse()->getBody())){
            return $result;
        }
        if(!$resultObject->spellcheck || !$resultObject->spellcheck->suggestions){
            return $result;
        }
        $suggestions = array_chunk($resultObject->spellcheck->suggestions, 2);
        foreach ($suggestions as $suggestion){
            $text = $suggestion[0];
            $data = $suggestion[1];

            $alternatives = [];
            if($data->suggestion){
                foreach ($data->suggestion as $suggestionData){
                    $alternatives[] = new SpellcheckSuggestionAlternative($suggestionData->word, $suggestionData->freq);
                }
            }
            $result[] = new SpellcheckSuggestion($text, $alternatives, $data->origFreq);
        }

        return $result;
    }
}
