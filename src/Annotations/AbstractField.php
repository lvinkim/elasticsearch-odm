<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 15/07/2018
 * Time: 12:16 AM
 */

namespace Lvinkim\ElasticSearchODM\Annotations;


use Doctrine\Common\Annotations\Annotation;

/**
 * Class AbstractField
 * @package Lvinkim\ElasticSearchODM\Annotations
 */
class AbstractField extends Annotation
{
    public $id = false;
    public $name;
    public $type;
    public $property;
    public $target;
    public $options = [];


}