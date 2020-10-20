<?php
 namespace Movie\Model;

 class Favourite
 {
     public $id;
     public $movieId;
     public $userId;

     public function exchangeArray($data)
     {
         $data = array_change_key_case ($data, 0);
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->movieId = (!empty($data['movieid'])) ? $data['movieid'] : null;
         $this->userId  = (!empty($data['userid'])) ? $data['userid'] : null;
     }

    public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'movieId' => $this->movieId,
            'userId'  => $this->userId
        ];
    }
 }