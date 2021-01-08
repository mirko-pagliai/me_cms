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

//Default options (with File engine)
$options = [
    'className' => 'File',
    'duration' => '+999 days',
    'path' => CACHE . 'me_cms' . DS,
    'prefix' => '',
    'mask' => 0777,
];

foreach (['default', 'admin', 'banners', 'pages', 'photos', 'posts', 'static_pages', 'users'] as $name) {
    $Cache[$name] = ['path' => CACHE . 'me_cms' . DS . $name] + $options;
}

return compact('Cache');
