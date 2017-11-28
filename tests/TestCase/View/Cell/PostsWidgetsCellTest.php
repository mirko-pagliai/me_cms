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
namespace MeCms\Test\TestCase\View\Cell;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;
use MeTools\TestSuite\TestCase;

/**
 * PostsWidgetsCellTest class
 */
class PostsWidgetsCellTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        Cache::clearAll();

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        $this->Widget = new WidgetHelper(new View);
    }

    /**
     * Test for `categories()` method
     * @test
     */
    public function testCategories()
    {
        $widget = ME_CMS . '.Posts::categories';

        $result = $this->Widget->widget($widget)->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/category/category'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => 'first-post-category']],
            'First post category (1)',
            '/option',
            ['option' => ['value' => 'sub-sub-post-category']],
            'Sub sub post category (2)',
            '/option',
            '/select',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Renders as list
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/category/first-post-category', 'title' => 'First post category']],
            'First post category',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/category/sub-sub-post-category', 'title' => 'Sub sub post category']],
            'Sub sub post category',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'postsCategories']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_categories', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertArrayKeysEqual([
            'first-post-category',
            'sub-sub-post-category',
        ], $fromCache->toArray());
    }

    /**
     * Test for `categories()` method, with no posts
     * @test
     */
    public function testCategoriesNoPosts()
    {
        $widget = ME_CMS . '.Posts::categories';

        $this->Posts->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $widget = ME_CMS . '.Posts::latest';

        $latestPost = $this->Posts->find('active')->order(['created' => 'DESC'])->first();

        //Tries with a limit of 1
        $result = $this->Widget->widget($widget, ['limit' => 1])->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Latest post',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $latestPost->slug, 'title' => $latestPost->title]],
            $latestPost->title,
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        list($latestPost, $penultimatePost) = $this->Posts->find('active')->order(['created' => 'DESC'])->limit(2)->toArray();

        //Tries with a limit of 2
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Latest 2 posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $latestPost->slug, 'title' => $latestPost->title]],
            $latestPost->title,
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $penultimatePost->slug, 'title' => $penultimatePost->title]],
            $penultimatePost->title,
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'posts']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_latest_1', $this->Posts->cache);
        $this->assertEquals(1, $fromCache->count());

        $fromCache = Cache::read('widget_latest_2', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
    }

    /**
     * Test for `latest()` method, with no posts
     * @test
     */
    public function testLatestNoPosts()
    {
        $this->Posts->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget(ME_CMS . '.Posts::latest')->render());
    }

    /**
     * Test for `months()` method
     * @test
     */
    public function testMonths()
    {
        $widget = ME_CMS . '.Posts::months';

        $result = $this->Widget->widget($widget)->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/' . date('Y/m')],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => '2016/12']],
            'December 2016 (5)',
            '/option',
            ['option' => ['value' => '2016/11']],
            'November 2016 (1)',
            '/option',
            '/select',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Renders as list
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/2016/12', 'title' => 'December 2016']],
            'December 2016',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/2016/11', 'title' => 'November 2016']],
            'November 2016',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'posts']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_months', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertArrayKeysEqual([
            '2016/12',
            '2016/11',
        ], $fromCache->toArray());

        foreach ($fromCache as $key => $entity) {
            $this->assertInstanceOf('Cake\I18n\FrozenDate', $entity->month);
            $this->assertEquals($key, $entity->month->i18nFormat('yyyy/MM'));
        }
    }

    /**
     * Test for `months()` method, with no posts
     * @test
     */
    public function testMonthsNoPosts()
    {
        $widget = ME_CMS . '.Posts::months';

        $this->Posts->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `search()` method
     * @test
     */
    public function testSearch()
    {
        $widget = ME_CMS . '.Posts::search';

        $result = $this->Widget->widget($widget)->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Search posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => [
                'method' => 'get',
                'accept-charset' => 'utf-8',
                'class' => 'form-inline',
                'action' => '/posts/search',
            ],
            ['div' => ['class' => 'form-group input text']],
            ['div' => ['class' => 'input-group']],
            'input' => [
                'type' => 'text',
                'name' => 'p',
                'placeholder' => 'Search...',
                'class' => 'form-control',
            ],
            'span' => ['class' => 'input-group-btn'],
            'button' => ['class' => 'btn btn-primary', 'type' => 'submit'],
            'i' => ['class' => 'fa fa-search'],
            ' ',
            '/i',
            '/button',
            '/span',
            '/div',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on search
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'postsSearch']));
        $this->assertEmpty($widget->render());
    }
}
