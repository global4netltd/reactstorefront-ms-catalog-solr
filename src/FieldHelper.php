<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\Document\Field;

/**
 * Class Helper
 * @package G4NReact\MsCatalogSolr
 */
class FieldHelper
{
    const SOLR_FIELD_TYPE_STATIC = '';
    const SOLR_FIELD_TYPE_STRING = 's';
    const SOLR_FIELD_TYPE_TEXT = 't';
    const SOLR_FIELD_TYPE_INT = 'i';
    const SOLR_FIELD_TYPE_DATETIME = 'dt';
    const SOLR_FIELD_TYPE_FLOAT = 'f';
    const SOLR_FIELD_TYPE_BOOL = 'b';
    const SOLR_FIELD_TYPE_TEXT_SEARCH = 'ngram';

    const SOLR_NOT_INDEXABLE_MARK = 'ni';
    const SOLR_MULTI_VALUE_MARK = 'mv';

    const SOLR_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    const FIELD_TYPE_DEFAULT = 'string';

    /**
     * Map field types to SOLR types
     * @var array
     */
    public static $mapFieldType = [
        Field::FIELD_TYPE_STATIC => self::SOLR_FIELD_TYPE_STATIC,
        Field::FIELD_TYPE_STRING => self::SOLR_FIELD_TYPE_STRING,
        Field::FIELD_TYPE_INT => self::SOLR_FIELD_TYPE_INT,
        Field::FIELD_TYPE_TEXT => self::SOLR_FIELD_TYPE_TEXT,
        Field::FIELD_TYPE_MEDIUMTEXT => self::SOLR_FIELD_TYPE_TEXT,
        Field::FIELD_TYPE_SMALLINT => self::SOLR_FIELD_TYPE_INT,
        Field::FIELD_TYPE_VARCHAR => self::SOLR_FIELD_TYPE_STRING,
        Field::FIELD_TYPE_DATETIME => self::SOLR_FIELD_TYPE_DATETIME,
        Field::FIELD_TYPE_DECIMAL => self::SOLR_FIELD_TYPE_FLOAT,
        Field::FIELD_TYPE_FLOAT => self::SOLR_FIELD_TYPE_FLOAT,
        Field::FIELD_TYPE_DOUBLE => self::SOLR_FIELD_TYPE_FLOAT, // check if neccessary
        Field::FIELD_TYPE_BOOL => self::SOLR_FIELD_TYPE_BOOL,
        Field::FIELD_TYPE_TIMESTAMP => self::SOLR_FIELD_TYPE_DATETIME,
        Field::FIELD_TYPE_DATE => self::SOLR_FIELD_TYPE_DATETIME,
        Field::FIELD_TYPE_TEXT_SEARCH => self::SOLR_FIELD_TYPE_TEXT_SEARCH
    ];

    /**
     * @var array
     */
    public static $mapSolrFieldTypeToFieldType = [
        self::SOLR_FIELD_TYPE_TEXT => Field::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_STRING => Field::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_INT => Field::FIELD_TYPE_INT,
        self::SOLR_FIELD_TYPE_DATETIME => Field::FIELD_TYPE_DATETIME,
        self::SOLR_FIELD_TYPE_FLOAT => Field::FIELD_TYPE_FLOAT,
        self::SOLR_FIELD_TYPE_BOOL => Field::FIELD_TYPE_BOOL,
        self::SOLR_FIELD_TYPE_STATIC => Field::FIELD_TYPE_STATIC,
        self::SOLR_FIELD_TYPE_TEXT_SEARCH => Field::FIELD_TYPE_TEXT
    ];

    /**
     * @param Field $field
     *
     * @return string
     */
    public static function getFieldName($field)
    {
        $fieldMappedType = self::$mapFieldType[$field->getType()] ?? self::SOLR_FIELD_TYPE_STATIC;

        return $field->getName()
            . ($fieldMappedType ? ('_' . $fieldMappedType) : $fieldMappedType)
            . ($field->getIndexable() ? '' : ('_' . self::SOLR_NOT_INDEXABLE_MARK))
            . ($field->getMultiValued() ? ('_' . self::SOLR_MULTI_VALUE_MARK) : '');
    }

    /**
     * @param string $solrFieldName
     * @param $value
     * @param bool $rawFieldName
     *
     * @return Field
     */
    public static function createFieldByResponseField(string $solrFieldName, $value, $rawFieldName = false): Field
    {
        $type = FieldHelper::FIELD_TYPE_DEFAULT;
        $indexable = true;
        $multiValue = false;


        /** check if need raw field name */
        if (!$rawFieldName) {
            $nameParts = explode('_', $solrFieldName);

            if ($nameParts[count($nameParts) - 1] === 'mv') {
                $multiValue = true;
                unset($nameParts[count($nameParts) - 1]);
            }

            if (($nameParts[count($nameParts) - 1] === 'ni')) {
                $indexable = false;
                unset($nameParts[count($nameParts) - 1]);
            }

            if (isset(FieldHelper::$mapSolrFieldTypeToFieldType[$nameParts[count($nameParts) - 1]])) {
                $type = FieldHelper::$mapSolrFieldTypeToFieldType[$nameParts[count($nameParts) - 1]];
                unset($nameParts[count($nameParts) - 1]);
            }

            $name = implode('_', $nameParts);
        } else {
            $name = $solrFieldName;
        }

        return new Field($name, $value, $type, $indexable, $multiValue);
    }
}
