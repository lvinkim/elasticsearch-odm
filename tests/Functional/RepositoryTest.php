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
        $this->userRepository->deleteIndex();

        $succeed = $this->userRepository->initialIndex();

        $this->assertTrue($succeed);
    }

    public function testFindOneById()
    {
        $id = 'lvinkim-001';

        /** @var User $user */
        $user = $this->userRepository->findOneById($id);

        $this->assertInstanceOf(User::class, $user);
    }
}