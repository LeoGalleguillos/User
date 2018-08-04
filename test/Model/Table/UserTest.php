<?php
namespace LeoGalleguillos\UserTest\Model\Table;

use ArrayObject;
use Exception;
use LeoGalleguillos\User\Model\Table as UserTable;
use LeoGalleguillos\UserTest\TableTestCase;
use Zend\Db\Adapter\Adapter;
use PHPUnit\Framework\TestCase;

class UserTest extends TableTestCase
{
    /**
     * @var string
     */
    protected $sqlPath;

    protected function setUp()
    {
        $this->sqlPath = $_SERVER['PWD'] . '/sql/leogalle_test/user/';

        $this->userTable      = new UserTable\User($this->getAdapter());
        $this->loginHashTable = new UserTable\User\LoginHash($this->getAdapter());
        $this->loginIpTable   = new UserTable\User\LoginIp($this->getAdapter());

        $this->setForeignKeyChecks0();
        $this->dropTable();
        $this->createTable();
        $this->setForeignKeyChecks1();
    }

    protected function dropTable()
    {
        $sql = file_get_contents($this->sqlPath . 'drop.sql');
        $result = $this->adapter->query($sql)->execute();
    }

    protected function createTable()
    {
        $sql = file_get_contents($this->sqlPath . 'create.sql');
        $result = $this->adapter->query($sql)->execute();
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            UserTable\User::class,
            $this->userTable
        );
    }

    public function testInsert()
    {
        $this->userTable->insert(
            'username',
            'password hash',
            '1983-10-22'
        );

        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );

        $this->assertSame(
            2,
            $this->userTable->selectCount()
        );
    }

    public function testSelectCount()
    {
        $this->assertSame(
            0,
            $this->userTable->selectCount()
        );
    }

    public function testSelectOrderByCreatedDesc()
    {
        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );
        $this->userTable->insert(
            'Username',
            'passwordhash12345',
            '1983-10-22'
        );
        $generator = $this->userTable->selectOrderByCreatedDesc();
        foreach ($generator as $array) {
            $this->assertInternalType(
                'array',
                $array
            );
        }
    }

    public function testSelectWhereUserId()
    {
        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );
        $this->assertInternalType(
            'array',
            $this->userTable->selectWhereUserId(1)
        );
    }

    public function testSelectWhereUserIdLoginHashLoginIp()
    {
        try {
            $this->userTable->selectWhereUserIdLoginHashLoginIp(
                1,
                'login-hash',
                'login-ip'
            );
            $this->fail();
        } catch (Exception $exception) {
            $this->assertSame(
                $exception->getMessage(),
                'Row with user ID, login hash, and login IP not found.'
            );
        }

        $this->userTable->insert(
            'username',
            'password-hash',
            '1983-10-22'
        );
        $this->loginHashTable->updateWhereUserId(
            'login-hash',
            1
        );
        $this->loginIpTable->updateWhereUserId(
            'login-ip',
            1
        );
        $array = $this->userTable->selectWhereUserIdLoginHashLoginIp(
            1,
            'login-hash',
            'login-ip'
        );
        $this->assertSame(
            'username',
            $array['username']
        );
        $this->assertSame(
            'password-hash',
            $array['password_hash']
        );
    }

    public function testSelectWhereUsername()
    {
        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->userTable->selectWhereUsername('LeoGalleguillos')
        );
    }

    public function testUpdateViewsWhereUserId()
    {
        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );
        $this->assertTrue(
            $this->userTable->updateViewsWhereUserId(1),
            $this->userTable->updateViewsWhereUserId(1),
            $this->userTable->updateViewsWhereUserId(1)
        );

        $arrayObject = $this->userTable->selectWhereUserId(1);

        $this->assertSame(
            $arrayObject['views'],
            '3'
        );
    }

    public function testUpdateWhereUserId()
    {
        $this->userTable->insert(
            'LeoGalleguillos',
            'abcdefg1234567890',
            '1983-10-22'
        );

        $arrayObject = new ArrayObject([
            'welcome_message' => 'My welcome message.',
        ]);

        $this->assertTrue(
            $this->userTable->updateWhereUserId($arrayObject, 1)
        );
        $this->assertFalse(
            $this->userTable->updateWhereUserId($arrayObject, 1)
        );
    }
}
