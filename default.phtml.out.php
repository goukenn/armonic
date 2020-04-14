<?php
$t->clearChilds();
$t->addDiv()->addSectionTitle(4)->Content="CDN View";
$mid=new IGKBalafonApplicationMiddlewareManager();
$mid["viewArgs"]=igk_view_args();
if(igk_environment()->is("development")){
    $mid->useExceptionHandle();
}
$mid->Run(function($service){$t=igk_createnode("div");
    $t->addDiv()->Content="Information du jour";
    $t->renderAJX();
});
$mid->Process();