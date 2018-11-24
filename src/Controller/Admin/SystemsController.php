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
namespace MeCms\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Core\Plugin;
use MeCms\Utility\Checkup;
use Thumber\Utility\ThumbManager;

/**
 * Systems controller
 */
class SystemsController extends AppController
{
    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction('browser')) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can clear all temporary files or logs
        if ($this->request->isAction('tmpCleaner') &&
            in_array($this->request->getParam('pass.0'), ['all', 'logs'])
        ) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Media browser with KCFinder.
     *
     * The KCFinder component is loaded by the `initialize()` method.
     * @return void
     * @uses MeCms\Controller\Component\KcFinderComponent::getTypes()
     */
    public function browser()
    {
        //Gets the type from the query and the supported types from configuration
        $type = $this->request->getQuery('type');
        $types = $this->KcFinder->getTypes();

        //If there's only one type, it automatically sets the query value
        if (!$type && count($types) < 2) {
            $type = first_value(array_keys($types));
            $this->request = $this->request->withQueryParams(compact('type'));
        }

        //Checks the type, then sets the KCFinder path
        if ($type && array_key_exists($type, $types)) {
            //Sets locale
            $locale = substr(I18n::getLocale(), 0, 2);
            $locale = empty($locale) ? 'en' : $locale;

            $this->set('kcfinder', sprintf('%s/kcfinder/browse.php?lang=%s&type=%s', Router::url('/vendor', true), $locale, $type));
        }

        $this->set('types', array_combine(array_keys($types), array_keys($types)));
    }

    /**
     * Changelogs viewer
     * @return void
     * @uses MeCms\Core\Plugin:all()
     * @uses MeCms\Core\Plugin:path()
     */
    public function changelogs()
    {
        foreach (Plugin::all() as $plugin) {
            $file = Plugin::path($plugin, 'CHANGELOG.md', true);

            if ($file) {
                $files[$plugin] = rtr($file);
            }
        }

        $changelog = null;

        //If a changelog file has been specified
        if ($this->request->getQuery('file')) {
            $changelog = file_get_contents(ROOT . DS . $files[$this->request->getQuery('file')]);
        }

        $this->set(compact('changelog', 'files'));
    }

    /**
     * System checkup
     * @return void
     * @uses MeCms\Utility\Checkup::$Apache
     * @uses MeCms\Utility\Checkup::$Backups
     * @uses MeCms\Utility\Checkup::$KCFinder
     * @uses MeCms\Utility\Checkup::$PHP
     * @uses MeCms\Utility\Checkup::$Plugin
     * @uses MeCms\Utility\Checkup::$TMP
     * @uses MeCms\Utility\Checkup::$Webroot
     */
    public function checkup()
    {
        $Checkup = new Checkup;

        $results = [
            'apache' => [
                'modules' => $Checkup->Apache->modules(),
                'version' => $Checkup->Apache->version(),
            ],
            'backups' => $Checkup->Backups->isWriteable(),
            'cache' => Cache::enabled(),
            'cakephp' => Configure::version(),
            'kcfinder' => [
                'htaccess' => $Checkup->KCFinder->htaccess(),
                'version' => $Checkup->KCFinder->version(),
            ],
            'phpExtensions' => $Checkup->PHP->extensions(),
            'plugins' => $Checkup->Plugin->versions(),
            'temporary' => $Checkup->TMP->isWriteable(),
            'webroot' => $Checkup->Webroot->isWriteable(),
        ];

        foreach ($results as $key => $value) {
            $this->set($key, $value);
        };
    }

    /**
     * Internal function to clear the cache
     * @return bool
     */
    protected function clearCache()
    {
        return !array_search(false, Cache::clearAll(), true);
    }

    /**
     * Internal function to clear the sitemap
     * @return bool
     */
    protected function clearSitemap()
    {
        return is_readable(SITEMAP) ? (new File(SITEMAP))->delete() : true;
    }

    /**
     * Temporary cleaner (assets, cache, logs, sitemap and thumbnails)
     * @param string $type Type
     * @return \Cake\Network\Response|null|void
     * @uses clearCache()
     * @uses clearSitemap()
     */
    public function tmpCleaner($type)
    {
        $this->request->allowMethod(['post', 'delete']);

        $assetsTarget = getConfigOrFail('Assets.target');
        $success = true;

        switch ($type) {
            case 'all':
                safe_unlink_recursive($assetsTarget);
                safe_unlink_recursive(LOGS);
                $success = self::clearCache() && self::clearSitemap() && (new ThumbManager)->clearAll();
                break;
            case 'cache':
                $success = self::clearCache();
                break;
            case 'assets':
                safe_unlink_recursive($assetsTarget);
                break;
            case 'logs':
                safe_unlink_recursive(LOGS);
                break;
            case 'sitemap':
                $success = self::clearSitemap();
                break;
            case 'thumbs':
                $success = (new ThumbManager)->clearAll();
                break;
            default:
                $success = false;
        }

        if ($success) {
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        return $this->redirect($this->referer(['action' => 'tmpViewer']));
    }

    /**
     * Temporary files viewer (assets, cache, logs, sitemap and thumbnails)
     * @return void
     */
    public function tmpViewer()
    {
        $assetsSize = (new Folder(getConfigOrFail('Assets.target')))->dirsize();
        $cacheSize = (new Folder(CACHE))->dirsize();
        $logsSize = (new Folder(LOGS))->dirsize();
        $sitemapSize = is_readable(SITEMAP) ? filesize(SITEMAP) : 0;
        $thumbsSize = (new Folder(getConfigOrFail('Thumber.target')))->dirsize();

        $this->set('cacheStatus', Cache::enabled());
        $this->set('totalSize', $assetsSize + $cacheSize + $logsSize + $sitemapSize + $thumbsSize);
        $this->set(compact('assetsSize', 'cacheSize', 'logsSize', 'sitemapSize', 'thumbsSize'));
    }
}
