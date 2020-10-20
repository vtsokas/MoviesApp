<?php
namespace Movie\Model;

use Zend\Db\TableGateway\TableGateway;

class MovieTable
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

    public function getMovie($id)
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

    public function saveMovie(Movie $movie)
    {     
        // Get data as array
        $data = array(
            'title'  => $movie->title,
            'startDate' => $movie->startDate,
            'endDate'  => $movie->endDate,
            'cinemaName' => $movie->cinemaName,
            'category'  => $movie->category
        );
        // Decide whether to add or update
        $id = (int) $movie->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getMovie($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Movie id does not exist');
            }
        }
    }

    public function deleteMovie($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}