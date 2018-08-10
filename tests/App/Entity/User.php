<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 8:56 PM
 */

namespace Tests\App\Entity;

use Lvinkim\ElasticSearchODM\Annotations as ODM;
use Tests\App\Entity\Embed\Company;
use Tests\App\Entity\Embed\Member;

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
     * @ODM\Field(property="date")
     */
    private $birth;

    /**
     * 所在公司
     * @var Company
     * @ODM\EmbedOne(target="Tests\App\Entity\Embed\Company", options={
     *     "properties"={
     *          "address"={
     *              "properties"={
     *                  "country"={
     *                      "type"="keyword"
     *                  },
     *                  "city"={
     *                      "type"="keyword"
     *                  }
     *              }
     *          },
     *          "contact"={
     *              "type"="keyword"
     *          },
     *          "name"={
     *              "type"="keyword"
     *          }
     *     }
     * })
     */
    private $company;

    /**
     * 家庭成员
     * @var Member[]
     * @ODM\EmbedMany(target="Tests\App\Entity\Embed\Member", options={
     *   "properties"= {
     *       "name"= {
     *           "type"= "keyword"
     *       },
     *       "relation"= {
     *           "type"= "keyword"
     *       }
     *   }
     * })
     */
    private $families;

    /**
     * 备注信息
     * @var mixed
     * @ODM\Field(property="keyword")
     */
    private $remark;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age): void
    {
        $this->age = $age;
    }


}