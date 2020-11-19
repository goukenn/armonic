<?php
// author: C.A.D. BONDJE DOUE

require_once(dirname(__FILE__)."/igk_framework.php");
$dir=realpath(dirname(__FILE__)."/../../");
chdir($dir);
$f=igk_str_rm_start(igk_getv($_SERVER, 'REQUEST_URI'), '/');
igk_header_cache_output();
header("Content-Type:image/png");
if(file_exists($f)){
    igk_wl(file_get_contents($f));
    igk_exit();
}
$favi="iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACfSURBVDhPY5B3/+8g6/6/gVzMACLkPP7/JxcPYgOKe///f/XuPxg8fvH/f+MMTDUgjNOAOeshOK7m//9z1/////Hz/39VP0x1RHlh1S6IATrBmHIEDeiYB/EGyEvY5PEa4JEF0dy/BLs8COM1ABRwIGAVj10ehPEaEFYGsR2b32EYrwEgzRS5ABRwxy/+/28ciV0ehPEaQAweaAP+/wcAXqFvrFmY6D0AAAAASUVORK5CYII=";
igk_wl(base64_decode($favi));
igk_exit();