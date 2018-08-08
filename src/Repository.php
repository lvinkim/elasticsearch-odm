<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 14/07/2018
 * Time: 12:50 PM
 */

namespace Lvinkim\ElasticSearchODM;

use Elasticsearch\Client;


/**
 * 对 ES 的 curd 操作的封装
 * Class Repository
 * @package Lvinkim\ElasticSearchODM
 */
abstract class Repository
{
    /** @var Client */
    private $client;

    /** @var EntityConverter */
    private $entityConverter;

    /**
     * Repository constructor.
     * @param DocumentManager $documentManager
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->client = $documentManager->getClient();
        $this->entityConverter = new EntityConverter();
    }

    /**
     * 返回索引名, 例如: db, access-log-*, ... 等
     * @return string
     */
    abstract protected function getIndexName(): string;

    /**
     * 返回文档 type , 例如: user, product, ... 等
     * @return string
     */
    abstract protected function getTypeName(): string;

    /**
     * 返回数据表的对应实体类名
     * @return string
     */
    abstract protected function getEntityClassName(): string;

    public function deleteIndex()
    {
        $params = [
            'index' => $this->getIndexName(),
        ];
        $response = $this->client->indices()->delete($params);

        return $response;
    }

    /**
     * 初始化 Index
     */
    public function initialIndex()
    {
        $properties = $this->entityConverter->getProperties($this->getEntityClassName());

        $response = $this->createIndex($properties);

        return $response['acknowledged'] ?? false;
    }

    public function findOneById($id)
    {
        $response = $this->getDocument($id);
    }

    public function findOne(array $filter = [])
    {

    }

    public function findMany(array $filter = [], array $sort = null, int $skip = null, int $limit = null)
    {

    }

    public function deleteOne($entity)
    {

    }

    public function deleteMany(array $filter = [])
    {

    }

    public function upsertOne($entity)
    {

    }

    public function upsertMany($entities)
    {

    }


    /**
     * @return bool
     */
    public function existsType()
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
        ];

        return $this->client->indices()->existsType($params);
    }

    /**
     * @return array
     */
    public function getMappingProperties()
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
        ];
        $response = $this->client->indices()->getMapping($params);

        $properties = $response[$this->getIndexName()]['mappings'][$this->getTypeName()]['properties'] ?? [];

        return $properties;

    }

    /**
     * 根据 mapping 属性，创建索引
     * @param array $properties
     * @return array
     */
    public function createIndex(array $properties)
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'mappings' => [
                    $this->getTypeName() => [
                        'properties' => $properties
                    ],
                ],
            ],
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * 更新 mapping 属性
     * @param array $properties
     * @return array
     */
    public function putMappingProperties(array $properties)
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'body' => [
                $this->getTypeName() => [
                    'properties' => $properties,
                ],
            ],
        ];

        return $this->client->indices()->putMapping($params);
    }

    /**
     * @param $id
     * @return array
     */
    public function getDocument($id)
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'id' => $id
        ];

        return $this->client->get($params);
    }

    /**
     * @param null $body
     * @return array
     */
    public function searchDocuments($body = null)
    {
        if (!$body) {
            // 默认遍历所有记录
            $body = [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ];
        }
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'body' => $body,
        ];

        return $this->client->search($params);
    }

    /**
     * 遍历
     * @param null|string|array $body
     * @return \Generator
     */
    public function traversalDocuments($body = null)
    {
        if (!$body) {
            // 默认遍历所有记录
            $body = [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ];
        }

        $params = [
            "scroll" => "30s",
            "size" => 1000,
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'body' => $body
        ];

        $response = $this->client->search($params);

        do {
            if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
                $documents = $response['hits']['hits'];
                foreach ($documents as $document) {
                    yield $document;
                }

                $scroll_id = $response['_scroll_id'];
                $response = $this->client->scroll([
                        "scroll_id" => $scroll_id,
                        "scroll" => "30s"
                    ]
                );
            } else {
                break;
            }
        } while (1);
    }

    /**
     * 更新文档的部分字段
     * @param $id
     * @param $updateSet
     * @return array
     */
    public function updateDocument($id, $updateSet)
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'id' => $id,
            'body' => [
                'doc' => $updateSet
            ]
        ];

        return $this->client->update($params);
    }

    /**
     * 更新单个索引文档
     * @param $document
     * @return array|bool
     */
    public function indexDocument($document)
    {
        if (!isset($document['_id'])) {
            return false;
        }

        $params = [
            'index' => $this->getIndexName(),
            'type' => $this->getTypeName(),
            'id' => $document['_id'],
            'body' => $document['_source'] ?? []
        ];

        return $this->client->index($params);
    }

    /**
     * 批量更新索引文档
     * @param $documents array|\Generator
     */
    public function indexMultiDocuments($documents)
    {
        $number = 0;
        $params = ['body' => []];
        foreach ($documents as $document) {

            $_id = $document['_id'] ?? false;
            $_source = $document['_source'] ?? [];
            if (!$_id || !$_source) {
                continue;
            }

            $number++;

            $params['body'][] = [
                'index' => [
                    '_index' => $this->getIndexName(),
                    '_type' => $this->getTypeName(),
                    '_id' => $_id
                ]
            ];

            $params['body'][] = $_source;

            if ($number >= 1000) {
                $this->client->bulk($params);
                $number = 0;
                $params = ['body' => []];
            }
        }

        // Send the last batch if it exists
        if ($number > 0) {
            $this->client->bulk($params);
        }
    }

}