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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Posts
 */
?>

<div class="posts index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Search posts'));
		
		if(!empty($pattern))
			echo $this->Html->para('bg-info padding-10', __d('me_cms', 'You have searched for: %s', $this->Html->i($pattern)));

		if(!empty($posts)) {
			$list = array();
			foreach($posts as $post) {
				$title = $this->Html->link($post['Post']['title'], array('action' => 'view', $post['Post']['slug']));
				$list[] = $this->Html->div(NULL, 
					sprintf('%s (%s)', $title, $this->Time->format($post['Post']['created'], $config['datetime']['short'])).
					$this->Html->para('text-justify', $this->Text->truncate(strip_tags($post['Post']['text']), 500, array('exact' => FALSE, 'html' => TRUE)))
				);
			}

			echo $this->Html->ul($list, array(), array('icon' => 'caret-right'));
		}
	?>
</div>