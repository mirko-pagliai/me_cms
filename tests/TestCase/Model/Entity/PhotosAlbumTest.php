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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use MeCms\Model\Entity\PhotosAlbum;

/**
 * PhotosAlbumTest class
 */
class PhotosAlbumTest extends TestCase
{
    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertEquals('MeCms\Model\Entity\PhotosAlbum', get_class(new PhotosAlbum));
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $entity = new PhotosAlbum();

        $this->assertFalse($entity->accessible('id'));
        $this->assertFalse($entity->accessible('photo_count'));
        $this->assertFalse($entity->accessible('modified'));
    }

    /**
     * Test for `_getPath()` method
     * @test
     */
    public function testPathGetMutator()
    {
        $entity = new PhotosAlbum();

        $this->assertNull($entity->path);

        $entity->id = 1;

        $this->assertEquals(PHOTOS . DS . '1', $entity->path);
    }
}