<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Model\Table;

use App\Utility\Token;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * UsersTableTest class
 */
class UsersTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.tokens',
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Users = TableRegistry::get('MeCms.Users');

        $this->example = [
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => 'mypassword1!',
            'password_repeat' => 'mypassword1!',
        ];

        Cache::clear(false, $this->Users->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Users);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('users', $this->Users->cache);
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertNotEmpty($entity->password);
        $this->assertNotEmpty($entity->password_repeat);

        $this->example['password'] = $this->example['password_repeat'] = '';

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->errors());
        $this->assertObjectNotHasAttribute('password', $entity);
        $this->assertObjectNotHasAttribute('password_repeat', $entity);

        unset($this->example['password'], $this->example['password_repeat']);

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->errors());
        $this->assertObjectNotHasAttribute('password', $entity);
        $this->assertObjectNotHasAttribute('password_repeat', $entity);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $expected = [
            'email' => ['_isUnique' => 'This value is already used'],
            'username' => ['_isUnique' => 'This value is already used'],
        ];

        $entity = $this->Users->newEntity($this->example);
        $this->assertNotEmpty($this->Users->save($entity));

        //Saves again the same entity
        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals($expected, $entity->errors());

        $this->example['group_id'] = 999;

        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals(array_merge([
            'group_id' => ['_existsIn' => 'You have to select a valid option']
        ], $expected), $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('users', $this->Users->getTable());
        $this->assertEquals('username', $this->Users->getDisplayField());
        $this->assertEquals('id', $this->Users->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Users->Groups);
        $this->assertEquals('group_id', $this->Users->Groups->getForeignKey());
        $this->assertEquals('INNER', $this->Users->Groups->getJoinType());
        $this->assertEquals('MeCms.UsersGroups', $this->Users->Groups->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Users->Posts);
        $this->assertEquals('user_id', $this->Users->Posts->getForeignKey());
        $this->assertEquals('MeCms.Posts', $this->Users->Posts->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Users->Tokens);
        $this->assertEquals('user_id', $this->Users->Tokens->getForeignKey());
        $this->assertEquals('Tokens.Tokens', $this->Users->Tokens->className());

        $this->assertTrue($this->Users->hasBehavior('Timestamp'));
        $this->assertTrue($this->Users->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\UserValidator', $this->Users->validator());
    }

    /**
     * Test for the `belongsTo` association with `UsersGroups`
     * @test
     */
    public function testBelongsToUsersGroups()
    {
        $user = $this->Users->findById(4)->contain(['Groups'])->first();

        $this->assertNotEmpty($user->group);

        $this->assertInstanceOf('MeCms\Model\Entity\UsersGroup', $user->group);
        $this->assertEquals(3, $user->group->id);
    }

    /**
     * Test for the `hasMany` association with `Posts`
     * @test
     */
    public function testHasManyPosts()
    {
        $user = $this->Users->findById(1)->contain(['Posts'])->first();

        $this->assertNotEmpty($user->posts);

        foreach ($user->posts as $post) {
            $this->assertInstanceOf('MeCms\Model\Entity\Post', $post);
            $this->assertEquals(1, $post->user_id);
        }
    }

    /**
     * Test for the `hasMany` association with `Tokens`
     * @test
     */
    public function testHasManyTokens()
    {
        //Creates a token
        $token = (new Token)->create('testToken', ['user_id' => 4]);

        $user = $this->Users->findById(4)->contain(['Tokens'])->first();

        $this->assertEquals(1, count($user->tokens));

        $this->assertInstanceOf('Tokens\Model\Entity\Token', $user->tokens[0]);
        $this->assertEquals(4, $user->tokens[0]->user_id);
        $this->assertEquals($token, $user->tokens[0]->token);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->Users->hasFinder('active'));

        $query = $this->Users->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $user) {
            $this->assertTrue($user->active);
            $this->assertFalse($user->banned);
        }
    }

    /**
     * Test for `findBanned()` method
     * @test
     */
    public function testFindBanned()
    {
        $this->assertTrue($this->Users->hasFinder('banned'));

        $query = $this->Users->find('banned');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE Users.banned = :c0', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $user) {
            $this->assertTrue($user->banned);
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $this->assertTrue($this->Users->hasFinder('pending'));

        $query = $this->Users->find('pending');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());

        $this->assertFalse($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $user) {
            $this->assertFalse($user->active);
            $this->assertFalse($user->banned);
        }
    }

    /**
     * Test for `getActiveList()` method
     * @test
     */
    public function testGetActiveList()
    {
        $users = $this->Users->getActiveList();

        $this->assertEquals([
            4 => 'Abc Def',
            1 => 'Alfa Beta',
            3 => 'Ypsilon Zeta',
        ], $users);
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = [
            'username' => 'test',
            'group' => 1,
            'status' => 'active',
        ];

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2 AND Users.banned = :c3)', $query->sql());

        $params = collection($query->valueBinder()->bindings())->extract('value')->toList();

        $this->assertEquals([
            '%test%',
            1,
            true,
            false,
        ], $params);

        $data['status'] = 'pending';

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2)', $query->sql());

        $params = collection($query->valueBinder()->bindings())->extract('value')->toList();

        $this->assertEquals([
            '%test%',
            1,
            false,
        ], $params);

        $data['status'] = 'banned';

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertEquals('SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.banned = :c2)', $query->sql());

        $params = collection($query->valueBinder()->bindings())->extract('value')->toList();

        $this->assertEquals([
            '%test%',
            1,
            true,
        ], $params);
    }

    /**
     * Test for `queryFromFilter()` method, with invalid data
     * @test
     */
    public function testQueryFromFilterWithInvalidData()
    {
        $expected = 'SELECT Users.id AS `Users__id`, Users.group_id AS `Users__group_id`, Users.username AS `Users__username`, Users.email AS `Users__email`, Users.password AS `Users__password`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`, Users.active AS `Users__active`, Users.banned AS `Users__banned`, Users.post_count AS `Users__post_count`, Users.created AS `Users__created`, Users.modified AS `Users__modified` FROM users Users';

        $data = [
            'status' => 'invalid',
            'username' => 'ab',
        ];

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertEquals($expected, $query->sql());
        $this->assertEmpty($query->valueBinder()->bindings());
    }

    /**
     * Test for `validationDoNotRequirePresence()` method
     * @test
     */
    public function testValidationDoNotRequirePresence()
    {
        $example = [
            'email' => 'example@test.com',
        ];

        $entity = $this->Users->newEntity($example);
        $this->assertNotEmpty($entity->errors());

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->errors());

        $example['email_repeat'] = $example['email'];

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->errors());

        $example['email_repeat'] = $example['email'] . 'aaa';

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEquals([
            'email_repeat' => ['compareWith' => 'Email addresses don\'t match'],
        ], $entity->errors());
    }

    /**
     * Test for `validationEmptyPassword()` method
     * @test
     */
    public function testValidationEmptyPassword()
    {
        $this->example['password'] = $this->example['password_repeat'] = '';

        $expected = [
            'password' => ['_empty' => 'This field cannot be left empty'],
            'password_repeat' => ['_empty' => 'This field cannot be left empty'],
        ];

        $entity = $this->Users->newEntity($this->example);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->errors());
    }
}
