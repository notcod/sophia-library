<?php

namespace Sophia;

class Controller
{
    protected $DB;
    protected $method;
    protected $data = [];

    public function DB()
    {
        $this->DB = new \Sophia\Addon\DB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
    public function post_data()
    {
        if (isset($_SESSION['post_data'])) {
            $this->data['post_data'] = $_SESSION['post_data'];
            unset($_SESSION['post_data']);
        }
    }
    public function model($model)
    {
        $run = explode('/', $model);
        $run = "\\Sophia\\Model\\" . end($run);

        if (class_exists($run)) {
            return new $run();
        }else{
            if (!file_exists(DIR.'models/' . $model . '.php')) return false;
            require_once(DIR.'models/' . $model . '.php');
            return new $run();
        }
    }
    public function view($arr = [])
    {
        $view = explode("\\", get_class($this));
        $arr["view"] = isset($arr["view"]) ? $arr["view"] : strtolower(end($view));

        $this->data['auth'] = ifset($_SESSION, 'user');

        $this->post_data();

        @$arr['style'] = isset($arr['style']) ? array_merge($arr['style'], $this->data['style']) : $this->data['style'];
        @$arr['script'] = isset($arr['script']) ? array_merge($arr['script'], $this->data['script']) : $this->data['script'];

        $data = array_merge($this->data, $arr);

        $data["description"] = isset($data["description"]) ? $data["description"] : SITENAME;
        $data["keywords"] = isset($data["keywords"]) ? $data["keywords"] : SITENAME.",".SITENAME;

        require_once(DIR.'template/Template.php');

        exit();
    }
    public function back()
    {
        $prevous = ifset($_SERVER, 'HTTP_REFERER');
        if ($prevous != NULL)
            redirect($prevous);
        else
            redirect('/');
    }
    public function return($data)
    {
        @$_SESSION['post_data'] = isset($data['error']) && $data['error'] == true ? ['message' => $data, '_method' => $_REQUEST['_method']] : array_merge($_REQUEST, ['message' => ['description' => $data]]);

        $this->back();
    }
    public function json($data)
    {
        die(json_encode($data));
    }
    public function ifToken()
    {
        if (isset($_REQUEST["_token"]) && isset($_SESSION["_token"]) && hash_equals(hash_hmac("sha256", md5($_REQUEST["_method"]), $_SESSION['_token']), $_REQUEST["_token"]))
            unset($_SESSION["_token"]);
        else
            $this->back();
    }
    public function check($arr = [])
    {
        $r = (object) $this->_req;
        $r->_errors = [];
        foreach ($arr as $v => $s) {
            if ((isset($this->_req[$v]) && !empty($this->_req[$v]) ? $this->_req[$v] : false) == false) $r->_errors[] = $s;
        }
        return $r;
    }

    function set($z)
    {
        foreach ($z as $v) {
            if (is_array($v)) {
                $key = current(array_keys($v));
                if (gettype($key) == "integer") {
                    if (is_array($v[1])) {
                        $val = post($v[0]);
                        foreach ($v[1] as $fu) {
                            $fu[1][] = $val;
                            $arr_vals = array_values(array_reverse($fu[1]));
                            $val = call_user_func_array($fu[0], $arr_vals);
                        }
                    } else {
                        if (isset($v[2])) {
                            $v[2][] = post($v[0]);
                            $arr_vals = array_values(array_reverse($v[2]));
                            $val = call_user_func_array($v[1], $arr_vals);
                        } else {
                            $val = $v[1](post($v[0]));
                        }
                    }
                    $variable = $v[0];
                } else {
                    $variable = $key;
                    $v = $v[$key];
                    if (is_array($v)) {
                        if (is_array($v[1])) {
                            $val = $v[0];
                            foreach ($v[1] as $fu) {
                                $fu[1][] = $val;
                                $arr_vals = array_values(array_reverse($fu[1]));
                                $val = call_user_func_array($fu[0], $arr_vals);
                            }
                        } else {
                            if (isset($v[2])) {
                                $v[2][] = $v[0];
                                $arr_vals = array_values(array_reverse($v[2]));
                                $val = call_user_func_array($v[1], $arr_vals);
                            } else
                                $val = $v[1]($v[0]);
                        }
                    } else
                        $val = $v;
                }
            } else {
                $variable = $v;
                $val = post($v);
            }
            $this->_req[$variable] = $this->DB->escape($val);
        }
    }
}
