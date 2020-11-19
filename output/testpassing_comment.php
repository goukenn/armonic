<?php
///<summary>get communes for select</summary>
function getcommunes($lang="FR"){
    if(!ipb_settings("no_reftab")){
        $m=new Model_Reftab();
        $tab=array();
        $langC=GetLangText();
        if($cd=$m->GetrMun()){
            $l=$langC[$lang];
            foreach($cd as $rows){
                $tab[]=array("code"=>$rows["MunKey"], "lib"=>$rows["MunTextB". $l]);
            }
        }
        else{
            //+ not connexions to reftab or empty list
        }
        return $tab;
    }
    return [];
}
