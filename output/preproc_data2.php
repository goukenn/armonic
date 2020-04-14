<?php
?><script language="javascript">
if(type=="j"){
    if(valeur=="") {
        document.getElementById(nom).value = "01";
        valeur = "01";
    }
    if(!isInt(valeur)){
        alert("<?php 
if($malangue == "NL")
    echo "opgegeven datum bestaat niet";
else{
    ?>Le jour n'est pas un nombre<?php 
}
?> !");
        document.getElementById(nom).value = "01";
    }
    if(valeur<0 || valeur>31) {
        alert("<?php 
if($malangue == "NL")
    echo "opgegeven datum bestaat niet";
else{
    ?>Le jour est hors des limites (0-31)<?php 
}
?>!");
        document.getElementById(nom).value = "01";
    }
    if(valeur.length=="1") document.getElementById(nom).value = "0"+valeur;
}</script>