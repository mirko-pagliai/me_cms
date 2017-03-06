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
namespace MeCms\Model\Validation;

use MeCms\Model\Validation\AppValidator;
use MeCms\Model\Validation\TagValidatorTrait;

/**
 * Tag validator class
 */
class TagValidator extends AppValidator
{
    use TagValidatorTrait;

    /**
     * Construct.
     *
     * Adds some validation rules.
     * @uses MeCms\Model\Validation\AppValidator::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        //Tag
        $this->add('tag', [
            'validTagLength' => [
                'last' => true,
                'message' => __d('me_cms', 'Must be between {0} and {1} chars', 3, 30),
                'rule' => [$this, 'validTagLength'],
            ],
            'validTagChars' => [
                'message' => sprintf(
                    '%s: %s',
                    __d('me_cms', 'Allowed chars'),
                    __d('me_cms', 'lowercase letters, numbers, space')
                ),
                'rule' => [$this, 'validTagChars'],
            ],
        ]);
    }
}
