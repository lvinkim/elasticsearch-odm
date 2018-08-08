<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 10:10 PM
 */

namespace Tests\App\Entity\Embed;


use Lvinkim\ElasticSearchODM\Annotations as ODM;

class Company
{
    /**
     * 公司名称
     * @var string
     * @ODM\Field(type="string")
     */
    private $name;
    /**
     * 联系电话
     * @var string
     * @ODM\Field(type="string")
     */
    private $contact;
    /**
     * 公司地址
     * @var Address
     * @ODM\EmbedOne(target="Tests\App\Entity\Embed\Address")
     */
    private $address;

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
    public function getContact(): string
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     */
    public function setContact(string $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

}