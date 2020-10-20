<?php

namespace Movie\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\AdapterInterface;
use Movie\Model\Movie;
use Movie\Model\MovieTable;
use Movie\Form\MovieForm;
use User\Model\UserTable;
use Cinema\Model\CinemaTable;
use Movie\Model\FavouriteTable;
use Movie\Model\Favourite;

class MovieController extends AbstractActionController
{
    protected $table;
    protected $userService;
    protected $cinemaTable;
    protected $favouriteTable;

    public function __construct(MovieTable $table, UserTable $userTable, CinemaTable $cinemaTable, FavouriteTable $favouriteTable)
    {
        $this->table = $table;
        $this->userService = $userTable;
        $this->cinemaTable = $cinemaTable;
        $this->favouriteTable = $favouriteTable;
    }

    public function indexAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_view'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // If cinema owner, get his/hers cinemas and filter the movies
        $query = array();
        if ($this->userService->getUserRole() == 'cinema_owner')
        {
            $cinemas = $this->cinemaTable->fetchAll(
                array('ownerId' => +$this->userService->getUserId()))->toArray();

            $arr = array();
            foreach ($cinemas as $cinema) 
            {
                $arr[] = $cinema['name'];
            }

            $query = array('cinemaName' => $arr);
        }
        // Return the corresponding view. Include a list of movies
        return new ViewModel(array(
            'movies' => $this->table->fetchAll($query),
            'favourites' => $this->favouriteTable->getFavouriteMovieIds(array('userId' => $this->userService->getUserId()))
        ));
    }

    public function addAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_add'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Initialize the form
        $form = new MovieForm();
        $form->get('submit')->setValue('Add');
        // If cinema owner, get his/hers cinemas and restrict the choices
        $q = array();
        if ($this->userService->getUserRole() == 'cinema_owner')
        {
            $q = array('ownerId' => +$this->userService->getUserId());
        }
        $cinemas = $this->cinemaTable->fetchAll($q)->toArray();

        $arr = array();
        foreach ($cinemas as $cinema) 
        {
            $arr[$cinema['name']] = $cinema['name'];
        }
        $form->get('cinemaName')->setValueOptions($arr);

        $request = $this->getRequest();
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $movie = new Movie();
        $form->setInputFilter($movie->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return ['form' => $form];
        }
        // Modify the object, save and return to the list
        $movie->exchangeArray($form->getData());
        $this->table->saveMovie($movie);
        return $this->redirect()->toRoute('movie');
    }

    public function editAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_edit'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to Add if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('movie', ['action' => 'add']);
        }
        // Retrieve the movie with the specified id. Doing so raises
        // an exception if the movie is not found, which should result
        // in redirecting to the landing page.
        try {
            $movie = $this->table->getMovie($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('movie', ['action' => 'index']);
        }
        // Initialize the form
        $form = new MovieForm();
        $form->bind($movie);
        $form->get('submit')->setAttribute('value', 'Edit');
        // If cinema owner, get his/hers cinemas and restrict the choices
        $q = array();
        if ($this->userService->getUserRole() == 'cinema_owner')
        {
            $q = array('ownerId' => +$this->userService->getUserId());
        }
        $cinemas = $this->cinemaTable->fetchAll($q)->toArray();

        $arr = array();
        foreach ($cinemas as $cinema) 
        {
            $arr[$cinema['name']] = $cinema['name'];
        }
        $form->get('cinemaName')->setValueOptions($arr);
        // If it is a GET request it means the user is navigating 
        // to the Add form. So just return the form
        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }
        // Here we know the user submitted the form. Initialize an
        // object and bind it to the form data
        $form->setInputFilter($movie->getInputFilter());
        $form->setData($request->getPost());
        // Validate against the input filters
        if (! $form->isValid()) {
            return $viewData;
        }
        // Modify the object, save and return to the list
        $this->table->saveMovie($movie);
        return $this->redirect()->toRoute('movie', ['action' => 'index']);
    }

    public function deleteAction()
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_delete'))
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
                $this->table->deleteMovie($id);
            }
            // Redirect to list of movies
            return $this->redirect()->toRoute('movie');
        }
        // If here, then the user is navigating to the delete form
        return [
            'id'    => $id,
            'movie' => $this->table->getMovie($id),
        ];
    }

    public function favouriteAction() 
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_favourite'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to list if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('movie');
        }
        // Retrieve the movie with the specified id. Doing so raises
        // an exception if the movie is not found, which should result
        // in redirecting to the landing page.
        try {
            $movie = $this->table->getMovie($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('movie', ['action' => 'index']);
        }
        $favs = $this->favouriteTable->fetchAll(array('movieId' => $id, 'userId' => $this->userService->getUserId()));
        if (sizeof($favs->toArray()) == 0) 
        {
            $fav = new Favourite();
            $fav->id = 0;
            $fav->movieId = $id;
            $fav->userId = $this->userService->getUserId();
            $this->favouriteTable->saveFavourite($fav);
        }

        return $this->redirect()->toRoute('movie');
    }

    public function unfavouriteAction() 
    {
        // Authorize the user. Redirect to login if not authorized
        if (!$this->userService->isAllowed('movie_favourite'))
        {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        // Get the id from the url and redirect to list if not exists
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('movie');
        }
        // Retrieve the movie with the specified id. Doing so raises
        // an exception if the movie is not found, which should result
        // in redirecting to the landing page.
        try {
            $movie = $this->table->getMovie($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('movie', ['action' => 'index']);
        }
        $this->favouriteTable->deleteFavourite(array('movieId' => $id, 'userId' => $this->userService->getUserId()));

        return $this->redirect()->toRoute('movie');
    }

}