<?php
// @file: igk_core.php
// @author: C.A.D. BONDJE DOUE
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>evalute constant and get the value</summary>
///<return>null if constant not defined</return>
/**
* evalute constant and get the value
*/
function igk_const($n){
    if(defined($n)){
        return constant($n);
    }
    return null;
}
///<summary>check if a constant match the defvalue</summary>
/**
* check if a constant match the defvalue
*/
function igk_const_defined($ctname, $defvalue=1){
    if(defined($ctname))
        return constant($ctname) == $defvalue;
    return false;
}
///<summary>Represente igk_create_instance function</summary>
///<param name="class"></param>
///<param name="obj" ref="true"></param>
///<param name="callback"></param>
/**
* Represente igk_create_instance function
* @param  $class
* @param  * $obj
* @param  $callback
*/
function igk_create_instance($class, & $obj, $callback){
    if($obj === null){
        $obj=$callback($class);
    }
    return $obj;
}
///<summary>Represente igk_dev_wln function</summary>
/**
* Represente igk_dev_wln function
*/
function igk_dev_wln(){
    if(igk_environment()->is("DEV")){
        call_user_func_array("igk_wln", func_get_args());
    }
}
///<summary>convert system path to uri scheme</summary>
/**
* convert system path to uri scheme
*/
function igk_html_uri($uri){
    if(is_object($uri)){
        igk_die(__FUNCTION__." passing object is not allowed;");
    }
    return str_replace("\\", "/", $uri);
}
///get basename without extension
/**
*/
function igk_io_basenamewithoutext($file){
    return igk_io_remove_ext(basename($file));
}
///<summary>Represente igk_io_get_script function</summary>
///<param name="f"></param>
/**
* Represente igk_io_get_script function
* @param 77  $f
*/
function igk_io_get_script($f){
    if(file_exists($f)){
        return "?>".file_get_contents($f);
    }
    return null;
}
///<summary>Represente igk_io_path_ext function</summary>
///<param name="fname"></param>
/**
* Represente igk_io_path_ext function
* @param  $fname
*/
function igk_io_path_ext($fname){
    if(empty($fname))
        return null;
    return ($t=explode(".", $fname)) > 1 ? array_pop($t): "";
}
///<summary>Remove extension from filename @name file name</summary>
/**
* Remove extension from filename @name file name
*/
function igk_io_remove_ext($name){
    if(empty($name))
        return null;
    $t=explode(".", $name);
    if(count($t) > 1){
        $s=substr($name, 0, strlen($name) - strlen($t[count($t)-1])-1);
        return $s;
    }
    return $name;
}
///<summary>detect that the environment in on command line mode</summary>
/**
* detect that the environment in on command line mode
*/
function igk_is_cmd(){
    return igk_get_env("sys://func/".__FUNCTION__) || (isset($_SERVER["argv"]) && !isset($_SERVER["SERVER_PROTOCOL"]));
}
///<summary>Represente igk_load_library function</summary>
///<param name="name"></param>
/**
* Represente igk_load_library function
* @param  $name
*/
function igk_load_library($name){
    static $inUse=null;
    if($inUse === null){
        $inUse=array();
    }
    $lib=IGK_LIB_DIR."/Library/";
    $c=$lib."/igk_".$name.".php";
    $ext=igk_io_path_ext(basename($name));
    if(empty($ext) || ($ext != ".php"))
        $ext=".php";
    if((file_exists($c) || file_exists($c=$lib."/".$name.$ext)) && !isset($inUse[$c])){
        include_once($c);
        $inUse[$c]=1;
        return 1;
    }
    return 0;
}
///<summary>shortcut to get server info data</summary>
/**
* shortcut to get server info data
*/
function igk_server(){
    return IGKServer::getInstance();
}
///<summary>download zip core </summary>
/**
* download zip core 
*/
function igk_sys_download_core($download=1){
    $tfile=tempnam(sys_get_temp_dir(), "igk");
    $zip=new ZipArchive();
    if($zip->open($tfile, ZIPARCHIVE::CREATE)){
        igk_zip_dir(IGK_LIB_DIR, $zip, "Lib/igk", "/\.(vscode|git|gkds)$/");
        $manifest=igk_createxmlnode("manifest");
        $manifest["xmlns"]="https://www.igkdev.com/balafon/schemas/manifest";
        $manifest["appName"]=IGK_PLATEFORM_NAME;
        $manifest->add("version")->Content=IGK_VERSION;
        $manifest->add("author")->Content=IGK_AUTHOR;
        $manifest->add("date")->Content=date("Ymd His");
        $zip->addFromString("manifest.xml", $manifest->render());
        $zip->addFromString("__lib.def", "");
        $zip->close();
    }
    if($download)
        igk_download_file("Balafon.".IGK_VERSION.".zip", $tfile, "binary", 0);
    return $tfile;
}
///<summary>utility to write html content </summary>
///<param name="args"> mixed| 1 array is attribute or next is considered as content to render </summary>
/**
* utility to write html content 
* @param 88 mixed args  mixed| 1 array is attribute or next is considered as content to render 
*/
function igk_tag_wln($tag, $args=''){
    $attr="";
    $targs=array_slice(func_get_args(), 1);
    if(is_array($args) && (func_num_args() > 2)){
        $attr=" ".igk_html_render_attribs($args);
        $targs=array_slice($targs, 1);
    }
    ob_start();
    call_user_func_array('igk_wln', $targs);
    $s=ob_get_contents();
    ob_end_clean();
    $o="<{$tag}".$attr;
    if(empty($s)){
        $o .= "/>";
    }
    else{
        $o .= "> ".$s."</{$tag}>";
    }
    igk_wl($o);
}
///<summary>Represente igk_wl function</summary>
///<param name="msg"></param>
/**
* Represente igk_wl function
* @param  $msg
*/
function igk_wl($msg){
    include(IGK_LIB_DIR.'/Inc/igk_trace.pinc');
    $tab=func_get_args();
    while($msg=array_shift($tab)){
        if(is_array($msg) || is_object($msg)){
            igk_log_var_dump($msg);
        }
        else
            echo $msg;
    }
}
///<summary>Represente igk_wl_pre function</summary>
///<param name="p"></param>
/**
* Represente igk_wl_pre function
* @param  $p
*/
function igk_wl_pre($p){
    echo "<pre>";
    print_r($p);
    echo "</pre>";
}
///<summary>Represente igk_wln function</summary>
///<param name="msg" default=""></param>
/**
* Represente igk_wln function
* @param  $msg the default value is ""
*/
function igk_wln($msg=""){
    include(IGK_LIB_DIR.'/Inc/igk_trace.pinc');
    if(!($lf=igk_get_env(IGK_LF_KEY))){
        $v_iscmd=igk_is_cmd();
        $lf=$v_iscmd ? IGK_CLF: "<br />";
    }
    foreach(func_get_args() as $k){
        $msg=$k;
        if(is_string($msg) || is_numeric($msg))
            igk_wl($msg.$lf);
        else{
            if($msg !== null){
                if(is_object($msg)){
                    if(igk_reflection_class_extends($msg, IGKHtmlItem::class)){
                        igk_wl($msg->Render().$lf);
                        continue;
                    }
                    var_dump($msg);
                }
                else{
                    igk_log_var_dump($msg);
                }
                igk_wl($lf);
            }
            else{
                igk_wl(__FUNCTION__."::msg is null".$lf);
            }
        }
    }
}
///<summary>write line to buffer and exit</summary>
/**
* write line to buffer and exit
*/
function igk_wln_e($msg){
    igk_set_env('TRACE_LEVEL', 3);
    call_user_func_array('igk_wln', func_get_args());
    igk_exit();
}
///<summary>represent internal core loader</summary>
/**
* represent internal core loader
*/
class IGKLoader{
    private $_controller;
    private $_listener;
    private $_output;
    ///<summary>dispatch call to controller</summary>
    /**
    * dispatch call to controller
    */
    public function __call($n, $args){
        if(method_exists($this->_controller, $n)){
            return call_user_func_array(array($this->_controller, $n), $args);
        }
    }
    ///<summary>Represente __construct function</summary>
    ///<param name="ctrl"></param>
    /**
    * Represente __construct function
    * @param  $ctrl
    */
    public function __construct($ctrl, $listener){
        $this->_controller=$ctrl;
        $this->_output="";
        $this->_listener=$listener;
    }
    ///<summary>Represente __get function</summary>
    ///<param name="n"></param>
    /**
    * Represente __get function
    * @param  $n
    */
    public function __get($n){
        if(method_exists($this, $m="get".$n)){
            return call_user_func_array(array($this, $m), array());
        }
        else{
            return $this->_controller->$n;
        }
    }
    ///<summary>Represente _inc_file function</summary>
    ///<param name="file"></param>
    ///<param name="data"></param>
    /**
    * Represente _inc_file function
    * @param  $file
    * @param  $data
    */
    private function _inc_file($file, $data){
        extract($data);
        $ctrl=$this->_controller;
        include($file);
    }
    ///<summary>Represente article function</summary>
    ///<param name="file"></param>
    ///<param name="args" default="null"></param>
    ///<param name="render" default="1"></param>
    /**
    * Represente article function
    * @param  $file
    * @param  $args the default value is null
    * @param  $render the default value is 1
    */
    public function article($file, $args=null, $render=1){
        $f=$this->_controller->getArticle($file);
        if(!file_exists($f)){
            return false;
        }
        $n=igk_createnode("notagnode");
        $n->addArticle($this->_controller, $f, $args);
        if($render){
            $n->renderAJX();
        }
        return $n;
    }
    ///<summary>Represente clear function</summary>
    /**
    * Represente clear function
    */
    public function clear(){
        $this->_controller->_output="";
    }
    ///<summary>check an resolve view file</summary>
    /**
    * check an resolve view file
    */
    public function file_exists($view){
        $f=stream_resolve_include_path($view);
        if(!empty($f))
            return $f;
        if(file_exists($view))
            return realpath($view);
        if(!empty($c=$this->_controller->getViewfile($view))){
            return $c;
        }
        return false;
    }
    ///<summary>Represente getConfigs function</summary>
    /**
    * Represente getConfigs function
    */
    public function getConfigs(){
        return $this->_controller->getConfigs();
    }
    ///<summary>Represente getLoader function</summary>
    /**
    * Represente getLoader function
    */
    public function getLoader(){
        return $this;
    }
    ///<summary>Represente getOut function</summary>
    /**
    * Represente getOut function
    */
    public function getOut(){
        return $this->_controller->_output;
    }
    ///<summary>Represente getUser function</summary>
    /**
    * Represente getUser function
    */
    public function getUser(){
        return $this->_controller->User;
    }
    ///<summary> use to load model class</summary>
    /**
    *  use to load model class
    */
    public function & model($name, $refname=null, $forceloading=false){
        $n=$refname ? $refname: $name;
        $igk_c=$this->_controller;
        $cl=$name;
        $cl_c=get_class($igk_c);
        ($m=igk_get_env($key="sys://instance/model/".$cl_c)) || ($m=array());
        if(isset($m[$n])){
            return $m[$n];
        }
        if(!class_exists($cl, $forceloading)){
            $meth="GetModelClassName";
            if(method_exists($cl_c, $meth)){
                $cl=call_user_func_array(array($cl_c, $meth), array($name));
            }
            else{
                $ns="";
                if($g_fc=$this->_listener){
                    $d=$g_fc();
                    $ns=$d->entryNS;
                }
                else{
                    $ns=$igk_c->getEntryNamespace();
                }
                $cl=$ns."\\Models\\".ucfirst($name)."Model";
            }
        }
        if(!class_exists($cl)){
            igk_die("model $name not found .".$cl. " = ".$cl_c);
        }
        $m[$n]=new $cl($igk_c);
        igk_set_env($key, $m);
        return $m[$n];
    }
    ///<summary> load only view file </summary>
    /**
    *  load only view file 
    */
    public function view($file, $data=array(), $render=0){
        if(file_exists($f=$this->_controller->getViewFile($file))){
            $file=$f;
        }
        else{
            if(!file_exists($file)){
                $file=dirname(__FILE__)."/Views/".$file.".phtml";
            }
        }
        if(!file_exists($file))
            return $this;
        $bck=set_include_path(dirname($file).PATH_SEPARATOR. get_include_path());
        $data=array_merge($this->_controller->getSystemVars(), array(
            "dir"=>dirname($file),
            "fname"=>igk_io_getviewname($file,
            $this->_controller->getViewDir())
        ), $data);
        ob_start();
        $this->_inc_file($file, $data);
        $o=ob_get_contents();
        ob_end_clean();
        set_include_path($bck);
        if($render)
            echo $o;
        else{
            $this->_controller->_output .= $o;
        }
        return $this;
    }
}
///<summary>represent server management </summary>
/**
* represent server management 
*/
final class IGKServer{
    private $data;
    private static $sm_server;
    ///<summary>Represente __construct function</summary>
    /**
    * Represente __construct function
    */
    private function __construct(){
        $this->prepareServerInfo();
    }
    ///<summary>Represente __get function</summary>
    ///<param name="n"></param>
    /**
    * Represente __get function
    * @param  $n
    */
    public function __get($n){
        if(isset($this->data[$n]))
            return $this->data[$n];
        return null;
    }
    ///<summary>Represente __isset function</summary>
    ///<param name="n"></param>
    /**
    * Represente __isset function
    * @param  $n
    */
    public function __isset($n){
        return isset($this->data[$n]);
    }
    ///<summary>Represente __set function</summary>
    ///<param name="n"></param>
    ///<param name="v"></param>
    /**
    * Represente __set function
    * @param  $n
    * @param  $v
    */
    public function __set($n, $v){
        if($v === null){
            unset($this->data[$n]);
        }
        else
            $this->data[$n]=$v;
    }
    ///<summary>Represente getInstance function</summary>
    /**
    * Represente getInstance function
    */
    public static function getInstance(){
        $r=& self::$sm_server;
        return igk_create_instance(__CLASS__, $r, function($s){
            return new $s();
        });
    }
    ///<summary>Represente IsEntryFile function</summary>
    ///<param name="file"></param>
    /**
    * Represente IsEntryFile function
    * @param  $file
    */
    public function IsEntryFile($file){
        return $file === realpath($this->SCRIPT_FILENAME);
    }
    ///<summary>check if this request is POST</summary>
    /**
    * check if this request is POST
    */
    public function ispost(){
        return $this->REQUEST_METHOD == "POST";
    }
    ///<summary>check for method. if type is null return the REQUEST_METHOD</summary>
    /**
    * check for method
    */
    public function method($type=null){
        if($type === null)
            return $this->REQUEST_METHOD;
        return $this->REQUEST_METHOD == $type;
    }
    ///<summary>Represente prepareServerInfo function</summary>
    /**
    * Represente prepareServerInfo function
    */
    public function prepareServerInfo(){
        $this->data=array();
        foreach($_SERVER as $k=>$v){
            $this->data[$k]=$v;
        }
        $this->IGK_SCRIPT_FILENAME=igk_html_uri(realpath($this->SCRIPT_FILENAME));
        $this->IGK_DOCUMENT_ROOT=igk_html_uri(realpath($this->DOCUMENT_ROOT))."/";
        $sym_root=$this->IGK_DOCUMENT_ROOT !== $this->DOCUMENT_ROOT;
        $c_script=$this->IGK_SCRIPT_FILENAME;
        if(!$sym_root)
            $c_script=$this->SCRIPT_FILENAME;
        if(!empty($doc_root=$this->IGK_DOCUMENT_ROOT)){
            $doc_root=str_replace("\\", "/", realpath($doc_root));
            $self=substr($c_script, strlen($doc_root));
            if($self[0] == "/")
                $self=substr($self, 1);
            $basedir=str_replace("\\", "/", dirname($doc_root."/".$self));
            $this->IGK_BASEDIR=$basedir;
            $uri=$this->REQUEST_SCHEME."://".$this->HTTP_HOST;
            $query=substr($basedir, strlen($doc_root) + 1);
            if(!empty($query))
                $query .= "/";
            $baseuri=$uri."/".$query;
            $this->IGK_BASEURI=$baseuri;
        }
        $this->IGK_CONTEXT=($t_=isset($this->HTTP_HOST)) ? "html": "cmd";
        $this->LF=$t_ ? "\n": "<br />";
        if(!empty($env=$this->ENVIRONMENT)){
            $this->ENVIRONMENT=defined('IGK_ENV_PRODUCTION') ? "production": $env;
        }
        else{
            $this->ENVIRONMENT=defined('IGK_ENV_PRODUCTION') ? "production": "development";
        }
        if(!isset($this->WINDIR)){
            $this->WINDIR=($this->OS == "Windows_NT");
        }
        if(isset($_SERVER['REDIRECT_STATUS']) && isset($_GET["__c"])){
            $_get=array_slice($_GET, 0);
            $this->REDIRECT_CODE=$_get["__c"];
            $this->REDIRECT_OPT=array();
            unset($_get["__c"]);
            $_SERVER["QUERY_STRING"]=http_build_query($_get);
        }
        $this->REQUEST_PATH=explode("?", $this->REQUEST_URI)[0];
    }
    ///<summary>Represente toArray function</summary>
    /**
    * Represente toArray function
    */
    public function toArray(){
        return $this->data;
    }
}
defined("IGK_FRAMEWORK") || die("REQUIRE FRAMEWORK - No direct access allowed");
define(basename(__FILE__), 1);