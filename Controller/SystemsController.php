<?php
/**
 * SystemsController
 *
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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');
App::uses('Folder', 'Utility');
App::uses('Apache', 'MeTools.Utility');
App::uses('BannerManager', 'MeCms.Utility');
App::uses('PhotoManager', 'MeCms.Utility');
App::uses('Php', 'MeTools.Utility');
App::uses('Plugin', 'MeTools.Utility');
App::uses('System', 'MeTools.Utility');
App::uses('Unix', 'MeTools.Utility');

/**
 * Systems Controller
 */
class SystemsController extends MeCmsAppController {
	/**
	 * Checks if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isAdmin();
	}
	
	/**
	 * Media browser with KCFinder
	 */
	public function admin_browser() {
		//Checks for KCFinder
		if(!is_readable(WWW_ROOT.'kcfinder')) {
			$this->Session->flash(__d('me_cms', '%s is not present into %s', 'KCFinder', WWW_ROOT.'kcfinder'), 'error');
			$this->redirect('/admin');
		}
		
		//Checks for uploads directory (`APP/webroot/files`)
		if(!is_writable(WWW_ROOT.'files')) {
			$this->Session->flash(__d('me_cms', 'The directory %s is not readable or writable', WWW_ROOT.'files'), 'error');
			$this->redirect('/admin');
		}
		
		//Gets types
		$types = $this->config['kcfinder']['types'];
		
		//Checks the type, then sets the KCFinder path
		if(!empty($this->request->query['type']) && array_key_exists($this->request->query['type'], $types))
			$this->set('kcfinder', sprintf('/kcfinder/browse.php?lang=%s&type=%s', Configure::read('Config.language'), $this->request->query['type']));
		
		$this->set(array(
			'title_for_layout'	=> __d('me_cms', 'Media browser'),
			'types'				=> array_combine(array_keys($types), array_keys($types))
		));
	}
	
	/**
	 * Manages cache and thumbnails.
	 * @uses System::checkCacheStatus()
	 * @uses System::getCacheSize()
	 * @uses System::getThumbsSize()
	 */
	public function admin_cache() {
        $this->set(array(
			'cacheStatus'		=> System::checkCacheStatus(),
			'cacheSize'			=> System::getCacheSize(),
			'title_for_layout'	=> __d('me_cms', 'Cache and thumbs'),
			'thumbsSize'		=> System::getThumbsSize()
        ));
    }
	
	/**
	 * System checkup.
	 * @uses Apache::checkMod()
	 * @uses BannerManager::getFolder()
	 * @uses BannerManager::getTmpPath()
	 * @uses PhotoManager::getFolder()
	 * @uses PhotoManager::getTmpPath()
	 * @uses Php::checkExt()
	 * @uses Php::checkVersion()
	 * @uses Plugin::getVersion()
	 * @uses Plugin::getVersions()
	 * @uses System::checkCache()
	 * @uses System::checkCacheStatus()
	 * @uses System::checkLogs()
	 * @uses System::checkThumbs()
	 * @uses System::checkTmp()
	 * @uses System::dirIsWritable()
	 * @uses System::getCakeVersion()
	 * @uses Unix::which()
	 */
	public function admin_checkup() {
		$phpRequired = '5.2.8';
		
		//Sets results
		$this->set(array(
			'bannersWWW'		=> System::dirIsWritable(BannerManager::getFolder()),
			'bannersTmp'		=> System::dirIsWritable(BannerManager::getTmpPath()),
			'cache'				=> System::checkCache(),
			'cacheStatus'		=> System::checkCacheStatus(),
			'cakeVersion'		=> System::getCakeVersion(),
			'expires'			=> Apache::checkMod('mod_expires'),
			'ffmpegthumbnailer'	=> Unix::which('ffmpegthumbnailer'),
			'imagick'			=> Php::checkExt('imagick'),
			'logs'				=> System::checkLogs(),
			'photosWWW'			=> System::dirIsWritable(PhotoManager::getFolder()),
			'photosTmp'			=> System::dirIsWritable(PhotoManager::getTmpPath()),
			'phpRequired'		=> $phpRequired,
			'phpVersion'		=> Php::checkVersion($phpRequired),
			'plugins'			=> Plugin::getVersions('MeCms'),
			'rewrite'			=> Apache::checkMod('mod_rewrite'),
			'tmp'				=> System::checkTmp(),
			'thumbs'			=> System::checkThumbs(),
			'version'			=> Plugin::getVersion('MeCms')
		));
		
		$this->set('title_for_layout', __d('me_cms', 'System checkup'));
	}
	
	/**
	 * Clears the cache.
	 * @uses System::clearCache()
	 */
	public function admin_clear_cache() {
		$this->request->onlyAllow('post', 'delete');
		
		if(System::clearCache())
			$this->Session->flash(__d('me_cms', 'The cache has been cleared'), 'success');
		else
			$this->Session->flash(__d('me_cms', 'The cache is not writable'), 'error');
		
		$this->redirect(array('action' => 'cache'));
	}
	
	/**
	 * Clears the thumbnails.
	 * @uses System::clearThumbs()
	 */
	public function admin_clear_thumbs() {
		$this->request->onlyAllow('post', 'delete');
		
		if(System::clearThumbs())
			$this->Session->flash(__d('me_cms', 'Thumbnails have been deleted'), 'success');
		else
			$this->Session->flash(__d('me_cms', 'Thumbnails have not been deleted'), 'error');
		
		$this->redirect(array('action' => 'cache'));
	}
	
	/**
	 * Log viewer
	 */
	public function admin_log_viewer() {
		//Gets log files
		$dir = new Folder(LOGS);
		$files = $dir->find('[^\.]+\.log(\.[^\-]+)?', TRUE);
		
		//Re-indexes, starting to 1
		$files = array_combine(range(1, count($files)), array_values($files));
		
		//If a log file has been specified
		if(!empty($this->request->query['file']) && $this->request->is('get'))
			$this->set('log', @file_get_contents(LOGS.$files[$this->request->query['file']]));
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Log viewer')), compact('files')));
	}
	
	/**
	 * Offline page
	 */
	public function offline() {
		//If the site has not been taken offline
		if(!$this->config['offline'])
			$this->redirect('/');
		
		//Sets the layout
		$this->layout = 'MeCms.users';
		
		$this->set('title_for_layout', FALSE);
	}
}