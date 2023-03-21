<?php

namespace App\Controller;

use App\Core\Request;
use App\Repository\User as UserRepository;
use Exception;
use App\Service\Twig;

abstract class AbstractController
{
    protected $request;
    protected $render;
    protected $user;

    public function __construct()
    {
        $this->request = new Request();
        $render  = new Twig();
        $this->render = $render->getRender();
        $this->user = $this->getCurrentUser();
    }
    
    abstract function indexAction();
    
    public function __call($name, $args)
    {
        $method = "{$name}Action";
        
        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), $args);
        } else {
            $exception = "Method {$method} not found in ".get_class($this);
            throw new Exception($exception);
        }
    }

    private function getCurrentUser()
    {
        $user_id = $_SESSION['user'];
        $userRepo = new UserRepository();

        return $userRepo->getUser($user_id);
    }
}
