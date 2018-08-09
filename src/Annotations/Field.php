<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 8:59 PM
 */

namespace Lvinkim\ElasticSearchODM\Annotations;

/**
 * Class Field
 * @package Lvinkim\ElasticSearchODM\Annotations
 * @Annotation
 */
final class Field extends AbstractField
{
    const TYPES = [
        'string',
        'bool',
        'int',
        'float',
        'array',
        'date',
        'raw',
    ];

    const PROPERTIES = [
        'text',
        'keyword',
        'integer',
        'float',
        'date',
        'boolean',
        'nested',
        'object',
    ];

    static public $defaultPropertyTypeMap = [
        'text' => 'raw',
        'keyword' => 'string',
        'integer' => 'int',
        'float' => 'float',
        'date' => 'date',
        'boolean' => 'bool',
        'nested' => 'embedMany',
        'object' => 'embedOne',
    ];

    public $property = 'keyword';
}