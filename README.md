# elasticsearch-odm

> 支持版本  
> 6.0 以上  

### 安装
```
$ composer require lvinkim/elasticsearch-odm
```

### 方法概述

#### 索引管理方法

* 初始化索引: initialIndex()
* 更新索引别名: updateAliases($newIndexName)
* 删除索引: deleteIndex()


#### 文档管理方法

* 通过 ID 获取单个 Entity 的方法：findOneById($id)
* 通过 $query 获取单个 Entity 的方法：findOne($query)
* 通过 $query 获取多个 Entity 的方法：findMany($query, ...)
* 删除单个 Entity 的方法：deleteOne($entity)
* 插入或更新单个 Entity 的方法：upsertOne($entity)
* 插入或更新多个 Entity 的方法：upsertMany($entities)

### 使用说明:

```php

use Lvinkim\ElasticSearchODM\Annotations as ODM;

/**
 * Class User
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

```

#### 步骤2. 定义 Repository 类

```php
use Lvinkim\ElasticSearchODM\Repository;

class UserRepository extends Repository
{

    /**
     * 返回索引名, 例如: db, access-log-*, ... 等
     * @return string
     */
    protected function getIndexName(): string
    {
        return 'unit-test';
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
```

#### 步骤3. 使用示例

```php

use Lvinkim\ElasticSearchODM\DocumentManager;
use Elasticsearch\ClientBuilder;

$hosts = ['docker.for.mac.localhost:9200'];
$client = ClientBuilder::create()->setHosts($hosts)->build();

$documentManager = new DocumentManager($client);
$userRepository = $documentManager->getRepository(UserRepository::class);

// 创建索引 

$userRepository->initialIndex();

// 插入文档
$user = new User();
$user->setAge(18);
$userRepository->insertOne($user);


// 更多方法.... 参见 Functional/RepositoryTest.php 的各用例

