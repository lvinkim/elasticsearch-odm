<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 23/07/2018
 * Time: 10:57 PM
 */

namespace Lvinkim\ElasticSearchODM;


use Elasticsearch\Client;

/**
 * Class DocumentManager
 * @package Lvinkim\ElasticSearchODM
 */
class DocumentManager
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    public function getRepository($repositoryClassName): Repository
    {
        return new $repositoryClassName($this);
    }
}