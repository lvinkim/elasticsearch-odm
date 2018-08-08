<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 8:56 PM
 */

namespace Tests\App\Entity;

use Lvinkim\ElasticSearchODM\Annotations as ODM;
use MongoDB\BSON\ObjectId;

/**
 * Class User
 * @package Tests\App\Entity
 * @ODM\Entity()
 */
class User
{
    /**
     * @var string
     * @ODM\Id
     */
    private $id;

    /**
     * @var int
     * @ODM\Field(property="integer")
     */
    private $age;

    /**
     * @var float
     * @ODM\Field(property="float")
     */
    private $weight;

    /**
     * @var bool
     * @ODM\Field(property="boolean")
     */
    private $status;

    /**
     * 标签
     * @var array
     * @ODM\Field(property="keyword", type="array")
     */
    private $tags;

    /**
     * 出生日期
     * @var \DateTime
     * @ODM\Field(target="date")
     */
    private $birth;

    /**
     * 备注信息
     * @var mixed
     * @ODM\Field(property="text")
     */
    private $remark;

}