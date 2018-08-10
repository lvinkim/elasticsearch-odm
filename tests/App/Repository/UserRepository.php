<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 23/07/2018
 * Time: 11:08 PM
 */

namespace Tests\App\Repository;

use Lvinkim\ElasticSearchODM\Repository;
use Tests\App\Entity\User;

class UserRepository extends Repository
{
    private $extendIndexName;

    /**
     * @param mixed $extendIndexName
     */
    public function setExtendIndexName($extendIndexName): void
    {
        $this->extendIndexName = $extendIndexName;
    }

    /**
     * 返回索引名, 例如: db, access-log-*, ... 等
     * @return string
     */
    protected function getIndexName(): string
    {
        return $this->extendIndexName ?: 'unit-test';
    }

    /**
     * 返回文档 type , 例如: user, product, ... 等
     * @return string
     */
    protected function getTypeName(): string
    {
        return 'user';
    }

    /**
     * 返回数据表的对应实体类名
     * @return string
     */
    protected function getEntityClassName(): string
    {
        return User::class;
    }
}