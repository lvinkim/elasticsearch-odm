<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 10:05 PM
 */

namespace Tests\App\Entity\Embed;


use Lvinkim\ElasticSearchODM\Annotations as ODM;

class Member
{
    /**
     * 名称
     * @var string
     * @ODM\Field(type="string")
     */
    private $name;
    /**
     * 亲属关系
     * @var string
     * @ODM\Field(type="string")
     */
    private $relation;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     */
    public function setRelation(string $relation): void
    {
        $this->relation = $relation;
    }
}