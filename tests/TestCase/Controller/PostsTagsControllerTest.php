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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * PostsTagsControllerTest class
 */
class PostsTagsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTagsTable
     */
    protected $PostsTags;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
        'plugin.me_cms.users',
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

        $this->PostsTags = TableRegistry::get('MeCms.PostsTags');

        Cache::clear(false, $this->PostsTags->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PostsTags);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(['_name' => 'postsTags']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsTags/index.ctp');

        $tagsFromView = $this->viewVariable('tags');
        $this->assertInstanceof('Cake\ORM\ResultSet', $tagsFromView);
        $this->assertNotEmpty($tagsFromView);

        foreach ($tagsFromView as $tag) {
            $this->assertInstanceof('MeCms\Model\Entity\Tag', $tag);
        }

        //Sets the cache name
        $tagsFromCache = Cache::read('tag_index', $this->PostsTags->cache);

        $this->assertEquals($tagsFromView->toArray(), $tagsFromCache->toArray());
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->PostsTags->Tags->find('active')->extract('slug')->first();
        $url = ['_name' => 'postsTag', $slug];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsTags/view.ctp');

        $tagFromView = $this->viewVariable('tag');
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);

        $tagFromCache = Cache::read((sprintf('tag_%s', md5($slug))), $this->PostsTags->cache);
        $this->assertEquals($tagFromView, $tagFromCache->first());

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\ResultSet', $postsFromView);
        $this->assertNotEmpty($postsFromView);

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }

        //Sets the cache name
        $cache = sprintf('tag_%s_limit_%s_page_%s', md5($slug), config('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        $this->get(array_merge($url, ['?' => ['q' => $slug]]));
        $this->assertRedirect($url);
    }
}