<?php

namespace G4NReact\MsCatalogSolr\Spellcheck;

use G4NReact\MsCatalog\Spellcheck\SpellcheckSuggestionAlternativeInterface;

/**
 * Class SpellcheckSuggestionAlternative
 * @package G4NReact\MsCatalogSolr\Spellcheck
 */
class SpellcheckSuggestionAlternative implements SpellcheckSuggestionAlternativeInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $frequency;

    /**
     * SpellcheckSuggestionAlternative constructor.
     * @param string $text
     * @param int $frequency
     */
    public function __construct(string $text, int $frequency)
    {
        $this->text = $text;
        $this->frequency = $frequency;
    }


    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }
}
