<?php
// author: C.A.D. BONDJE DOUE

require_once(dirname(__FILE__)."/igk_framework.php");
try {
    IGKOb::Start();
    include_once(igk_getr("file"));
    IGKOb::Clear();
    igk_wl("ok");
}
catch(Exception $ex){
    IGKOb::Start();
    igk_wl("error ".$ex);
    IGKOb::Clear();
    igk_wl("bad");
}