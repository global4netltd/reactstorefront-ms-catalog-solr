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

    const SOLR_NOT_INDEXABLE_MARK = 'ni';
    const SOLR_MULTI_VALUE_MARK = 'mv';

    const SOLR_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    const FIELD_TYPE_DEFAULT = 'string';

    /**
     * Map field types to SOLR types
     * @var array
     */
    public static $mapFieldType = [
        Field::FIELD_TYPE_STATIC   => self::SOLR_FIELD_TYPE_STATIC,
        Field::FIELD_TYPE_STRING   => self::SOLR_FIELD_TYPE_STRING,
        Field::FIELD_TYPE_INT      => self::SOLR_FIELD_TYPE_INT,
        Field::FIELD_TYPE_TEXT     => self::SOLR_FIELD_TYPE_TEXT,
        Field::FIELD_TYPE_VARCHAR  => self::SOLR_FIELD_TYPE_STRING,
        Field::FIELD_TYPE_DATETIME => self::SOLR_FIELD_TYPE_DATETIME,
        Field::FIELD_TYPE_DECIMAL  => self::SOLR_FIELD_TYPE_FLOAT,
        Field::FIELD_TYPE_FLOAT    => self::SOLR_FIELD_TYPE_FLOAT,
        Field::FIELD_TYPE_DOUBLE   => self::SOLR_FIELD_TYPE_FLOAT, // check if neccessary
        Field::FIELD_TYPE_BOOL     => self::SOLR_FIELD_TYPE_BOOL,
    ];

    /**
     * @var array
     */
    public static $mapSolrFieldTypeToFieldType = [
        self::SOLR_FIELD_TYPE_TEXT     => Field::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_STRING   => Field::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_INT      => Field::FIELD_TYPE_INT,
        self::SOLR_FIELD_TYPE_DATETIME => Field::FIELD_TYPE_DATETIME,
        self::SOLR_FIELD_TYPE_FLOAT    => Field::FIELD_TYPE_FLOAT,
        self::SOLR_FIELD_TYPE_BOOL     => Field::FIELD_TYPE_BOOL,
        self::SOLR_FIELD_TYPE_STATIC   => Field::FIELD_TYPE_STATIC
    ];

    /**
     * @return array
     */
    public static $mapIndexedByFieldNameTemporary = [
        'entity_id',
        'store_id',
        'url_key',
        'parent_id',
        'path',
        'sku'
    ];

    /**
     * @param Field $field
     * @return string
     */
    public static function getFieldName($field)
    {
        $fieldMappedType = self::$mapFieldType[$field->getType()] ?? self::SOLR_FIELD_TYPE_STATIC;
        $indexable = $field->getIndexable() === false ? in_array($field->getName(), self::$mapIndexedByFieldNameTemporary) : $field->getIndexable();

        return $field->getName()
            . ($fieldMappedType ? ('_' . $fieldMappedType) : $fieldMappedType)
            . ($indexable ? '' : ('_' . self::SOLR_NOT_INDEXABLE_MARK))
            . ($field->getMultiValued() ? ('_' . self::SOLR_MULTI_VALUE_MARK) : '');
    }

    /**
     * @param string $solrFieldName
     * @param mixed $value
     * @return Field
     */
    public static function createFieldByResponseField(string $solrFieldName, $value): Field
    {
        $nameParts = explode('_', $solrFieldName);

        $type = FieldHelper::FIELD_TYPE_DEFAULT;
        $indexable = true;
        $multiValue = false;

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

        return new Field($name, $value, $type, $indexable, $multiValue);
    }
}
