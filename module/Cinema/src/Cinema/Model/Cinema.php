<?php
 namespace Cinema\Model;

use DomainException;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

 class Cinema implements InputFilterAwareInterface
 {
     public $id;
     public $ownerId;
     public $ownerName;
     public $name;

     private $inputFilter;

     public function exchangeArray($data)
     {
         $data = array_change_key_case ($data, 0);
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->ownerId = (!empty($data['ownerid'])) ? $data['ownerid'] : null;
         $this->name  = (!empty($data['name'])) ? $data['name'] : null;
     }

    public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'ownerId' => $this->ownerId,
            'name'  => $this->name,
        ];
    }

     public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'ownerId',
            'required' => true,
            'filters' => [],
            'validators' => [],
        ]);

        $inputFilter->add([
            'name' => 'name',
            'required' => true,
            'filters' => [],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
 }