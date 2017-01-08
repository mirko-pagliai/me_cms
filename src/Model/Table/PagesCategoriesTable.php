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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * PagesCategories model
 * @property \Cake\ORM\Association\BelongsTo $ParentPagesCategories
 * @property \Cake\ORM\Association\HasMany $ChildPagesCategories
 */
class PagesCategoriesTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'pages';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'Parents'));
        $rules->add($rules->isUnique(['slug'], __d('me_cms', 'This value is already used')));
        $rules->add($rules->isUnique(['title'], __d('me_cms', 'This value is already used')));

        return $rules;
    }

    /**
     * "Active" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        $query->where([sprintf('%s.page_count >', $this->alias()) => 0]);

        return $query;
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('pages_categories');
        $this->displayField('title');
        $this->primaryKey('id');

        $this->belongsTo('Parents', [
            'className' => 'MeCms.PagesCategories',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('Childs', [
            'className' => 'MeCms.PagesCategories',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('Pages', [
            'className' => 'MeCms.Pages',
            'foreignKey' => 'category_id',
        ]);

        $this->addBehavior('Timestamp');
        $this->addBehavior('MeCms.Tree');
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\PostsCategoryValidator
     */
    public function validationDefault(\Cake\Validation\Validator $validator)
    {
        return new \MeCms\Model\Validation\PagesCategoryValidator;
    }
}
