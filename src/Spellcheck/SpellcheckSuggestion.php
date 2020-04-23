<?php

namespace G4NReact\MsCatalogSolr\Spellcheck;

use G4NReact\MsCatalog\Spellcheck\SpellcheckSuggestionInterface;

/**
 * Class SpellcheckSuggestion
 * @package G4NReact\MsCatalogSolr\Spellcheck
 */
class SpellcheckSuggestion implements SpellcheckSuggestionInterface
{

    /**
     * @var string
     */
    protected $text;

    /**
     * @var SpellcheckSuggestionAlternativeInterface[]
     */
    protected $alternatives;

    /**
     * @var int
     */
    protected $origFreq;

    /**
     * SpellcheckSuggestion constructor.
     * @param string $text
     * @param array $alternatives
     * @param int $origFreq
     */
    public function __construct(string $text, array $alternatives, int $origFreq)
    {
        $this->text = $text;
        $this->alternatives = $alternatives;
        $this->origFreq = $origFreq;
    }


    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return SpellcheckSuggestionAlternative[]
     */
    public function getAlternatives(): array
    {
        return $this->alternatives;
    }

    /**
     * @return SpellcheckSuggestionAlternative[]
     */
    public function getSortedAlternatives(): array
    {
        $alternatives = $this->alternatives;
        usort($alternatives, function(SpellcheckSuggestionAlternative $a, SpellcheckSuggestionAlternative $b){
            return $b->getFrequency() - $a->getFrequency();
        });

        return $alternatives;
    }

    /**
     * @return int
     */
    public function getNumberOfAlternatives(): int
    {
        return count($this->alternatives);
    }

    /**
     * @return int
     */
    public function getOriginalFrequency(): int
    {
        return $this->origFreq;
    }
}
