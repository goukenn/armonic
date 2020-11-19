<?php
///<summary>Represente scriptaction function</summary>
function scriptaction(){
    //: information
    $lang="fr";
    ?><script language="javascript">
function do<?php  echo $lang ?>(){
    document.writeline("the<?php  echo $lang ?>is done");
}
</script><?php 
}
