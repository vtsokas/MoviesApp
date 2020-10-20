<?php
 namespace User\Model;

use DomainException;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

 class User implements InputFilterAwareInterface
 {
     public $id;
     public $name;
     public $surname;
     public $username;
     public $password;
     public $email;
     public $role;
     public $isCοnfirmed;

     private $inputFilter;

     public function exchangeArray($data)
     {
         $data = array_change_key_case ($data, 0);
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->name = (!empty($data['name'])) ? $data['name'] : null;
         $this->surname  = (!empty($data['surname'])) ? $data['surname'] : null;
         $this->username  = (!empty($data['username'])) ? $data['username'] : null;
         $this->password  = (!empty($data['password'])) ? $data['password'] : null;
         $this->email  = (!empty($data['email'])) ? $data['email'] : null;
         $this->role  = (!empty($data['role'])) ? $data['role'] : null;
         $this->isConfirmed  = (!empty($data['isconfirmed'])) ? $data['isconfirmed'] : null;
     }

    public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'name' => $this->name,
            'surname'  => $this->surname,
            'username'     => $this->username,
            'password' => $this->password,
            'email'  => $this->email,
            'role' => $this->role,
            'isConfirmed'  => $this->isConfirmed
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
            'name' => 'username',
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
            'name' => 'password',
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