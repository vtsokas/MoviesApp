<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\AdapterInterface;
use User\Model\User;
use User\Model\UserTable;
use User\Form\UserForm;
use User\Form\LoginForm;

class UserController extends AbstractActionController
{
    protected $table;

    public function __construct(UserTable $table)
    {
        $this->table = $table;
    }

    public function loginAction()
    {
        // Intitialize a form
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');

        $request = $this->getRequest();
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $loginData = $request->getPost();
        // Call the service method to login
        $this->table->login($loginData->username, $loginData->password);

        return $this->redirect()->toRoute('home');
    }

    public function logoutAction() 
    {
        // Logout and return to the login page
        $this->table->logout();
        return $this->redirect()->toRoute('user');
    }

    public function indexAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->table->isAllowed('user_view'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Return the corresponding view. Include a list of movies
        return new ViewModel(array(
            'users' => $this->table->fetchAll(),
        ));
    }

    public function addAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->table->isAllowed('user_add'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Initialize the form
        $form = new UserForm();
        $form->get('submit')->setValue('Add');
        
        if ($this->table->getUserRole() == 'admin')
        {
            $form->get('role')->setValueOptions(array('admin' => 'admin', 'user' => 'user', 'cinema_owner' => 'cinema_owner'));
        } else 
        {
            $form->get('role')->setValueOptions(array('user' => 'user', 'cinema_owner' => 'cinema_owner'));
        }

        $request = $this->getRequest();
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $user = new User();
        $form->setInputFilter($user->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return ['form' => $form];
        }
        // Modify the object, save and redirect
        $user->exchangeArray($form->getData());
        if ($user->isConfirmed == null) $user->isConfirmed = false;
        $this->table->saveUser($user);
        /////////
        // TODO: Redirect according to the role?
        ////////
        return $this->redirect()->toRoute('user');
    }

    public function editAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->table->isAllowed('user_edit'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to Add if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('user', ['action' => 'add']);
        }
        // Retrieve the movie with the specified id. Doing so raises
        // an exception if the movie is not found, which should result
        // in redirecting to the landing page.
        try {
            $user = $this->table->getUser($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('user', ['action' => 'index']);
        }
        // Initialize the form
        $form = new UserForm();
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Edit');

        if ($this->table->getUserRole() == 'admin')
        {
            $form->get('role')->setValueOptions(array('admin' => 'admin', 'user' => 'user', 'cinema_owner' => 'cinema_owner'));
        } else 
        {
            $form->get('role')->setValueOptions(array('user' => 'user', 'cinema_owner' => 'cinema_owner'));
        }

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        if (! $request->isPost()) {
            return $viewData;
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $form->setInputFilter($user->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return $viewData;
        }
        // Modify the object, save and return to the list
        $this->table->saveUser($user);
        /////////
        // TODO: Redirect according to the role?
        ////////
        return $this->redirect()->toRoute('user', ['action' => 'index']);
    }

    public function deleteAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->table->isAllowed('user_delete'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to list if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('movie');
        }

        $request = $this->getRequest();
        // If it is POST then the user is submitting
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            // If said YES, then delete
            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deleteUser($id);
            }
            // Redirect to list of users
            return $this->redirect()->toRoute('user');
        }
        // If here, then the user is navigating to the delete form
        return [
            'id'    => $id,
            'user' => $this->table->getUser($id),
        ];
    }

}