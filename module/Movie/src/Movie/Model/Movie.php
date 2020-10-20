<?php
 namespace Movie\Model;

use DomainException;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

 class Movie implements InputFilterAwareInterface
 {
     public $id;
     public $title;
     public $startDate;
     public $endDate;
     public $cinemaName;
     public $category;

     private $inputFilter;

     public function exchangeArray($data)
     {
         $data = array_change_key_case ($data, 0);
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->title = (!empty($data['title'])) ? $data['title'] : null;
         $this->startDate  = (!empty($data['startdate'])) ? $data['startdate'] : null;
         $this->endDate  = (!empty($data['enddate'])) ? $data['enddate'] : null;
         $this->cinemaName  = (!empty($data['cinemaname'])) ? $data['cinemaname'] : null;
         $this->category  = (!empty($data['category'])) ? $data['category'] : null;
     }

    public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'title' => $this->title,
            'startDate'  => $this->startDate,
            'endDate'     => $this->endDate,
            'cinemaName' => $this->cinemaName,
            'category'  => $this->category
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
            'name' => 'title',
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

        $inputFilter->add([
            'name' => 'cinemaName',
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