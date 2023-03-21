<?php

namespace App\Service;

use App\Helper\UserPermissions;
use App\Repository\Navigation as NavigationRepository;
use App\Repository\User as UserRepository;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader as TwigFileLoader;
use Twig_Filter_Function;

class Twig
{
    private $render;
    
    public function __construct()
    {
        $loader = new TwigFileLoader('templates');
        $render = new TwigEnvironment($loader, array(
            'cache' => 'templates/.cache',
        ));
        $render->addFilter('floor', new Twig_Filter_Function('floor'));
        $render->enableAutoReload();
        $this->render = $render;
        $this->defineGlobals();
    }
    
    public function getRender()
    {
        return $this->render;
    }
    
    private function defineGlobals()
    {
        $navigation = $this->getNavigation();
        $user = $this->getCurrentUser();
        $this->render->addGlobal('global_navigation', $navigation);
        $this->render->addGlobal('global_user', $user);
    }
    
    private function getNavigation()
    {
        $navigationRepo = new NavigationRepository();
        $navigation = $navigationRepo->getNavigation();
        
        $out = array();
        foreach ($navigation as $nav) {
            $nav['groups_access'] = !empty($nav['groups_access']) ? json_decode($nav['groups_access']) : array();
            $nav['single_user_access'] = !empty($nav['single_user_access']) ? json_decode($nav['single_user_access']) : array();
    
            if (!UserPermissions::isCurrentUserHaveRights($nav['single_user_access'], $nav['groups_access'])) {
                continue;
            }
            
            if (empty($nav['head_menu'])) {
                $out['links'][] = $nav;
                continue;
            }
            
            if (!array_key_exists($nav['head_menu'], $out)) {
                $out[$nav['head_menu']] = array();
                $out[$nav['head_menu']][] = $nav;
            } else {
                $out[$nav['head_menu']][] = $nav;
            }
        }
        
        return $out;
    }
    
    private function getCurrentUser()
    {
        $user_id = $_SESSION['user'];
        $userRepo = new UserRepository();
        
        return $userRepo->getUser($user_id);
    }
}
