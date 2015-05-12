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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
	$this->assign('title', __('List users'));
	$this->Library->datepicker('#created', ['format' => 'MM/YYYY', 'viewMode' => 'years']);
?>

<div class="users index">
	<?= $this->Html->h2(__('List users')) ?>
	<?= $this->Html->button(__d('me_cms', 'Add'), ['action' => 'add'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
	<?php echo $this->Form->createInline(NULL, ['class' => 'filter-form', 'type' => 'get']); ?>
		<fieldset>
			<?php
				echo $this->Form->legend(__d('me_cms', 'Filter'));
				echo $this->Form->input('username', [
					'default'		=> $this->request->query('username'),
					'placeholder'	=> __d('me_cms', 'username'),
					'size'			=> 16
				]);
				echo $this->Form->input('status', [
					'default'	=> $this->request->query('status'),
					'empty'		=> sprintf('-- %s --', __d('me_cms', 'all status')),
					'options'	=> [
						'active'	=> __d('me_cms', 'Only active'),
						'pending'	=> __d('me_cms', 'Only pending'),
						'banned'	=> __d('me_cms', 'Only banned')
					]
				]);
				echo $this->Form->input('group', [
					'default'	=> $this->request->query('group'),
					'empty'		=> sprintf('-- %s --', __d('me_cms', 'all groups'))
				]);
				echo $this->Form->datepicker('created', [
					'data-date-format'	=> 'YYYY-MM',
					'default'			=> $this->request->query('created'),
					'placeholder'		=> __d('me_cms', 'month'),
					'size'				=> 5
				]);
				echo $this->Form->submit(NULL, ['icon' => 'search']);
			?>
		</fieldset>
	<?php echo $this->Form->end(); ?>
	
    <table class="table table-hover">
		<thead>
			<tr>				
				<th><?php echo $this->Paginator->sort('username', __d('me_cms', 'Username')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('full_name', __d('me_cms', 'Name')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('email', __d('me_cms', 'Email')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('group_id', __d('me_cms', 'Group')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('post_count', __d('me_cms', 'Posts')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('created', __d('me_cms', 'Date')) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($users as $user): ?>
				<tr>
					<td>
						<?php
							$title = $this->Html->link($user->username, array('action' => 'view', $user->id));

							//If the user is banned
							if($user->banned)
								$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Banned'), ['class' => 'text-danger']));
							//Else, if the user is not active (pending)
							elseif(!$user->active)
								$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Pending'), ['class' => 'text-warning']));

							echo $this->Html->strong($title);

							$actions = [
								$this->Html->link(__d('me_cms', 'View'), ['action' => 'view', $user->id], ['icon' => 'eye']),
								$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $user->id], ['icon' => 'pencil'])
							];

							//Only admins can activate accounts and delete users
							if($this->Auth->isGroup('admin')) {
								//If the user is not active (pending)
								if(!$user->active)
									$actions[] = $this->Form->postLink(__d('me_cms', 'Activate'), ['action' => 'activate', $user->id], ['icon' => 'user-plus', 'confirm' => __d('me_cms', 'Are you sure you want to activate this account?')]);

								$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $user->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
							}

							echo $this->Html->ul($actions, ['class' => 'actions']);
						?>
					</td>
					<td class="text-center"><?= $user->full_name ?></td>
					<td class="text-center"><?= $this->Html->link($user->email, sprintf('mailto:%s', $user->email)) ?></td>
					<td class="text-center"><?= $user->group->label ?></td>
					<td class="min-width text-center"><?= $user->post_count ?></td>
					<td class="min-width text-center"><?= $user->created->i18nFormat(config('main.datetime.long')) ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?= $this->element('MeTools.paginator') ?>
</div>