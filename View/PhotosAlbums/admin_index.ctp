<?php
/**
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\View\PhotosAlbums
 */
?>
	
<?php $this->extend('/Common/photos'); ?>
	
<div class="photosAlbums index">
	<?php
		echo $this->Html->h2(__d('me_cms_backend', 'Photos albums'));
		echo $this->Html->button(__d('me_cms_backend', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th class="min-width text-center"><?php echo $this->Paginator->sort('photo_count', __d('me_cms_backend', 'Photos')); ?></th>
		</tr>
		<?php foreach($photosAlbums as $photosAlbum): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($photosAlbum['PhotosAlbum']['title'], array('controller' => 'photos', $photosAlbum['PhotosAlbum']['id']));
						echo $this->Html->strong($title);
						
						echo $this->Html->ul(array(
							$this->Html->link(__d('me_cms_backend', 'View'), array('controller' => 'photos', $photosAlbum['PhotosAlbum']['id']), array('icon' => 'eye')),
							$this->Html->link(__d('me_cms_backend', 'Edit'), array('action' => 'edit', $photosAlbum['PhotosAlbum']['id']), array('icon' => 'pencil')),
							$this->Form->postLink(__d('me_cms_backend', 'Delete'), array('action' => 'delete', $photosAlbum['PhotosAlbum']['id']), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms_backend', 'Are you sure you want to delete this  photos album?')),
							$this->Html->link(__d('me_cms_backend', 'Open'), array('action' => 'view', $photosAlbum['PhotosAlbum']['slug'], 'admin' => FALSE, 'plugin' => 'me_cms_frontend'), array('icon' => 'external-link', 'target' => '_blank'))
						), array('class' => 'actions'));
					?>
				</td>
				<td><?php echo $photosAlbum['PhotosAlbum']['description']; ?></td>
				<td class="min-width text-center"><?php echo $photosAlbum['PhotosAlbum']['photo_count']; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>