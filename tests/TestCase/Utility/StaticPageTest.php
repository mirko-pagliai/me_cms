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
namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\StaticPage;

/**
 * StaticPageTest class
 */
class StaticPageTest extends TestCase
{
    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        Cache::clear('static_pages');
        $this->removePlugins(['TestPlugin']);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $this->loadPlugins(['TestPlugin']);
        $TestPluginPath = rtr(array_value_first(App::path('Template', 'TestPlugin'))) . DS;

        $pages = StaticPage::all();
        $this->assertContainsOnlyInstancesOf(Entity::class, $pages);
        foreach ($pages as $page) {
            $this->assertInstanceOf(FrozenTime::class, $page->modified);
        }

        //Checks filenames
        $this->assertEquals([
            'page-from-app',
            'cookies-policy',
            'cookies-policy-it',
            'test-from-plugin',
            'page_on_second_from_plugin',
            'page-on-first-from-plugin',
        ], Hash::extract($pages, '{n}.filename'));

        //Checks paths
        $this->assertEquals([
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'templates' . DS . 'StaticPages' . DS . 'page-from-app.' . StaticPage::EXTENSION,
            'templates' . DS . 'StaticPages' . DS . 'cookies-policy-it.' . StaticPage::EXTENSION,
            'templates' . DS . 'StaticPages' . DS . 'cookies-policy.' . StaticPage::EXTENSION,
            $TestPluginPath . 'StaticPages' . DS . 'test-from-plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin.' . StaticPage::EXTENSION,
        ], Hash::extract($pages, '{n}.path'));

        //Checks slugs
        $this->assertEquals([
            'page-from-app',
            'cookies-policy',
            'cookies-policy-it',
            'test-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
            'first-folder/page-on-first-from-plugin',
        ], Hash::extract($pages, '{n}.slug'));

        //Checks titles
        $this->assertEquals([
            'Page From App',
            'Cookies Policy',
            'Cookies Policy It',
            'Test From Plugin',
            'Page On Second From Plugin',
            'Page On First From Plugin',
        ], Hash::extract($pages, '{n}.title'));
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet()
    {
        $this->loadPlugins(['TestPlugin']);

        //Gets all pages from slugs
        $pages = array_map([StaticPage::class, 'get'], Hash::extract(StaticPage::all(), '{n}.slug'));
        $this->assertEquals([
            DS . 'StaticPages' . DS . 'page-from-app',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy-it',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'test-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin',
        ], $pages);

        //Tries to get a no existing page
        $this->assertNull(StaticPage::get('no-Existing'));
    }

    /**
     * Test for `get()` method, using a different locale
     * @test
     */
    public function testGetDifferentLocale()
    {
        $expected = 'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy';
        $this->assertEquals($expected, StaticPage::get('cookies-policy'));

        $originalDefaultlLocale = ini_set('intl.default_locale', 'it_IT');
        $this->assertEquals(sprintf('%s-it', $expected), StaticPage::get('cookies-policy'));
        ini_set('intl.default_locale', $originalDefaultlLocale);

        $originalDefaultlLocale = ini_set('intl.default_locale', 'it');
        $this->assertEquals(sprintf('%s-it', $expected), StaticPage::get('cookies-policy'));
        ini_set('intl.default_locale', $originalDefaultlLocale);
    }

    /**
     * Test for `getAllPaths()` method
     * @test
     */
    public function testGetAllPaths()
    {
        $this->loadPlugins(['TestPlugin']);
        $result = $this->invokeMethod(StaticPage::class, 'getAllPaths');
        $this->assertContains(APP . 'templates' . DS . 'StaticPages' . DS, $result);
        $this->assertContains(ROOT . 'templates' . DS . 'StaticPages' . DS, $result);
        $this->assertContains(Plugin::path('TestPlugin') . 'templates' . DS . 'StaticPages' . DS, $result);
        $this->assertEquals(Cache::read('paths', 'static_pages'), $result);
    }

    /**
     * Test for `getSlug()` method
     * @group onlyUnix
     * @test
     */
    public function testGetSlug()
    {
        $getSlugMethod = function () {
            return $this->invokeMethod(StaticPage::class, 'getSlug', func_get_args());
        };


        foreach ([
            'my-file',
            'my-file.' . StaticPage::EXTENSION,
            '/first/second/my-file.' . StaticPage::EXTENSION,
        ] as $file) {
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second'));
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second/'));
        }

        $this->assertEquals('first/my-file', $getSlugMethod('first/my-file.' . StaticPage::EXTENSION, '/first/second'));
        $this->assertEquals('third/my-file', $getSlugMethod('/first/second/third/my-file.' . StaticPage::EXTENSION, '/first/second'));
    }

    /**
     * Test for `getSlug()` method on Windows
     * @group onlyWindows
     * @test
     */
    public function testGetSlugWin()
    {
        $getSlugMethod = function () {
            return $this->invokeMethod(StaticPage::class, 'getSlug', func_get_args());
        };

        $this->assertEquals('my-file', $getSlugMethod('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first\\second'));
        $this->assertEquals('my-file', $getSlugMethod('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first\\second\\'));
        $this->assertEquals('my-file', $getSlugMethod('C:\\\\first\\my-file.' . StaticPage::EXTENSION, 'C:\\\\first'));
        $this->assertEquals('second/my-file', $getSlugMethod('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first'));
        $this->assertEquals('second/my-file', $getSlugMethod('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first\\'));
    }

    /**
     * Test for `title()` method
     * @test
     */
    public function testTitle()
    {
        $expected = [
            'Page From App',
            'Cookies Policy',
            'Cookies Policy It',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ];

        //Gets all slugs and all paths from pages
        $slugs = Hash::extract(StaticPage::all(), '{*}.slug');
        $paths = Hash::extract(StaticPage::all(), '{*}.path');

        $count = count($slugs);
        for ($id = 0; $id < $count; $id++) {
            $this->assertEquals($expected[$id], StaticPage::title($slugs[$id]));
            $this->assertEquals($expected[$id], StaticPage::title($paths[$id]));
        }
    }
}
