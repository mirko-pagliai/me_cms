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
namespace MeCms\Model\Table;

use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * UsersGroups model
 */
class UsersGroupsTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'users';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['label'], I18N_VALUE_ALREADY_USED));
        $rules->add($rules->isUnique(['name'], I18N_VALUE_ALREADY_USED));

        return $rules;
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users_groups');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->hasMany('Users', ['className' => ME_CMS . '.Users'])
            ->setForeignKey('group_id');

        $this->addBehavior('Timestamp');

        $this->_validatorClass = '\MeCms\Model\Validation\UsersGroupValidator';
    }
}
