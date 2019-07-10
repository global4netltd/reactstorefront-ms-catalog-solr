<?php

namespace G4NReact\MsCatalogSolr;

/**
 * Class Helper
 * @package G4NReact\MsCatalogSolr
 */
class Helper
{
    const SOLR_FIELD_TYPE_DEFAULT = '_s';
    const SOLR_FIELD_TYPE_STRING = '_s';
    const SOLR_FIELD_TYPE_TEXT = '_t';
    const SOLR_FIELD_TYPE_INT = '_i';
    const SOLR_FIELD_TYPE_DATETIME = '_dt';
    const SOLR_FIELD_TYPE_FLOAT = '_f';
    const SOLR_FIELD_TYPE_BOOL = '_b';

    const SOLR_NOT_INDEXABLE_MARK = '_ni';
    const SOLR_MULTI_VALUE_MARK = '_mv';

    const SOLR_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    const FIELD_TYPE_DEFAULT = 'string';

    /**
     * Map field types to SOLR types
     * @var array
     */
    public static $mapFieldType = [
        'static'   => self::SOLR_FIELD_TYPE_TEXT,
        'string'   => self::SOLR_FIELD_TYPE_STRING,
        'int'      => self::SOLR_FIELD_TYPE_INT,
        'integer'  => self::SOLR_FIELD_TYPE_INT,
        'text'     => self::SOLR_FIELD_TYPE_TEXT,
        'varchar'  => self::SOLR_FIELD_TYPE_TEXT,
        'datetime' => self::SOLR_FIELD_TYPE_DATETIME,
        'decimal'  => self::SOLR_FIELD_TYPE_FLOAT,
        'float'    => self::SOLR_FIELD_TYPE_FLOAT,
        'double'   => self::SOLR_FIELD_TYPE_FLOAT, // r u sure?
        'price'    => self::SOLR_FIELD_TYPE_FLOAT,
        'bool'     => self::SOLR_FIELD_TYPE_BOOL,
        'boolean'  => self::SOLR_FIELD_TYPE_BOOL,
    ];

    /**
     * @var array
     */
    public static $mapSolrFieldTypeToFieldType = [
        't'  => 'string',
        's'  => 'string',
        'i'  => 'int',
        'dt' => 'datetime',
        'f'  => 'float',
        'b'  => 'bool',
    ];
}
