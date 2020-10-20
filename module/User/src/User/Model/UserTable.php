<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;

class UserTable
{
    protected $tableGateway;
    protected $sessionContainer;

    protected $permissions = array(
        'admin' => array('*'),
        'unauthorized' => array('user_add'),
        'cinema_owner' => array(
            'movie_view', 'movie_add', 'movie_edit', 'movie_delete', 
            'cinema_view', 'cinema_add', 'cinema_edit', 'cinema_delete',
            'movie_favourite'),
        'user' => array('movie_view', 'user_add', 'user_edit','movie_favourite')
    );

    public function __construct(TableGateway $tableGateway, Container $sessionContainer)
    {
        $this->tableGateway = $tableGateway;
        $this->sessionContainer = $sessionContainer;
    }

    /////////////
    // A U T H //
    /////////////

    public function login($username, $password) 
    {
        $rowset = $this->tableGateway->select(array(
            'username' => $username, 
            'password' => $password
        ));
        $row = $rowset->current();

        if ($row != null) {
            $this->sessionContainer->userId = $row->id;
            $this->sessionContainer->username = $username;
            $this->sessionContainer->password = $password;
            $this->sessionContainer->role = $row->role;
        } else {
            $this->sessionContainer->role = 'unauthorized';
        }
    }

    public function logout() 
    {
        $this->sessionContainer->userId = null;
        $this->sessionContainer->username = null;
        $this->sessionContainer->password = null;
        $this->sessionContainer->role = 'unauthorized';
    }

    public function getUserRole() 
    {
        return $this->sessionContainer->role;
    }

    public function getUserInfo()
    {
        return $this->sessionContainer->username;
    }

    public function getUserId()
    {
        return $this->sessionContainer->userId;
    }
    
    public function isAllowed($resource) 
    {
        if ($this->getUserRole() == null) $this->sessionContainer->role = 'unauthorized';
        $permissions = $this->permissions[$this->getUserRole()];
        if (in_array('*', $permissions) || in_array($resource, $permissions)) 
        {
            return true;
        }

        return false;
    }

    /////////////
    // C R U D //
    /////////////

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function fetchAllAsOptions()
    {
        $resultSet = $this->tableGateway->select();
        $options = array();
        foreach ($resultSet as $row) 
        {
            $options[$row->id] = $row->username;
        }
        return $options;
    }

    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveUser(User $user)
    {     
        $data = array(
            'name'  => $user->name,
            'surname' => $user->surname,
            'username'  => $user->username,
            'password' => $user->password,
            'email'  => $user->email,
            'role' => $user->role,
            'isConfirmed'  => $user->isConfirmed
        );

        $id = (int) $user->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('User id does not exist');
            }
        }
    }

    public function deleteUser($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}