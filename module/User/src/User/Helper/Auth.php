<?php

namespace User\Helper;

use Zend\View\Helper\AbstractHelper;
use User\Model\UserTable;

class Auth extends AbstractHelper
{
    private $authService;

    public function __construct(UserTable $service)
    {
        $this->authService = $service;    
    }

    public function __invoke()
    {
        return $this->authService;
    }
}