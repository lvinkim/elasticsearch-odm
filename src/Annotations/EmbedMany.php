<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 10:06 PM
 */

namespace Lvinkim\ElasticSearchODM\Annotations;

/**
 * Class EmbedMany
 * @package Lvinkim\ElasticSearchODM\Annotations
 * @Annotation
 */
final class EmbedMany extends AbstractField
{
    public $type = 'embedMany';
    public $property = 'nested';
}