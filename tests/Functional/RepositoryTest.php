<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/7/25
 * Time: 3:53 PM
 */

namespace Tests\Functional;


use Elasticsearch\ClientBuilder;
use Lvinkim\ElasticSearchODM\DocumentManager;
use PHPUnit\Framework\TestCase;
use Tests\App\Entity\User;
use Tests\App\Repository\UserRepository;


class RepositoryTest extends TestCase
{

    /** @var DocumentManager */
    static private $documentManager;

    public static function setUpBeforeClass()
    {
        $hosts = ['docker.for.mac.localhost:9200'];
        $manager = ClientBuilder::create()->setHosts($hosts)->build();

        self::$documentManager = new DocumentManager($manager);
    }

    /** @var UserRepository */
    private $userRepository;


    public function setUp()
    {
        $this->userRepository = self::$documentManager->getRepository(UserRepository::class);
    }

    public function testCreateIndex()
    {
        $succeed = $this->userRepository->initialIndex();

        $this->assertTrue($succeed);
    }

    public function testIndexDocument()
    {
        $document = [
            '_id' => 'lvinkim-123',
            '_source' => [
                'id' => 'lvinkim-123',
                'status' => true,
                'age' => 22,
                'weight' => 56.2,
                'birth' => intval(microtime(true) * 1000),
                'tags' => ['one', 'two'],
                'company' => [
                    'name' => 'company name',
                    'contact' => '18811012138',
                    'address' => [
                        'country' => '中国',
                        'city' => '广州',
                    ],
                ],
                'families' => [
                    [
                        'name' => 'Tom',
                        'relation' => 'brother',
                    ],
                ],
                'remark' => 'nothing'
            ],
        ];

        $response = $this->userRepository->indexDocument($document);

        $this->assertEquals($document['_id'], $response['_id']);
    }

    public function testIndexMultiDocuments()
    {
        $documents = (function () {
            foreach (range(1, 10) as $value) {
                yield [
                    '_id' => 'lvinkim-' . $value,
                    '_source' => [
                        'id' => 'lvinkim-' . $value,
                        'status' => true,
                        'age' => 22,
                        'weight' => 56.2,
                        'birth' => intval(microtime(true) * 1000),
                        'tags' => ['one', 'two'],
                        'company' => [
                            'name' => 'company name',
                            'contact' => '18811012138',
                            'address' => [
                                'country' => '中国',
                                'city' => '广州',
                            ],
                        ],
                        'families' => [
                            [
                                'name' => 'Tom',
                                'relation' => 'brother',
                            ],
                        ],
                        'remark' => 'nothing'
                    ],
                ];

            }
        })();

        $this->userRepository->indexMultiDocuments($documents);

        $this->assertTrue(true);
    }

    public function testUpdateDocument()
    {
        $id = 'lvinkim-123';
        $updateSet = [
            'age' => 30,
        ];
        $response = $this->userRepository->updateDocument($id, $updateSet);

        $this->assertEquals($id, $response['_id']);
    }

    public function testGetDocument()
    {
        $id = 'lvinkim-123';
        $response = $this->userRepository->getDocument($id);

        $this->assertEquals($id, $response['_id']);

    }


    public function testFindOneById()
    {
        $id = 'lvinkim-123';

        /** @var User $user */
        $user = $this->userRepository->findOneById($id);

        $this->assertInstanceOf(User::class, $user);

    }

    public function testFindOne()
    {
        $query = [
            "match" => [
                "company.address.city" => "广州",
            ]
        ];

        /** @var User $user */
        $user = $this->userRepository->findOne($query);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindMany()
    {
        $query = [
            "match" => [
                "company.address.city" => "广州",
            ]
        ];

        /** @var User[] $users */
        $users = $this->userRepository->findMany($query);

        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }

        $this->assertTrue(true);
    }

    /**
     * @depends testIndexDocument
     */
    public function testDeleteOne()
    {
        $id = 'lvinkim-123';

        $entity = $this->userRepository->findOneById($id);

        $this->assertInstanceOf(User::class, $entity);

        $succeed = $this->userRepository->deleteOne($entity);
        $this->assertTrue($succeed);
    }

    /**
     * @depends testIndexDocument
     */
    public function testUpsertOne()
    {
        $id = 'lvinkim-123';

        $entity = $this->userRepository->findOneById($id);

        $succeed = $this->userRepository->upsertOne($entity);

        $this->assertTrue($succeed);

    }

    /**
     * @depends testIndexMultiDocuments
     */
    public function testUpsertMany()
    {
        $query = [
            "match" => [
                "company.address.city" => "广州",
            ]
        ];

        /** @var User[] $users */
        $users = $this->userRepository->findMany($query);

        $this->userRepository->upsertMany($users);

        $this->assertTrue(true);
    }

    /**
     * @depends testIndexMultiDocuments
     */
    public function testTraversal()
    {
        $users = $this->userRepository->traversal();
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

}