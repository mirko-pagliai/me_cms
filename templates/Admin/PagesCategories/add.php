<?php
declare(strict_types=1);
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
$this->extend('/Admin/common/form');
$this->assign('title', $title = __d('me_cms', 'Add pages category'));
$this->Library->slugify();
?>

<?= $this->Form->create($category); ?>
<div class="row">
    <div class="col-lg-3 order-12">
        <div class="float-form">
        <?php
        if (!empty($categories)) {
            echo $this->Form->control('parent_id', [
                'help' => I18N_BLANK_TO_CREATE_CATEGORY,
                'label' => I18N_PARENT_CATEGORY,
                'options' => $categories,
            ]);
        }
        ?>
        </div>
    </div>
    <fieldset class="col-lg-9">
    <?php
    echo $this->Form->control('title', [
        'id' => 'title',
        'label' => I18N_TITLE,
    ]);
    echo $this->Form->control('slug', [
        'help' => I18N_HELP_SLUG,
        'id' => 'slug',
        'label' => I18N_SLUG,
    ]);
    echo $this->Form->control('description', [
        'label' => I18N_DESCRIPTION,
        'rows' => 3,
    ]);
    ?>
    </fieldset>
</div>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
