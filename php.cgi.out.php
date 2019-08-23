#!D:\wamp3.1\bin\php\php7.2.8\php.exe
<?php
// define("IGK_APP_DIR", dirname(__FILE__));
// define("IGK_BASE_DIR", dirname(__FILE__));
///<summary>represent git_die function</summary>
///<param name="content"></param>
function git_die($content){
    igk_render($content);
    exit();
}
///<summary>represent igk_getv function</summary>
///<param name="tab"></param>
///<param name="n"></param>
///<param name="def" default="null"></param>
function igk_getv($tab, $n, $def=null){
    if (isset($tab[$n]))
        return $tab[$n];
    return $def;
}
///<summary>represent igk_render function</summary>
///<param name="content"></param>
///<param name="status" default="404"></param>
function igk_render($content, $status=404){
    echo "Content-Type: text/html\r\n\r\n";
    echo $content;
}

@exec("where git 2> NULL", $c, $o);
if ($o != 0){
    echo "gitprocess not found";
    exit(1);
}
$git_cmd=$c[0];
$c=array();
if (!isset($_SERVER["PATH_INFO"])){
    echo "PATH info is required";
    exit(2);
}
$request_uri=igk_getv($_SERVER, "REQUEST_URI");
$path=igk_getv($_SERVER, "PATH_INFO");
defined("GIT_PROJECT_ROOT") ? $root=GIT_PROJECT_ROOT: ($root=igk_getv($_SERVER, 'GIT_PROJECT_ROOT')) || git_die("GIT  REPOS NOT DEFINED");
$query=igk_getv(explode("?", $request_uri), 1);
$ru="?".$query;
$path_i=$path;
$cmd=<<<EOF
set GIT_PROJECT_ROOT={$root}
set GIT_HTTP_EXPORT_ALL=1
set GIT_CURL_VERBOSE=1
set PATH_INFO={$path_i}
set REQUEST_URI={$path_i}{$ru}
set QUERY_STRING={$query}
"{$git_cmd}" http-backend
EOF;
$cmd=implode("&", explode("\n", str_replace("\r", "", $cmd)));
@exec($cmd, $c, $o);
$s=implode("\n", $c);
echo $s;