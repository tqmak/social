<?php

declare(strict_types=1);

namespace App\Controller;


use Cake\Mailer\Mailer;
use Cake\View\View;
use Cake\Mailer\TransportFactory;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Composer\Script\ScriptEvents;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function initialize(): void
    {
        $this->Model = $this->loadModel('Post');
        $this->Model = $this->loadModel('Comment');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
    }

    public function index()
    {
        $user = $this->Authentication->getIdentity();
        if ($user->role == 0) {
            $users = $this->paginate($this->Users);
            $this->set(compact('user'));
            $this->set(compact('users'));
        }
    }

    public function home()
    {
        $this->paginate = [
            'contain' => ['Users'],
        ];
        $post = $this->paginate($this->Post);

        $this->set(compact('post'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $result = $this->Authentication->getIdentity();
        if ($result->role == 0) {
            $user = $this->Users->get($id, [
                'contain' => ['Post'],
            ]);
        } else if ($result->role == 1) {
            $user = $this->Users->get($result->id, [
                'contain' => ['Post'],
            ]);
        }
        $this->set(compact('user'));
    }
    // -----------------------------post view---------------------------------------
    public function postview($id = null, $userid = null)
    {
        $post = $this->Post->get($id, [
            'contain' => ['Users', 'Comment'],
        ]);
        $post['userid'] = $userid;

        $comment = $this->Comment->newEmptyEntity();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['post_id'] = $id;
            $comment = $this->Comment->patchEntity($comment, $data);
            if ($this->Comment->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));
                return $this->redirect(['action' => 'postview', $id, $userid]);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }

        $this->set(compact('post', 'comment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    // ---------------------------------registration page---------------------------------
    public function add()
    {
        // $this->viewBuilder()->setLayout('mydefault');
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $productImage = $this->request->getData("image");
            $fileName = $productImage->getClientFilename();
            $fileSize = $productImage->getSize();
            $data["image"] = $fileName;
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $hasFileError = $productImage->getError();

                if ($hasFileError > 0) {
                    $data["image"] = "";
                } else {
                    $fileType = $productImage->getClientMediaType();

                    if ($fileType == "image/png" || $fileType == "image/jpeg" || $fileType == "image/jpg") {
                        $imagePath = WWW_ROOT . "img/" . $fileName;
                        $productImage->moveTo($imagePath);
                        $data["image"] = $fileName;
                    }
                }
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }
    // -----------------------------------here we are adding the post-------------------------------------
    public function postadd($userid)
    {
        $post = $this->Post->newEmptyEntity();
        $post['userid'] = $userid;
        if ($this->request->is('post')) {

            $data = $this->request->getData();
            $productImage = $this->request->getData("post_image");
            $fileName = $productImage->getClientFilename();
            $fileSize = $productImage->getSize();
            $data["post_image"] = $fileName;
            $data['users_id'] = $userid;
            $post = $this->Post->patchEntity($post, $data);
            if ($this->Post->save($post)) {

                $hasFileError = $productImage->getError();

                if ($hasFileError > 0) {
                    $data["post_image"] = "";
                } else {
                    $fileType = $productImage->getClientMediaType();

                    if ($fileType == "image/png" || $fileType == "image/jpeg" || $fileType == "image/jpg") {
                        $imagePath = WWW_ROOT . "img/" . $fileName;
                        $productImage->moveTo($imagePath);
                        $data["post_image"] = $fileName;
                    }
                }

                $this->Flash->success(__('The post has been saved.'));

                return $this->redirect(['action' => 'view', $userid]);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $users = $this->Post->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('post', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        $fileName2 = $user['image'];

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $productImage = $this->request->getData("image");
            $fileName = $productImage->getClientFilename();
            if ($fileName == '') {
                $fileName = $fileName2;
            }

            $data["image"] = $fileName;
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {

                $hasFileError = $productImage->getError();
                if ($hasFileError > 0) {
                    $data["image"] = "";
                } else {
                    $fileType = $productImage->getClientMediaType();

                    if ($fileType == "image/png" || $fileType == "image/jpeg" || $fileType == "image/jpg") {
                        $imagePath = WWW_ROOT . "img/" . $fileName;
                        $productImage->moveTo($imagePath);
                        $data["image"] = $fileName;
                    }
                }

                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }
    // ---------------------------------------here we are editing the post-------------------------------
    public function postedit($id = null, $userid = null)
    {
        $post = $this->Post->get($id, [
            'contain' => [],
        ]);
        $post['userid'] = $userid;
        $fileName2 = $post['post_image'];

        // echo '<pre>';print_r($post);die;
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $productImage = $this->request->getData("post_image");
            $fileName = $productImage->getClientFilename();
            if ($fileName == '') {
                $fileName = $fileName2;
            }

            $data["post_image"] = $fileName;
            $user = $this->Users->patchEntity($post, $data);
            if ($this->Post->save($post)) {

                $hasFileError = $productImage->getError();
                if ($hasFileError > 0) {
                    $data["post_image"] = "";
                } else {
                    $fileType = $productImage->getClientMediaType();

                    if ($fileType == "image/png" || $fileType == "image/jpeg" || $fileType == "image/jpg") {
                        $imagePath = WWW_ROOT . "img/" . $fileName;
                        $productImage->moveTo($imagePath);
                        $data["post_image"] = $fileName;
                    }
                }

                $this->Flash->success(__('The post has been saved.'));

                return $this->redirect(['controller' => 'users', 'action' => 'view', $userid]);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $this->set(compact('post'));
    }
    // ------------------------------------here we are editing the comments-----------------------------

    public function commentedit($id = null, $postid = null, $userid = null)
    {
        $comment = $this->Comment->get($id, [
            'contain' => [],
        ]);
        $comment['postid'] = $postid;
        $comment['userid'] = $userid;
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comment->patchEntity($comment, $this->request->getData());
            if ($this->Comment->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'postview', $postid, $userid]);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $this->set(compact('comment'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    // --------------------------------------------here we are deleting the post---------------------------
    public function postdelete($id = null, $userid = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $post = $this->Post->get($id);
        if ($this->Post->delete($post)) {
            $this->Flash->success(__('The post has been deleted.'));
        } else {
            $this->Flash->error(__('The post could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'users', 'action' => 'view', $userid]);
    }
    // ----------------------------------------------here we are deleting the comments------------------------
    public function commentdelete($id = null, $postid = null, $userid = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comment->get($id);
        if ($this->Comment->delete($comment)) {
            $this->Flash->success(__('The comment has been deleted.'));
        } else {
            $this->Flash->error(__('The comment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'postview', $postid, $userid]);
    }
    // ------here we giving the permissions to page which can be accessed without login the session------
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add']);
    }

    public function login() // login function//
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            if ($user->role == 0) {
                $redirect = $this->request->getQuery('redirect', ['controller' => 'Users', 'action' => 'index']);
            } else if ($user->role == 1) {
                $redirect = $this->request->getQuery('redirect', ['controller' => 'Users', 'action' => 'view']);
            }
            $this->Flash->success(__('You have successfully logged In.'));
            return $this->redirect($redirect);
        }
        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Please enter your username or password.'));
        }
    }

    public function logout()  //logout function//
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success(__('You have successfully logged out.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    public function forgot() // forgot password function//
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $users = TableRegistry::get("Users");
            $user = $users->find('all')->where(['email' => $email])->first();
            if ($total = $users->find('all')->where(['email' => $email])->count() == 0) {
                $this->Flash->error(__('Email is not registered in system'));
            } else {
                if ($user) {
                    $token = rand(10000, 100000);
                    $user->token = $token;
                    if ($users->save($user)) {
                        $mailer = new Mailer('default');
                        $mailer->setTransport('gmail');
                        $mailer->setFrom(['abc@gmail.com' => 'Akshat']);
                        $mailer->setTo($email);
                        $mailer->setEmailFormat('html');
                        $mailer->setSubject('Reset password link');
                        $mailer->deliver('<a href="http://localhost:8765/users/reset?token=' . $token . '">Click here</a> for reset your password');

                        $this->Flash->success(__('Reset email send successfully.'));
                    }
                } else {
                    $this->Flash->error(__('Please enter valid credential..'));
                }
            }
        }

        $this->set(compact('user'));
    }

    public function reset()  //reseting the password//
    {
        $user = $this->Users->newEmptyEntity();
        $token = $_REQUEST['token'];
        $users = TableRegistry::get("Users");
        $result = $users->find('all')->where(['token' => $token])->first();
        if ($result) {
            if ($this->request->is('post')) {
                $user = $this->Users->patchEntity($user, $this->request->getData());
                $password = $this->request->getData('password');
                $res1 = preg_match('(^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]*).{8,}$)', $password);
                $confirm_password = $this->request->getData('confirm_password');
                if ($res1 == 1 && $confirm_password == $password) {
                    $result->password = $password;
                    $result->token = NULL;
                    if ($users->save($result)) {
                        $this->Flash->success(__('Password updated successfully.'));
                        return $this->redirect(['action' => 'login']);
                    }
                }
                $this->Flash->error(__('Please enter valid password'));
            }
        } else {
            return $this->redirect(['action' => 'login']);
        }

        $this->set(compact('user'));
    }
    // here we are getting the pagination how much data show be display in index page at a time.//
    public $paginate = [
        'limit' => 5
    ];
}
