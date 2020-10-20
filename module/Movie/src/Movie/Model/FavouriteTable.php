<?php
namespace Movie\Model;

use Zend\Db\TableGateway\TableGateway;

class FavouriteTable
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

    public function getFavouriteMovieIds($query = array())
    {
        $resultSet = $this->tableGateway->select($query);
        $arr = array();
        $res = $resultSet->toArray();
        foreach ($res as $fav)
        {
            $arr[] = $fav['movieId'];
        }
        return $arr;
    }

    public function getFavourite($id)
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

    public function saveFavourite(Favourite $fav)
    {     
        // Get data as array
        $data = array(
            'movieId'  => $fav->movieId,
            'userId' => $fav->userId
        );
        // Decide whether to add or update
        $id = (int) $fav->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getFavourite($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Fav id does not exist');
            }
        }
    }

    public function deleteFavourite($q)
    {
        $this->tableGateway->delete($q);
    }
}