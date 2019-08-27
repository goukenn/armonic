<?php

define("IGK_TREAT_TESTING", 1);

if(!defined('IGK_FRAMEWORK')){
    $libfile="";
    if(isset($_SERVER["IGK_LIB_DIR"])){
        $libfile=realpath($_SERVER["IGK_LIB_DIR"]."/igk_framework.php");
    }
    if(!(!empty($libfile) && file_exists($libfile)) && !(file_exists($libfile=dirname(__FILE__)."/../igk/igk_framework.php"))){
        // die("framework doesn't exists.");
        exit(-1);
    }
    require_once($libfile);
    igk_display_error(1);
}
require_once(dirname(__FILE__)."/../armonic.php");


spl_autoload_register(function($n){
    if (file_exists($sfile = dirname(__FILE__)."/{$n}.php")){
        include_once($sfile);
    }    
    else {
        echo "try load ".$n;
        return 0;
    }
    // require_once();
});