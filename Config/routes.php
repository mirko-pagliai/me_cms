<?php

/**
 * Routes.
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Config
 */

//Admin home page
Router::connect('/admin',	array('controller' => 'posts', 'plugin' => 'me_cms', 'admin' => TRUE));

//Login
Router::connect('/login',	array('controller' => 'users',	'action' => 'login',	'plugin' => 'me_cms'));
Router::connect('/logout',	array('controller' => 'users',	'action' => 'logout',	'plugin' => 'me_cms'));

//Each "admin" request is directed to the plugin
Router::connect('/admin/:controller',			array('plugin' => 'me_cms', 'admin' => TRUE));
Router::connect('/admin/:controller/:action/*',	array('plugin' => 'me_cms', 'admin' => TRUE));