<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 9:51 PM
 */

namespace Lvinkim\ElasticSearchODM\Annotations;

/**
 * Class EmbedOne
 * @package Lvinkim\ElasticSearchODM\Annotations
 * @Annotation
 */
final class EmbedOne extends AbstractField
{
    public $type = 'embedOne';
    public $property = 'object';
}