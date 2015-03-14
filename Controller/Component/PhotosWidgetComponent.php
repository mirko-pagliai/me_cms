<?php
/**
 * PhotosWidgetComponent
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
 * @package		MeCms\Controller\Component
 */

App::uses('Component', 'Controller');

/**
 * Photos widgets
 */
class PhotosWidgetComponent extends Component {
	/**
	 * Albums widgets
	 * @return array Albums
	 */
	public function albums() {
		//Tries to get data from the cache
		$albums = Cache::read($cache = 'widget_albums', 'photos');
		
		//If the data are not available from the cache
        if(empty($albums)) {
			//Loads the `PhotosAlbums` model
			$this->PhotosAlbum = ClassRegistry::init('MeCms.PhotosAlbum');
						
			$albums = array();
			
			//Changes the title, adding the "photo_count" value
			foreach($this->PhotosAlbum->find('active', array('fields' => array('title', 'slug', 'photo_count'))) as $album)
				$albums[$album['PhotosAlbum']['slug']] = sprintf('%s (%d)', $album['PhotosAlbum']['title'], $album['PhotosAlbum']['photo_count']);
						
            Cache::write($cache, $albums, 'photos');
		}
		
		return $albums;
	}
	
	/**
	 * Random photo widget
	 * @return array Photos
	 */
	public function random() {
		$options = array_values(func_get_args())[0];
		
		$limit = empty($options['limit']) ? 1 : $options['limit'];
		
		//Loads the `Photo` model
		$this->Photo = ClassRegistry::init('MeCms.Photo');
		
		return $this->Photo->find('random', am(array(
			'conditions'	=> array('Album.active' => TRUE),
			'contain'		=> 'Album',
			'fields'		=> array('album_id', 'filename')
		), compact('limit')));
	}
}