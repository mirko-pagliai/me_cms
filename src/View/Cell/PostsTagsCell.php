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
namespace MeCms\View\Cell;

use Cake\Cache\Cache;
use Cake\Network\Exception\InternalErrorException;
use Cake\View\Cell;

/**
 * PostsTags cell
 */
class PostsTagsCell extends Cell
{
    /**
     * Constructor. It loads the model
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     * @uses Cake\View\Cell::__construct()
     */
    public function __construct(
        \Cake\Network\Request $request = null,
        \Cake\Network\Response $response = null,
        \Cake\Event\EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel('MeCms.Tags');
    }

    /**
     * Internal method to get the font sizes
     * @param array|bool $style Style for tags. Array with `maxFont` and
     *  `minFont` keys or `false` to disable
     * @return array
     * @throws InternalErrorException
     */
    protected function _getFontSizes(array $style)
    {
        //Maximum and minimun font sizes we want to use
        $maxFont = empty($style['maxFont']) ? 40 : $style['maxFont'];
        $minFont = empty($style['minFont']) ? 12 : $style['minFont'];

        if ($maxFont <= $minFont) {
            throw new InternalErrorException(__d('me_cms', 'Invalid values'));
        }

        return [$maxFont, $minFont];
    }

    /**
     * Popular tags widgets
     * @param int $limit Limit
     * @param string $prefix Prefix for each tag. This works only with the cloud
     * @param string $render Render type (`cloud`, `form` or `list`)
     * @param bool $shuffle Shuffles tags
     * @param array|bool $style Style for tags. Array with `maxFont` and
     *  `minFont` keys or `false` to disable
     * @return void
     * @uses _getFontSizes()
     */
    public function popular(
        $limit = 10,
        $prefix = '#',
        $render = 'cloud',
        $shuffle = true,
        $style = ['maxFont' => 40, 'minFont' => 12]
    ) {
        //Returns on tags index
        if ($this->request->isUrl(['_name' => 'postsTags'])) {
            return;
        }

        //Sets the initial cache name
        $cache = sprintf('widget_tags_popular_%s', $limit);

        if ($style && is_array($style)) {
            //Maximum and minimun font sizes we want to use
            list($maxFont, $minFont) = $this->_getFontSizes($style);
            $style = true;

            //Updates the cache name
            $cache = sprintf('%s_max_%s_min_%s', $cache, $maxFont, $minFont);
        }

        //Tries to get from cache
        $tags = Cache::read($cache, $this->Tags->Posts->cache);

        if (empty($tags)) {
            $tags = $this->Tags->find()
                ->select(['tag', 'post_count'])
                ->limit($limit)
                ->order([
                    'post_count' => 'DESC',
                    'tag' => 'ASC',
                ])
                ->cache($cache, $this->Tags->Posts->cache)
                ->toArray();

            //Adds the proportional font size to each tag
            if ($render === 'cloud' && $style) {
                //Highest and lowest numbers of occurrences and their difference
                $maxCount = $tags[0]->post_count;
                $minCount = end($tags)->post_count;
                $diffCount = $maxCount - $minCount;

                foreach ($tags as $tag) {
                    if ($diffCount) {
                        $tag->size = round((($tag->post_count - $minCount) / $diffCount * ($maxFont - $minFont)) + $minFont);
                    } else {
                        $tag->size = $maxFont;
                    }
                }
            }

            Cache::write($cache, $tags, $this->Tags->Posts->cache);
        }

        if ($shuffle && $limit > 1) {
            shuffle($tags);
        }

        if ($render === 'form') {
            //Takes place here, because shuffle() re-indexes
            foreach ($tags as $k => $tag) {
                $tags[$tag->slug] = $tag;
                unset($tags[$k]);
            }
        }

        $this->set(compact('prefix', 'tags'));

        if (in_array($render, ['form', 'list'])) {
            $this->viewBuilder()->template(sprintf('popular_as_%s', $render));
        }
    }
}
