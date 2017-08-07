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
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * PhotosAlbums controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $PhotosAlbums
 */
class PhotosAlbumsController extends AppController
{
    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *   the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can delete albums
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        return true;
    }

    /**
     * Lists albums
     * @return void
     */
    public function index()
    {
        $this->paginate['order'] = ['title' => 'ASC'];

        $albums = $this->paginate($this->PhotosAlbums->find());

        $this->set(compact('albums'));
    }

    /**
     * Adds photos album
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $album = $this->PhotosAlbums->newEntity();

        if ($this->request->is('post')) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->request->getData());

            if ($this->PhotosAlbums->save($album)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('album'));
    }

    /**
     * Edits photos album
     * @param string $id Photos Album ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $album = $this->PhotosAlbums->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->request->getData());

            if ($this->PhotosAlbums->save($album)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('album'));
    }
    /**
     * Deletes photos album
     * @param string $id Photos Album ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $album = $this->PhotosAlbums->get($id);

        //Before deleting, it checks if the album has some photos
        if (!$album->photo_count) {
            $this->PhotosAlbums->deleteOrFail($album);

            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
