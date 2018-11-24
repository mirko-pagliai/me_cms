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
namespace MeCms\Test\TestCase\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;
use MeCms\Core\Plugin;
use MeCms\Model\Table\UsersGroupsTable;
use MeCms\Shell\InstallShell;
use MeCms\TestSuite\ConsoleIntegrationTestCase;
use MeTools\TestSuite\Traits\MockTrait;
use Tools\ReflectionTrait;

/**
 * InstallShellTest class
 */
class InstallShellTest extends ConsoleIntegrationTestCase
{
    use MockTrait;
    use ReflectionTrait;

    /**
     * @var array
     */
    protected $debug = [];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.UsersGroups',
    ];

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        safe_unlink_recursive(WWW_ROOT . 'vendor', 'empty');

        Plugin::unload('TestPlugin');
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        foreach (['config', 'links', 'paths'] as $property) {
            $this->assertNotEmpty($this->Shell->$property);
        }
    }

    /**
     * Test for `getOtherPlugins()` method
     * @test
     */
    public function testGetOtherPlugins()
    {
        $this->assertEmpty($this->invokeMethod($this->Shell, 'getOtherPlugins'));

        Plugin::load('TestPlugin');
        $this->assertEquals(['TestPlugin'], $this->invokeMethod($this->Shell, 'getOtherPlugins'));
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        //Gets all methods from `InstallShell`, except for the `all()` method
        $methods = array_merge(get_child_methods(get_parent_class(InstallShell::class)), get_child_methods(InstallShell::class));
        $methods = array_diff($methods, ['all']);

        $InstallShell = $this->getMockForShell(InstallShell::class, array_merge(['in', '_stop'], $methods));
        $InstallShell->method('in')->will($this->returnValue('y'));

        //Sets a callback for each method
        foreach ($methods as $method) {
            $InstallShell->method($method)->will($this->returnCallback(function () use ($method) {
                $this->debug[] = $method;
            }));
        }

        //Calls with `force` options
        Plugin::load('TestPlugin');
        $InstallShell->params['force'] = true;
        $InstallShell->all();

        $expectedMethodsCalledInOrder = [
            'setPermissions',
            'createRobots',
            'fixComposerJson',
            'createPluginsLinks',
            'createVendorsLinks',
            'copyConfig',
            'fixKcfinder',
            'runFromOtherPlugins',
        ];
        $this->assertEquals($expectedMethodsCalledInOrder, $this->debug);

        //Calls with no interactive mode
        $this->debug = [];
        unset($InstallShell->params['force']);
        array_unshift($expectedMethodsCalledInOrder, 'createDirectories');
        array_push($expectedMethodsCalledInOrder, 'createGroups', 'createAdmin');
        $InstallShell->all();
        $this->assertEquals($expectedMethodsCalledInOrder, $this->debug);
    }

    /**
     * Tests for `copyConfig()` method
     * @test
     */
    public function testCopyConfig()
    {
        $this->exec('me_cms.install copy_config -v');
        $this->assertExitWithSuccess();

        foreach ($this->Shell->config as $file) {
            $file = rtr(CONFIG . pluginSplit($file)[1] . '.php');
            $this->assertOutputContains('File or directory `' . $file . '` already exists');
        }
    }

    /**
     * Test for `createAdmin()` method
     * @test
     */
    public function testCreateAdmin()
    {
        $InstallShell = $this->getMockForShell(InstallShell::class, ['in', '_stop', 'dispatchShell']);
        $InstallShell->expects($this->once())
            ->method('dispatchShell')
            ->with('MeCms.user', 'add', '--group', 1);
        $InstallShell->createAdmin();
    }

    /**
     * Test for `createGroups()` method
     * @test
     */
    public function testCreateGroups()
    {
        //A group already exists
        $this->exec('me_cms.install create_groups -v');
        $this->assertExitWithError();
        $this->assertErrorContains('Some user groups already exist');

        //Deletes all groups
        $this->getMockForTable(UsersGroupsTable::class, null)->deleteAll(['id >=' => '1']);
        $this->exec('me_cms.install create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('The user groups have been created');
        $this->assertErrorEmpty();
    }

    /**
     * Tests for `createVendorsLinks()` method
     * @test
     */
    public function testCreateVendorsLinks()
    {
        $parentClass = get_parent_class(InstallShell::class);
        $links = array_diff($this->Shell->links, (new $parentClass)->links);
        unset($links[array_search('kcfinder', $links)]);

        $this->exec('me_cms.install create_vendors_links -v');
        $this->assertExitWithSuccess();

        foreach ($links as $link) {
            $this->assertOutputContains('Link `' . rtr(WWW_ROOT) . 'vendor' . DS . $link . '` has been created');
        }
    }

    /**
     * Test for `fixKcfinder()` method
     * @test
     */
    public function testFixKcfinder()
    {
        //This makes it believe that KCFinder is installed
        safe_mkdir(KCFINDER, 0777, true);
        file_put_contents(KCFINDER . 'browse.php', '@version 3.12');
        safe_unlink(KCFINDER . '.htaccess');
        $this->exec('me_cms.install fix_kcfinder -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('Creating file ' . KCFINDER . '.htaccess');
        $this->assertOutputContains('<success>Wrote</success> `' . KCFINDER . '.htaccess' . '`');
        $this->assertErrorEmpty();
        $this->assertStringEqualsFile(
            KCFINDER . '.htaccess',
            'php_value session.cache_limiter must-revalidate' . PHP_EOL .
            'php_value session.cookie_httponly On' . PHP_EOL .
            'php_value session.cookie_lifetime 14400' . PHP_EOL .
            'php_value session.gc_maxlifetime 14400' . PHP_EOL .
            'php_value session.name CAKEPHP'
        );

        //For now KCFinder is not available
        $browseFile = KCFINDER . 'browse.php';
        $browseFileContent = file_get_contents($browseFile);
        safe_unlink($browseFile);
        $this->exec('me_cms.install fix_kcfinder -v');
        $this->assertExitWithError();
        $this->assertErrorContains('KCFinder is not available');
        file_put_contents($browseFile, $browseFileContent);
    }

    /**
     * Test for `runFromOtherPlugins()` method
     * @test
     */
    public function testRunFromOtherPlugins()
    {
        $this->assertEmpty($this->Shell->runFromOtherPlugins());

        Plugin::load('TestPlugin');
        $this->assertEquals(['TestPlugin' => 0], $this->Shell->runFromOtherPlugins());
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->Shell->getOptionParser();
        $this->assertInstanceOf(ConsoleOptionParser::class, $parser);
        $this->assertEquals('Executes some tasks to make the system ready to work', $parser->getDescription());
        $this->assertArrayKeysEqual(['force', 'help', 'quiet', 'verbose'], $parser->options());

        $expectedMethods = array_map([Inflector::class, 'underscore'], $this->getShellMethods());
        $this->assertArrayKeysEqual($expectedMethods, $parser->subcommands());
    }
}
