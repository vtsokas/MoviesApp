<?php 

namespace Movie\Form;

use Zend\Form\Form;

class MovieForm extends Form
{
    public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('movie');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Title',
            ],
        ]);
        $this->add([
            'name' => 'startDate',
            'type' => 'date',
            'options' => [
                'label' => 'Start date',
            ],
        ]);
        $this->add([
            'name' => 'endDate',
            'type' => 'date',
            'options' => [
                'label' => 'End date',
            ],
        ]);
        $this->add([
            'name' => 'cinemaName',
            'type' => 'select',
            'options' => [
                'label' => 'Cinema',
            ],
        ]);
        $this->add([
            'name' => 'category',
            'type' => 'text',
            'options' => [
                'label' => 'Category',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
            ],
        ]);
    }
}