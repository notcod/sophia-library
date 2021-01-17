<?php

namespace Sophia;

class Core
{
    protected $currentController = 'Home';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct()
    {
        if (!DEV_MODE)
            ob_start('html_one_line');
        else
            ob_start('img_root');

        $url = $this->getUrl();

        $folder = isset($_POST["_token"]) || isset($_GET["_token"])  ? "requests" : "controllers";

        $Controller = isset($url[0]) && !empty($url[0]) ? ucwords($url[0]) : $this->currentController;

        $folder = $Controller == "json" ? 'json' : $folder;

        if (file_exists(DIR . $folder . '/' . $Controller . '.php')) {
            $this->currentController = $Controller;
            if (isset($url[0])) unset($url[0]);
        } else {
            $error = "Controller [ $Controller ] doesn't exist!";
            require_once(DIR . 'template/404.php');
            exit();
        }

        require_once(DIR . $folder . '/' . $this->currentController . '.php');

        $this->currentController = ucwords($folder) . "\\" . $this->currentController;

        $this->currentController = new $this->currentController;


        $Method = isset($url[1]) ? $url[1] : $this->currentMethod;
        if (method_exists($this->currentController, $Method)) {
            $this->currentMethod = $Method;
            if (isset($url[1])) unset($url[1]);
        } else {
            $error = "Method [ " . $Method . " ] doesn't exist in Controller [ $Controller ]";
            require_once(DIR . 'template/404.php');
            exit();
        }
        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl()
    {
        // $var = "url";
        $var = "572d4e421e5e6b9bc11d815e8a027112";
        if (isset($_GET[$var])) {
            $url = rtrim($_GET[$var], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
