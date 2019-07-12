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
namespace MeCms\Model\Entity;

use Cake\Utility\Hash;
use MeCms\ORM\PostAndPageEntity;

/**
 * Post entity
 * @property int $id
 * @property int $category_id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $subtitle
 * @property string $text
 * @property string $preview
 * @property int $priority
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property bool $active
 * @property \MeCms\Model\Entity\PostsCategory $category
 * @property \MeCms\Model\Entity\User $user
 * @property \MeCms\Model\Entity\Tag[] $tags
 */
class Post extends PostAndPageEntity
{
    /**
     * Virtual fields that should be exposed
     * @var array
     */
    protected $_virtual = ['plain_text', 'tags_as_string'];

    /**
     * Gets tags as string, separated by a comma and a space (virtual field)
     * @return string|null
     */
    protected function _getTagsAsString()
    {
        if (empty($this->_properties['tags'])) {
            return null;
        }

        return implode(', ', Hash::extract($this->_properties['tags'], '{*}.tag'));
    }
}
