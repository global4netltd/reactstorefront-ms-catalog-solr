<?php

namespace G4NReact\MsCatalogSolr;

use G4NReact\MsCatalog\Document\AbstractField;
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
        AbstractField::FIELD_TYPE_STATIC   => self::SOLR_FIELD_TYPE_STATIC,
        AbstractField::FIELD_TYPE_STRING   => self::SOLR_FIELD_TYPE_STRING,
        AbstractField::FIELD_TYPE_INT      => self::SOLR_FIELD_TYPE_INT,
        AbstractField::FIELD_TYPE_TEXT     => self::SOLR_FIELD_TYPE_TEXT,
        AbstractField::FIELD_TYPE_VARCHAR  => self::SOLR_FIELD_TYPE_STRING,
        AbstractField::FIELD_TYPE_DATETIME => self::SOLR_FIELD_TYPE_DATETIME,
        AbstractField::FIELD_TYPE_DECIMAL  => self::SOLR_FIELD_TYPE_FLOAT,
        AbstractField::FIELD_TYPE_FLOAT    => self::SOLR_FIELD_TYPE_FLOAT,
        AbstractField::FIELD_TYPE_DOUBLE   => self::SOLR_FIELD_TYPE_FLOAT, // check if neccessary
        AbstractField::FIELD_TYPE_BOOL     => self::SOLR_FIELD_TYPE_BOOL,
    ];

    /**
     * @var array
     */
    public static $mapSolrFieldTypeToFieldType = [
        self::SOLR_FIELD_TYPE_TEXT     => AbstractField::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_STRING   => AbstractField::FIELD_TYPE_STRING,
        self::SOLR_FIELD_TYPE_INT      => AbstractField::FIELD_TYPE_INT,
        self::SOLR_FIELD_TYPE_DATETIME => AbstractField::FIELD_TYPE_DATETIME,
        self::SOLR_FIELD_TYPE_FLOAT    => AbstractField::FIELD_TYPE_FLOAT,
        self::SOLR_FIELD_TYPE_BOOL     => AbstractField::FIELD_TYPE_BOOL,
        self::SOLR_FIELD_TYPE_STATIC   => AbstractField::FIELD_TYPE_STATIC
    ];

    /**
     * @param Field $field
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
}
