<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 10:19 PM
 */

namespace Tests\App\Entity\Embed;


use Lvinkim\ElasticSearchODM\Annotations as ODM;

class Address
{
    /**
     * 国家
     * @var string
     * @ODM\Field(type="string")
     */
    private $country;
    /**
     * 城市
     * @var string
     * @ODM\Field(type="string")
     */
    private $city;

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }
}