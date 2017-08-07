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
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Add users group'));
?>

<?= $this->Form->create($group); ?>
<fieldset>
    <?php
        echo $this->Form->control('name', [
            'label' => I18N_NAME,
        ]);
        echo $this->Form->control('label', [
            'label' => I18N_LABEL,
        ]);
        echo $this->Form->control('description', [
            'label' => I18N_DESCRIPTION,
            'rows' => 3,
            'type' => 'textarea',
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>