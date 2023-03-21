<?php

namespace App\Core;

use App\Core\Interfaces\IRequest;

class Request implements IRequest
{
    private $get = array();
    private $post = array();
    private $url = "";
    
    public function __construct()
    {
        $this->storeGet();
        $this->storePost();
        $this->storeUrl();
        
        // Надо мидлваре прикрутить, но потом, не сейчас
        if (empty($_SESSION['user'])) {
            header("Location: http://{$_SERVER['HTTP_HOST']}/admin/index.php");
            exit;
        }
    }
    
    private function storeUrl()
    {
        $url       = $_SERVER['REQUEST_URI'];
        $url       = explode("?", $url);
        $this->url = $url[0];
    }
    
    private function storeGet()
    {
        foreach ($_GET as $k => $v) {
            $this->get[$k] = $v;
        }
    }
    
    private function storePost()
    {
        foreach ($_POST as $k => $v) {
            $this->post[$k] = $v;
        }
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
    public function getQueryKey($key, $default = null)
    {
        if (isset($this->get[$key])) {
            return $this->get[$key];
        }
        
        return $default;
    }
    
    public function getPostKey($key, $default = null)
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        
        return $default;
    }
    
    public function isGetEmpty()
    {
        return empty($this->get);
    }
    
    public function isPostEmpty()
    {
        return empty($this->post);
    }
    
    public function redirectToCurrentPage()
    {
        header('Location: /'. $this->getURL());
    }
    
    /**
     * @param $data
     * @param bool $status
     */
    public function returnJson($data, $status = true)
    {
        if ($status) {
            echo json_encode(array(
                'status' => $status,
                'data'   => $data,
            ));
        } else {
            echo json_encode(array(
                'error'  => 1,
                'data'   => $data,
            ));
        }
    }
}
