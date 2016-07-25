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

<?php $this->extend('/Admin/Common/Photos/index'); ?>
    
<div class='clearfix'>
    <?php foreach($photos as $photo): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="photo-box">
                <div class="photo-title">
                    <?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?>
                </div>
                <div class="photo-album">
                    <?= __d('me_cms', 'Album') ?>: 
                    <?= $this->Html->link($photo->album->title, ['?' => ['album' => $photo->album->id]], ['title' => __d('me_cms', 'View items that belong to this category')]) ?>
                </div>
                <div class="photo-created">
                    (<?= $photo->created->i18nFormat(config('main.datetime.long')) ?>)
                </div>
                <div class="photo-image">
                    <?= $this->Thumb->image($photo->path, ['side' => 400, 'force' => TRUE]) ?>
                </div>

                <?php
                    $actions = [
                        $this->Html->link(NULL, ['action' => 'edit', $photo->id], ['icon' => 'pencil', 'title' => __d('me_cms', 'Edit')]),
                        $this->Html->link(NULL, ['action' => 'download', $photo->id], ['icon' => 'download', 'title' => __d('me_cms', 'Download')]),
                    ];

                    //Only admins can delete photos
                    if($this->Auth->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(NULL, ['action' => 'delete', $photo->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'title' => __d('me_cms', 'Delete'), 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
                    }
                        
                    //If the photo is active
                    if($photo->active) {
                        $actions[] = $this->Html->link(NULL, ['_name' => 'photo', 'slug' => $photo->album->slug, 'id' => $photo->id], ['icon' => 'external-link', 'target' => '_blank', 'title' => __d('me_cms', 'Open')]);
                    }
                    else {
                        $actions[] = $this->Html->link(NULL, ['_name' => 'photos_preview', $photo->id], ['icon' => 'external-link', 'target' => '_blank', 'title' => __d('me_cms', 'Preview')]);
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>