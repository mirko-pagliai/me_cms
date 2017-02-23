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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\Post;
use MeTools\Utility\Youtube;

/**
 * PostTest class
 */
class PostTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
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

        $this->Posts = TableRegistry::get('MeCms.Posts');

        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Posts);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('MeCms\Model\Entity\Post', new Post);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $entity = new Post();

        $this->assertFalse($entity->accessible('id'));
        $this->assertFalse($entity->accessible('modified'));
    }

    /**
     * Test for `_getPreview()` method
     * @test
     */
    public function testPreviewGetMutator()
    {
        $entity = new Post();

        $this->assertNull($entity->preview);

        $entity->text = 'This is a simple text';
        $this->assertFalse($entity->preview);

        $entity->text = '<img src=\'image.jpg\' /> Image before text';
        $this->assertEquals('image.jpg', $entity->preview);

        $expected = Youtube::getPreview('videoID');

        $entity->text = '[youtube]videoID[/youtube]';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '[youtube]videoID[/youtube]Text';
        $this->assertEquals($expected, $entity->preview);

        $entity->text = '[youtube]videoID[/youtube] Text';
        $this->assertEquals($expected, $entity->preview);
    }

    /**
     * Test for `_getTagsAsString()` method
     * @test
     */
    public function testTagsAsStringGetMutator()
    {
        $post = $this->Posts->findById(1)->contain(['Tags'])->first();
        $this->assertEquals('cat, dog, bird', $post->tags_as_string);

        $post = $this->Posts->findById(3)->contain(['Tags'])->first();
        $this->assertEquals('cat', $post->tags_as_string);

        $post = $this->Posts->findById(4)->contain(['Tags'])->first();
        $this->assertNull($post->tags_as_string);
    }
}
