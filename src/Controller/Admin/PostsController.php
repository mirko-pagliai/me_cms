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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use MeCms\Controller\AppController;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PostsCategoriesTable::getList()
     * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     * @uses MeCms\Model\Table\UsersTable::getActiveList()
     * @uses MeCms\Model\Table\UsersTable::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['add', 'edit'])) {
            $categories = $this->Posts->Categories->getTreeList();
            $users = $this->Posts->Users->getActiveList();
        } else {
            $categories = $this->Posts->Categories->getList();
            $users = $this->Posts->Users->getList();
        }

        if ($users->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create an user'));

            return $this->redirect(['controller' => 'Users', 'action' => 'index']);
        }

        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PostsCategories', 'action' => 'index']);
        }

        $this->set(compact('categories', 'users'));
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction(['add', 'edit'])) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     * @uses MeCms\Model\Table\Traits\IsOwnedByTrait::isOwnedBy()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can edit all posts.
        //Users can edit only their own posts
        if ($this->request->isEdit()) {
            return $this->Auth->isGroup(['admin', 'manager']) ||
                $this->Posts->isOwnedBy($this->request->getParam('pass.0'), $this->Auth->user('id'));
        }

        //Only admins and managers can delete posts
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        return true;
    }

    /**
     * Lists posts
     * @return void
     * @uses MeCms\Model\Table\PostsTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Posts->find()
            ->contain([
                'Categories' => ['fields' => ['id', 'title']],
                'Tags' => function ($q) {
                    return $q->order(['tag' => 'ASC']);
                },
                'Users' => ['fields' => ['id', 'first_name', 'last_name']],
            ]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $posts = $this->paginate($this->Posts->queryFromFilter($query, $this->request->getQuery()));

        $this->set(compact('posts'));
    }

    /**
     * Adds post
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function add()
    {
        $post = $this->Posts->newEntity();

        if ($this->request->is('post')) {
            //Only admins and managers can add posts on behalf of other users
            if (!$this->Auth->isGroup(['admin', 'manager'])) {
                $this->request = $this->request->withData('user_id', $this->Auth->user('id'));
            }

            $post = $this->Posts->patchEntity($post, $this->request->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('post'));
    }

    /**
     * Edits post
     * @param string $id Post ID
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function edit($id = null)
    {
        $post = $this->Posts->findById($id)
            ->contain(['Tags' => function ($q) {
                return $q->order(['tag' => 'ASC']);
            }])
            ->formatResults(function ($results) {
                return $results->map(function ($row) {
                    $row->created = $row->created->i18nFormat(FORMAT_FOR_MYSQL);

                    return $row;
                });
            })
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            //Only admins and managers can edit posts on behalf of other users
            if (!$this->Auth->isGroup(['admin', 'manager'])) {
                $this->request = $this->request->withData('user_id', $this->Auth->user('id'));
            }

            $post = $this->Posts->patchEntity($post, $this->request->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('post'));
    }
    /**
     * Deletes post
     * @param string $id Post ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->Posts->deleteOrFail($this->Posts->get($id));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }
}
