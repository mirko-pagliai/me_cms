<?php
declare(strict_types=1);

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

namespace MeCms\Test\TestCase\View\Helper\MenuHelper;

use MeCms\TestSuite\MenuHelperTestCase;

/**
 * UsersMenuHelperTest class
 */
class UsersMenuHelperTest extends MenuHelperTestCase
{
    /**
     * @test
     * @uses \MeCms\View\Helper\MenuHelper\UsersMenuHelper::getLinks()
     */
    public function testGetLinks(): void
    {
        $this->assertEmpty($this->getLinksAsHtml());

        $expected = [
            '<a href="/me-cms/admin/users" title="List users">List users</a>',
            '<a href="/me-cms/admin/users/add" title="Add user">Add user</a>',
            '<a href="/me-cms/admin/users-groups" title="List groups">List groups</a>',
            '<a href="/me-cms/admin/users-groups/add" title="Add group">Add group</a>',
        ];
        $this->setIdentity(['group' => ['name' => 'admin']]);
        $this->assertSame($expected, $this->getLinksAsHtml());

        $expected = [
            '<a href="/me-cms/admin/users" title="List users">List users</a>',
            '<a href="/me-cms/admin/users/add" title="Add user">Add user</a>',
        ];
        $this->setIdentity(['group' => ['name' => 'manager']]);
        $this->assertSame($expected, $this->getLinksAsHtml());
    }
}
