<?php
function csrf($get = false, $form = "")
{
    if (!isset($_SESSION["_token"]) || empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['_token'];
    $token = hash_hmac("sha256", md5($form), $token);
    if ($get) {
        return '&_token=' . $token;
    } else {
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}
function contains($str, $check)
{
    return strpos($str, $check) !== false;
}
function url_strip($href)
{
    return (substr($href, 0, strlen("https://")) == "https://" || substr($href, 0, strlen("http://")) == "http://" || substr($href, 0, 2) == "//");
}
function length($str, $c)
{
    return strlen($str) >= $c ? $str : '';
}
function getFile($dir, $paths = "", $data = []){
    if(!file_exists(APPROOT.$dir)){
        if(!CREATE_FILE) return false;

        $extension = explode(".", $dir);
        $extension = end($extension);

        $path = explode("/", $dir);
        $start = "";
        for($i = 0; $i < count($path) - 1; $i++){
            $start = $start == "" ? $path[0] : $start."/".$path[$i];
            if(!file_exists(APPROOT.$start)){
                mkdir(APPROOT.$start);
            }
        }
        $myfile = fopen(APPROOT.$dir, "a") or die("Unable to open file!");
        fclose($myfile);
    }
    if($paths != "")
        require_once($paths.$dir);
    else
        return ROOT.$dir;
}
function content($part, $data){
    return getFile('app/views/' . $part, APPROOT, $data);
}
function getDefConst($check = false)
{
    $constants = get_defined_constants(true);

    if($check) return (isset($constants['user'][$check]) ? $constants['user'][$check] : false);

    return (isset($constants['user']) ? $constants['user'] : array());
}
function isEmail($q)
{
    return filter_var($q, FILTER_VALIDATE_EMAIL) ? $q : false;
}
function ifset($a, $b)
{
    return isset($a[$b]) ? $a[$b] : NULL;
}
function html_one_line($content = '')
{
    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/', // Remove HTML comments
        '/\>\s+\</m'
    );
    $replace = array('>', '<', '\\1', '', '><');
    $content = preg_replace($search, $replace, $content);
    return img_root($content);
}
function img_root($content = '')
{
    if (ROOT == "/") return $content;
    $dom = new DOMDocument();
    $dom->loadHTML($content);
    foreach ($dom->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        if (strpos($src, 'https://') === false && strpos($src, 'http://') === false) {
            $img->setAttribute('src', IMGROOT . $src);
        }
    }
    return $dom->saveHTML();
}
function minimize_css($cache)
{

    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/', // Remove HTML comments
        '#/\*.*?\*/#s',
        '/\>\s+\</m',
        "/[\n\r]/"
    );
    $replace = array('>', '<', '\\1', '', '', '><', '');
    return preg_replace($search, $replace, $cache);
}
function message($data, $form = null)
{
    echo '<input type="hidden" name="_method" value="' . $form . '">';
    echo csrf(false, $form);
    if (isset($data["post_data"]["message"])) {

        if ($form == null) {

            $description = isset($data["post_data"]["message"]["description"]) ? $data["post_data"]["message"]["description"] : "";
            $status = isset($data["post_data"]["message"]["error"]) && $data["post_data"]["message"]["error"] == true ? 'success' : 'danger';
            $hash = md5(microtime(true));
            return '<div class="alert alert-' . $status . '" id="' . $hash . '">' . $description . '</div>
                <script>window.location.hash = "#' . $hash . '";</script>';
        } else {
            if (data($data, "_method") == $form) {

                $description = isset($data["post_data"]["message"]["description"]) ? $data["post_data"]["message"]["description"] : "";
                $status = isset($data["post_data"]["message"]["error"]) && $data["post_data"]["message"]["error"] == true ? 'success' : 'danger';
                $hash = md5(microtime(true));
                return '<div class="alert alert-' . $status . '" id="' . $hash . '">' . $description . '</div>
                <script>window.location.hash = "#' . $hash . '";</script>';
            }
        }
    }
}
function url()
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' . "://" . $_SERVER['SERVER_NAME'] . "/" : 'https' . "://" . $_SERVER['SERVER_NAME'] . "/";
}
function strip($q)
{
    return htmlspecialchars($q);
}
function int($q)
{
    $q = preg_replace('/[^0-9.]/', '', $q);
    return (empty($q)) ? 0 : $q;
}
function md50($q)
{
    return strlen($q) > 0 ? substr(strtonum(md5($q)), 6, 6) : '';
}
function strtonum($data)
{
    $new_string = "";
    $alphabet =  range("A", "Z");
    $string_arr = str_split(clean($data));
    foreach ($string_arr as $str) {
        $new_string .= is_numeric($str) ? $str : array_search($str, $alphabet);
    }
    return $new_string;
}
function clean($q)
{
    return strtolower(preg_replace('/[^\w]/', '', $q));
}
function ip()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}
function get($data, $value, $type = 'string')
{
    if ($type == 'string') {
        return isset($data[$value]) ? strip($data[$value]) : '';
    } elseif ($type == 'int') {
        return isset($data[$value]) ? int($data[$value]) : '';
    }
}

function post1($value, $type = 'string', $data = null)
{
    $data = $data == null ? $_POST : $data;
    if ($type == 'string') {
        return isset($data[$value]) ? strip($data[$value]) : '';
    } elseif ($type == 'int') {
        return isset($data[$value]) ? int($data[$value]) : '';
    }
}
function post($v)
{
    return isset($_POST[$v]) ? strip($_POST[$v]) : '';
}
function data($data, $value)
{
    return isset($data["post_data"][$value]) ? strip($data["post_data"][$value]) : '';
}
function isCurrent($data, $value)
{
    $page = get($data, 'page');
    return $page == $value ? 'active' : '';
}
function format_date($date)
{
    return date_format(date_create($date), "H:i\h d.m.Y.");
}
function fatal_handler()
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        $myfile = fopen(dirname(dirname(__FILE__)) . "/logs/php_logs.txt", "a") or die("Unable to open file1!");
        fwrite($myfile, "\n" . json_encode($error));
        fclose($myfile);

        if ($errno == 8 || $errno == 1) {
            // if($errno != 2 || $errno == 1){
            if (!headers_sent()) {
                // redirect("/?error=$errstr");exit;
            }
        }
    }
}
// register_shutdown_function("fatal_handler");

function redirect($filename)
{
    @$isSent = headers_sent();
    if (!$isSent)
        header('Location: ' . $filename);
    else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $filename . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $filename . '" />';
        echo '</noscript>';
    }
}


function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
// function tidyHTML($buffer) {
//     $buffer = html_one_line($buffer);
//     // load our document into a DOM object
//     $dom = new DOMDocument();
//     // we want nice output
//     $dom->preserveWhiteSpace = false;
//     $dom->loadHTML(mb_convert_encoding($buffer, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
//     $dom->formatOutput = true;
//     return $dom->saveHTML();
// }
// function tidyHTML_one_line($buffer) {
//     return(html_one_line(tidyHTML($buffer)));
// }
