<?php

namespace Cinema\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\AdapterInterface;
use Cinema\Model\Cinema;
use Cinema\Model\CinemaTable;
use Cinema\Form\CinemaForm;
use User\Model\UserTable;

class CinemaController extends AbstractActionController
{
    protected $table;
    protected $userService;

    public function __construct(CinemaTable $table, UserTable $userTable)
    {
        $this->table = $table;
        $this->userService = $userTable;
    }

    public function indexAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('cinema_view'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // If cinema owner, get his/hers cinemas and filter the movies
        $query = array();
        if ($this->userService->getUserRole() == 'cinema_owner')
        {
            $query = array('ownerId' => +$this->userService->getUserId());
        }
        // Return the corresponding view. Include a list of Cinemas
        return new ViewModel(array(
            'cinemas' => $this->table->fetchAll($query),
            'users' => $this->userService->fetchAllAsOptions()
        ));
    }

    public function addAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('cinema_add'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Initialize the form
        $form = new CinemaForm();
        $form->get('submit')->setValue('Add');
        // If cinema owner allow add only to self
        if ($this->userService->getUserRole() == 'cinema_owner')
        {
            $opts = array();
            $opts[$this->userService->getUserId()] = 'You';
            $form->get('ownerId')->setValueOptions($opts);
        } 
        else 
        {
            $form->get('ownerId')->setValueOptions($this->userService->fetchAllAsOptions());
        }

        $request = $this->getRequest();
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $cinema = new Cinema();
        $form->setInputFilter($cinema->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return ['form' => $form];
        }var_dump($form->getData());
        // Modify the object, save and return to the list
        $cinema->exchangeArray($form->getData());
        
        //$cinema->ownerId = $form->getData()['ownerId'];
        $this->table->saveCinema($cinema);
        return $this->redirect()->toRoute('cinema');
    }

    public function editAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('cinema_edit'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to Add if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('cinema', ['action' => 'add']);
        }
        // Retrieve the Cinema with the specified id. Doing so raises
        // an exception if the Cinema is not found, which should result
        // in redirecting to the landing page.
        try {
            $cinema = $this->table->getCinema($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('cinema', ['action' => 'index']);
        }
        // Initialize the form
        $form = new CinemaForm();
        $form->bind($cinema);
        $form->get('submit')->setAttribute('value', 'Edit');
        $form->get('ownerId')->setValueOptions($this->userService->fetchAllAsOptions());
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $form->setInputFilter($cinema->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return $viewData;
        }
        // Modify the object, save and return to the list
        $this->table->saveCinema($cinema);
        return $this->redirect()->toRoute('cinema', ['action' => 'index']);
    }

    public function deleteAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('cinema_delete'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to list if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cinema');
        }

        $request = $this->getRequest();
        // If it is POST then the user is submitting
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            // If said YES, then delete
            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deleteCinema($id);
            }
            // Redirect to list of cinemas
            return $this->redirect()->toRoute('cinema');
        }
        // If here, then the user is navigating to the delete form
        return [
            'id'    => $id,
            'cinema' => $this->table->getCinema($id),
            'users' => $this->userService->fetchAllAsOptions()
        ];
    }

}