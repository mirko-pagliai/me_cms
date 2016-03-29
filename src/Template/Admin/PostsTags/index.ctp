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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?= $this->assign('title', __d('me_cms', 'Tags')) ?>

<?= $this->Html->cssStart() ?>
	<style type="text/css">
		.index > div > div {
			margin-bottom: -10px;
			padding: 10px 5px;
		}

		.index > div > div > div {
			background-color: #f9f9f9;
			border-bottom: 1px solid #ddd;
			padding: 15px 15px;
		}
	</style>
<?= $this->Html->cssEnd() ?>

<div class="postsTags index">
	<?= $this->Html->h2(__d('me_cms', 'Tags')) ?>
	
    <?= $this->Form->createInline(FALSE, ['class' => 'filter-form', 'type' => 'get']) ?>
		<fieldset>
			<legend><?= __d('me_cms', 'Filter').$this->Html->icon('eye') ?></legend>
			<div>
				<?php
					echo $this->Form->input('name', [
						'default'		=> $this->request->query('name'),
						'placeholder'	=> __d('me_cms', 'name'),
						'size'			=> 16
					]);
					echo $this->Form->submit(NULL, ['icon' => 'search']);
				?>
			</div>
		</fieldset>
	<?= $this->Form->end() ?>
    
	<div class="div-striped row">
		<?php foreach($tags as $tag): ?>
			<div class="col-sm-3">
				<div>
                    <?= $this->Html->link($this->Html->strong($tag->tag), ['controller' => 'PostsTags', 'action' => 'edit', $tag->id]) ?> 
                    (<?= $this->Html->link($tag->post_count, ['controller' => 'Posts', 'action' => 'index', '?' => ['tag' => $tag->tag]], ['title' => __d('me_cms', 'View items that belong to this element')]) ?>)
                    <?php
						$actions = [];

						//Only admins and managers can edit tags
						if($this->Auth->isGroup(['admin', 'manager']))
							$actions[] = $this->Html->link(__d('me_cms', 'Edit'), ['controller' => 'PostsTags', 'action' => 'edit', $tag->id], ['icon' => 'pencil']);

						$actions[] = $this->Html->link(__d('me_cms', 'Open'), ['_name' => 'posts_tag', $tag->slug], ['icon' => 'external-link', 'target' => '_blank']);

						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?= $this->element('MeTools.paginator') ?>
</div>