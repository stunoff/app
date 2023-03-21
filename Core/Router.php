<?php

namespace App\Core;

use App\Core\Interfaces\IRequest;
use App\Helper\UserPermissions;
use App\Repository\Navigation;

class Router
{
    private $request;
    private $routes = array();
    
    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $navigationRepository = new Navigation();
        $urlPermissions = $navigationRepository->getPermissions($this->getUrl());
        $havePermissions = UserPermissions::isCurrentUserHaveRights(
            json_decode($urlPermissions['single_user_access'], true),
            json_decode($urlPermissions['groups_access'], true)
        );
    
        if (!$havePermissions) {
            exit('нет доступа');
        }
    }
    
    public function getUrl()
    {
        $url  = str_replace('.php', '', $this->request->getURL());
        $data = explode('/', $url);
        
        return !empty($data) ? implode('/', $data) : 'index';
    }
    
    public function getRoutes()
    {
        $navigation = new Navigation();
        $routes = $navigation->getRoutes();
        foreach ($routes as $route) {
            $this->routes[$route['page_url']] = array(
                'controller' => $route['controller'],
                'method' => $route['method'],
            );
        }
    }
    
    public function run()
    {
        $url = $this->getUrl();
        if (array_key_exists($url, $this->routes)) {
            $controllerName = 'App\Controller\\' . $this->routes[$url]['controller'] . 'Controller';
            $controller = new $controllerName();
            $method = $this->routes[$url]['method'];
            
            return call_user_func(array($controller, $method));
        } else {
            exit('not found 404');
        }
    }
}
