<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 9:43 PM
 */

namespace Lvinkim\ElasticSearchODM\Annotations;

/**
 * Class Id
 * @package Lvinkim\ElasticSearchODM\Annotations
 * @Annotation
 */
final class Id extends AbstractField
{
    public $id = true;
    public $type = 'string';
    public $property = 'keyword';
}