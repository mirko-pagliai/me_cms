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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use DatabaseBackup\Utility\BackupImport;
use DatabaseBackup\Utility\BackupManager;
use MeCms\Controller\AppController;
use MeCms\Form\BackupForm;

/**
 * Backups controller
 */
class BackupsController extends AppController
{
    /**
     * @var \DatabaseBackup\Utility\BackupManager
     */
    public $BackupManager;

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        $this->BackupManager = new BackupManager;
    }

    /**
     * Internal method to get a filename. It decodes the filename and adds the
     *  backup target directory
     * @param string $filename Filename
     * @return string
     * @since 2.18.3
     */
    protected function getFilename($filename)
    {
        return Configure::read(DATABASE_BACKUP . '.target') . DS . urldecode($filename);
    }

    /**
     * Lists backup files
     * @return void
     * @uses DatabaseBackup\Utility\BackupManager::index()
     */
    public function index()
    {
        $backups = collection($this->BackupManager->index())
            ->map(function ($backup) {
                $backup->slug = urlencode($backup->filename);

                return $backup;
            });

        $this->set(compact('backups'));
    }

    /**
     * Adds a backup file
     * @return \Cake\Network\Response|null|void
     * @see MeCms\Form\BackupForm
     * @see MeCms\Form\BackupForm::execute()
     */
    public function add()
    {
        $backup = new BackupForm;

        if ($this->request->is('post')) {
            //Creates the backup
            if ($backup->execute($this->request->getData())) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('backup'));
    }

    /**
     * Deletes a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses DatabaseBackup\Utility\BackupManager::delete()
     * @uses getFilename()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->BackupManager->delete($this->getFilename($filename));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Deletes all backup files
     * @return \Cake\Network\Response|null
     * @uses DatabaseBackup\Utility\BackupManager::deleteAll()
     */
    public function deleteAll()
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->BackupManager->deleteAll();

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Downloads a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response
     * @uses getFilename()
     */
    public function download($filename)
    {
        return $this->response->withFile($this->getFilename($filename), ['download' => true]);
    }

    /**
     * Restores a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses DatabaseBackup\Utility\BackupImport::filename()
     * @uses DatabaseBackup\Utility\BackupImport::import()
     * @uses getFilename()
     */
    public function restore($filename)
    {
        (new BackupImport)->filename($this->getFilename($filename))->import();

        //Clears the cache
        Cache::clearAll();

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sends a backup file via mail
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @since 2.18.3
     * @uses DatabaseBackup\Utility\BackupManager::send()
     * @uses getFilename()
     */
    public function send($filename)
    {
        $this->BackupManager->send($this->getFilename($filename), getConfig('email.webmaster'));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }
}
