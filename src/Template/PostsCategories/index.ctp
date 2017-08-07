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
$this->extend('/Common/index');
$this->assign('title', $title = I18N_POSTS_CATEGORIES);

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add($title, ['_name' => 'postsCategories']);

$categories = $categories->map(function ($category) {
    return $this->Html->link($category->title, ['_name' => 'postsCategory', $category->slug]);
})->toList();

echo $this->Html->ul($categories, ['icon' => 'caret-right']);
