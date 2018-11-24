<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\TestSuite\EntityTestCase;

/**
 * UserTest class
 */
class UserTest extends EntityTestCase
{
    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        safe_unlink_recursive(USER_PICTURES, 'empty');
        safe_unlink(WWW_ROOT . 'img' . DS . 'no-avatar.jpg');

        parent::tearDown();
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertHasNoAccessibleProperty(['id', 'post_count', 'modified']);
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertHasVirtualField(['full_name', 'picture']);
    }

    /**
     * Test for `_getFullName()` method
     * @test
     */
    public function testFullNameGetMutator()
    {
        $this->assertNull($this->Entity->full_name);

        $this->Entity->first_name = 'Alfa';
        $this->Entity->last_name = 'Beta';
        $this->assertEquals($this->Entity->first_name . ' ' . $this->Entity->last_name, $this->Entity->full_name);
    }

    /**
     * Test for `_getPicture()` method
     * @test
     */
    public function testPictureGetMutator()
    {
        $this->Entity->id = 1;
        $this->assertEquals('MeCms.no-avatar.jpg', $this->Entity->picture);

        $filename = WWW_ROOT . 'img' . DS . 'no-avatar.jpg';
        file_put_contents($filename, null);
        $this->assertEquals('no-avatar.jpg', $this->Entity->picture);

        $id = 0;
        foreach (['jpg', 'jpeg', 'gif', 'png', 'JPEG'] as $extension) {
            $id++;
            $this->Entity->id = $id;
            $filename = WWW_ROOT . 'img' . DS . 'users' . DS . $id . '.' . $extension;
            file_put_contents($filename, null);
            $this->assertEquals('users' . DS . $id . '.' . $extension, $this->Entity->picture);
        }
    }
}
