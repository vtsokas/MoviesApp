<?php
namespace Cinema\Model;

use Zend\Db\TableGateway\TableGateway;

class CinemaTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($query = array())
    {
        $resultSet = $this->tableGateway->select($query);
        return $resultSet;
    }

    public function getCinema($id)
    {
        $id  = (int) $id;
        // We send the query parameters as an array
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveCinema(Cinema $cinema)
    {     
        // Get data as array
        $data = array(
            'ownerId'  => $cinema->ownerId,
            'name' => $cinema->name
        );
        // Decide whether to add or update
        $id = (int) $cinema->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCinema($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Cinema id does not exist');
            }
        }
    }

    public function deleteCinema($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}