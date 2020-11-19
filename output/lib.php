<?php
use \IPB_LogginCentral as IPB_LogginCentral;
use \Roles as Roles;
use IPB\cia2\models\UsersModel;
///<summary>Represente ajustget function</summary>
///<param name="temp"></param>
function ajustget($temp){
    $res=str_replace("&Atilde;&copy;", "é", $temp);
    $res=str_replace("&Atilde;&uml;", "è", $res);
    $res=str_replace("&Atilde;&nbsp;", "à", $res);
    $res=str_replace("&Atilde;&sup1;", "ù", $res);
    $res=str_replace("&Atilde;&ordf;", "ê", $res);
    $res=str_replace("&Atilde;&laquo;", "ë", $res);
    $res=str_replace("&Atilde;&acute;", "ô", $res);
    $res=str_replace("&Atilde;&para;", "ö", $res);
    $res=str_replace("&Atilde;&raquo;", "û", $res);
    $res=str_replace("&Atilde;&cent;", "â", $res);
    $res=str_replace("&Atilde;&frac14;", "ü", $res);
    $res=str_replace("&Atilde;&reg;", "î", $res);
    $res=str_replace("&Atilde;&macr;", "ï", $res);
    $res=str_replace("&Atilde;&sect;", "ç", $res);
    $res=str_replace("&#39;", "&quot;", $res);
    $res=str_replace("&amp;#39;", "&quot;", $res);
    return $res;
}
///<summary>Represente buildmenu function</summary>
///<param name="pa"></param>
///<param name="pcui"></param>
///<param name="rgp"></param>
///<param name="vac"></param>
///<param name="rep"></param>
///<param name="com"></param>
///<param name="rap"></param>
///<param name="zoom"></param>
///<param name="bs"></param>
///<param name="rh"></param>
///<param name="fiches"></param>
///<param name="prox"></param>
///<param name="asa"></param>
///<param name="panel"></param>
///<param name="sai"></param>
///<param name="prev"></param>
///<param name="colcon"></param>
function buildmenu($pa, $pcui, $rgp, $vac, $rep, $com, $rap, $zoom, $bs, $rh, $fiches, $prox, $asa, $panel, $sai, $prev, $colcon){
    global $userid;
    $menu=array();
    if($pa != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="PA/process/login.php";
        $menu[$u]["lib"]="PA";
        $menu[$u]["title"]="Plans d'action";
    }
    if($pcui != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="PCUI/process/login.php";
        $menu[$u]["lib"]="PCUI";
        $menu[$u]["title"]="Plan Communal d'Urgence et d'Intervention";
    }
    if($rgp != ""){
        $nb="";
        if($userid == "203"){
            $query="SELECT COUNT(id) AS nb FROM PV.pv WHERE typeid=2 AND statutid=1";
            $resultset=query($query);
            if($row=fetch($resultset)){
                $nb=$row["nb"];
                if($nb == "0")
                    $nb="";
                else
                    $nb=" ($nb)";
            }
        }
        else if($userid == "204"){
            $query="SELECT COUNT(id) AS nb FROM PV.pv WHERE (typeid=1 AND statutid=1) OR (typeid=2 AND statutid IN (1,3)) ";
            $resultset=query($query);
            if($row=fetch($resultset)){
                $nb=$row["nb"];
                if($nb == "0")
                    $nb="";
                else
                    $nb=" ($nb)";
            }
        }
        else{
            $query="SELECT ci, cq, cs FROM USERS.utilisateurs WHERE id='$userid'";
            $resultset=query($query);
            $chef=false;
            if($row=fetch($resultset)){
                if($row["ci"] == "1" || $row["cq"] == "1" || $row["cs"] == "1")
                    $chef=true;
            }
            if($chef)
                $query="SELECT COUNT(id) AS nb FROM PV.pv WHERE statutid IN ('8','10') ";
            else
                $query="SELECT COUNT(id) AS nb FROM PV.pv WHERE statutid IN ('7','9') AND verbalisant='".$userid."' ";
            $resultset=query($query);
            if($row=fetch($resultset)){
                $nb=$row["nb"];
                if($nb == "0")
                    $nb="";
                else
                    $nb=" ($nb)";
            }
        }
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="RGP/process/login.php";
        $menu[$u]["lib"]="RGP$nb";
        $menu[$u]["title"]="R&egrave;glement g&eacute;n&eacute;ral de police";
    }
    if($vac != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="VAC/process/login.php";
        $menu[$u]["lib"]="VAC";
        $menu[$u]["title"]="Vacanciers";
    }
    if($rep != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="REP/process/login.php";
        $menu[$u]["lib"]="REP";
        $menu[$u]["title"]="R&eacute;pertoire g&eacute;n&eacute;ral";
    }
    if($com != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="COM/process/login.php";
        $menu[$u]["lib"]="COM-DOC";
        $menu[$u]["title"]="Communication et biblioth&egrave;que";
    }
    if($rap != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="RAP/process/login.php";
        $menu[$u]["lib"]="RAP";
        $menu[$u]["title"]="Rapport journalier";
    }
    if($zoom != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="ZOOMADM/process/login.php";
        $menu[$u]["lib"]="ZOOM";
        $menu[$u]["title"]="Administration du ZOOM";
    }
    if($bs != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="BS/process/login.php";
        $menu[$u]["lib"]="BS";
        $menu[$u]["title"]="Bulletins de service &eacute;lectroniques";
    }
    if($rh != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="RH/process/login.php";
        $menu[$u]["lib"]="RH";
        $menu[$u]["title"]="Ressources humaines";
    }
    if($fiches != ""){
        $query="SELECT COUNT(fiche.id) AS nb ";
        $query .= " FROM FICHES.fiche ";
        $query .= " INNER JOIN FICHES.destinataire ON (fiche.id=destinataire.ficheid) ";
        $query .= " LEFT OUTER JOIN FICHES.lecture ON (lecture.ficheid=fiche.id AND lecture.userid=destinataire.userid) ";
        $query .= " WHERE statut IN ('1','2') AND destinataire.userid='".$userid."' AND lecture.id IS NULL";
        $resultset=query($query);
        $nbfiche="";
        if($row=fetch($resultset)){
            $nbfiche=$row["nb"];
            if($nbfiche == "0")
                $nbfiche="";
            else
                $nbfiche=" (".$nbfiche.")";
        }
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="FICHES/index.php";
        $menu[$u]["lib"]="FICHES$nbfiche";
        $menu[$u]["title"]="Gestion des fiches communales";
    }
    if($prox != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="DOM/process/login.php";
        $menu[$u]["lib"]="DOM";
        $menu[$u]["title"]="Domiciliations et apostilles";
    }
    if($asa != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="ASA/process/login.php";
        $menu[$u]["lib"]="ASA";
        $menu[$u]["title"]="ASA et personnes isol&eacute;es/fragiles";
    }
    if($panel != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="PANEL/process/login.php";
        $menu[$u]["lib"]="PANEL";
        $menu[$u]["title"]="Panels photo";
    }
    if($sai != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="SAI/process/login.php";
        $menu[$u]["lib"]="SAISIES";
        $menu[$u]["title"]="Registre des saisies";
    }
    if($prev != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="PREV/process/login.php";
        $menu[$u]["lib"]="PREV";
        $menu[$u]["title"]="Pr&eacute;vention";
    }
    if($colcon != ""){
        $u=count($menu);
        $menu[$u]=array();
        $menu[$u]["link"]="COLCON/process/login.php";
        $menu[$u]["lib"]="CC";
        $menu[$u]["title"]="Gestion des Coll&egrave;ges et Conseils de Police";
    }
    $last=0;
    if($pa != ""){
        $u=count($menu);
        $last=$u;
        $menu[$u]=array();
        $menu[$u]["link"]="rccu";
        $menu[$u]["lib"]="RCCU";
        $menu[$u]["title"]="SLA RCCU";
        $u++;
        $menu[$u]=array();
        $menu[$u]["link"]="https://polcommag.wixsite.com/polcommag";
        $menu[$u]["lib"]="PolComMag";
        $menu[$u]["title"]="PolCom Mag";
        if($userid == "13"){
            $u++;
            $menu[$u]=array();
            $menu[$u]["link"]="https://polcommag.wixsite.com/brochuredaccueil";
            $menu[$u]["lib"]="LA ZONE";
            $menu[$u]["title"]="Brochure d'accueil";
        }
    }
    $u=count($menu);
    $menu[$u]=array();
    $menu[$u]["link"]="index.php?changepwd=1";
    $menu[$u]["lib"]="MDP";
    $menu[$u]["title"]="Changer votre mot de passe";
    for($i=0; $i < count($menu); $i++){
        $menu[$i]["sel"]=false;
        $lib=$menu[$i]["lib"];
        if($lib == "RCCU" || $lib == "PolComMag" || $lib == "LA ZONE")
            $menu[$i]["new"]=true;
        else
            $menu[$i]["new"]=false;
    }
    return $menu;
}
///<summary>Represente calendarjs function</summary>
///<param name="abs" default=""></param>
function calendarjs($abs=""){
    global $malangue;
    ?>
<script language="javascript" type="text/javascript" src="include/datetimepicker.js">

//Date Time Picker script- by TengYong Ng of http://www.rainforestnet.com
//Script featured on JavaScript Kit (http://www.javascriptkit.com)
//For this script, visit http://www.javascriptkit.com

</script>
<SCRIPT>
	// Fonction qui permet d'ajouter soit une annee, un mois ou un jour a notre controleur de date
	function Add(objet, champ){

		// Lecture de la valeur date
		valeur = document.getElementById(objet+"hidden").value;

		if(valeur!=""){
			// Decoupage des champs et convertion des valeurs en entier
			annee = parseInt(valeur.substring(0,4));
			if(valeur.substring(5,7)=="08") mois=8;		// bug javascript
			else if(valeur.substring(5,7)=="09") mois=9;	// bug javascript
			else mois = parseInt(valeur.substring(5,7));
			if(valeur.substring(8,10)=="08") jour=8;	// bug javascript
			else if(valeur.substring(8,10)=="09") jour=9;	// bug javascript
			else jour = parseInt(valeur.substring(8,10));

			switch(champ){
				case 1:
					// ajouter une annee
					annee++;
					// cas des annees bisextiles
					// On evite d'arriver a des situations du genre 2012-02-29 + 1 an = 2013-02-29
					if(mois==2 && jour>28) jour=28;
					break;
				case 2:
					// ajouter un mois (si on est decembre, on incremente l'annee)
					if(mois==12){
						annee++;
						mois=1;
					} else mois++;
					// On evite d'arriver a des situations du genre 2012-01-30 + 1 mois = 2012-02-30
					if(mois==4 || mois==6 || mois==9 || mois==11){
						if(jour>30) jour=30;
					} else if(mois==2) {if(jour>28) jour=28;}
					break;
				case 3:
					// ajouter un jour
					if(mois==1 || mois ==3 || mois==5 || mois==7 || mois==8 || mois==10 || mois==12){
						// cas des mois en 31 jour
						if(jour==31){
							// on passe du 31 du mois au 1er du mois suivant
							jour=1;
							if(mois==12){
								mois=1;
								annee++;
							} else mois++;
						} else jour++;
					} else if(mois==4 || mois==6 || mois==9 || mois==11){
						// cas des mois en 30 jour
						if(jour==30){
							// on passe du 30 du mois au 1er du mois suivant
							jour=1;
							if(mois==12){
								mois=1;
								annee++;
							} else mois++;
						} else jour++;
					} else if(mois==2){
						// cas de fevrier
						if(annee % 4 == 0) {
							// si annee bissextile
							if(jour==29){
								// on passe du 29 fevrier au 1er mars
								jour=1;
								mois=3;
							} else jour++;
						} else {
							if(jour==28){
								// on passe du 28 fevrier au 1er mars
								jour=1;
								mois=3;
							} else jour++;
						}
					}
					break;
			}
			// Conversion des resultats en chaine de caracteres
			sannee = annee.toString();
			smois = mois.toString();
			if(smois.length==1) smois="0"+smois;
			sjour = jour.toString();
			if(sjour.length==1) sjour="0"+sjour;
			// on re-injecter les valeur dans le champ date et son champ cache
			//document.getElementById(objet).value = sannee+"-"+smois+"-"+sjour;
			document.getElementById(objet).value = sjour+"/"+smois+"/"+sannee;
			document.getElementById(objet+"hidden").value = sannee+"-"+smois+"-"+sjour;
		}
	}

	// Fonction qui permet de retirer soit une annee, un mois ou un jour a notre controleur de date
	function Sub(objet, champ, valeur){
		// Lecture de la valeur date
		valeur = document.getElementById(objet+"hidden").value;

		if(valeur!=""){
			// Decoupage des champs et convertion des valeurs en entier
			annee = parseInt(valeur.substring(0,4));
			if(valeur.substring(5,7)=="08") mois=8;		// bug javascript
			else if(valeur.substring(5,7)=="09") mois=9;	// bug javascript
			else mois = parseInt(valeur.substring(5,7));
			if(valeur.substring(8,10)=="08") jour=8;	// bug javascript
			else if(valeur.substring(8,10)=="09") jour=9;	// bug javascript
			else jour = parseInt(valeur.substring(8,10));

			switch(champ){
				case 1:
					// retirer une annee
					annee--;
					// cas des annees bisextiles
					// On evite d'arriver a des situations du genre 2012-02-29 - 1 an = 2011-02-29
					if(mois==2 && jour>28) jour=28;
					break;
				case 2:
					// retirer un mois (si on est janvier, on decremente l'annee)
					if(mois==1){
						annee--;
						mois=12;
					} else {
						mois--;
					}
					// On evite d'arriver a des situations du genre 2012-03-30 - 1 mois = 2012-02-30
					if(mois==4 || mois==6 || mois==9 || mois==11){
						if(jour>30) jour=30;
					} else if(mois==2) {if(jour>28) jour=28;}
					break;
				case 3:
					// retirer un jour
					if(mois==2 || mois ==4 || mois==6 || mois==8 || mois==9 || mois==11 || mois==1){
						// cas des mois qui suivent un mois en 31 jour
						if(jour==1){
							// on passe du 1er du mois au 31 du mois precedent
							jour=31;
							if(mois==1){
								mois=12;
								annee--;
							} else mois--;
						} else jour--;
					} else if(mois==5 || mois==7 || mois==10 || mois==12){
						// cas des mois qui suivent un mois en 30 jour
						if(jour==1){
							// on passe du 1er du mois au 30 du mois precedent
							jour=30;
							if(mois==1){
								mois=12;
								annee--;
							} else mois--;
						} else jour--;

					} else if(mois==3){
						// cas de fevrier
						if(annee % 4 == 0){
							// si annee bissextile
							if(jour==1){
								// on passe du 1er mars au 29 fevrier
								jour=29;
								mois=2;
							} else jour--;
						} else {
							if(jour==1){
								// on passe du 1er mars au 28 fevrier
								jour=28;
								mois=2;
							} else jour--;
						}
					}
					break;
			}
			sannee = annee.toString();
			smois = mois.toString();
			if(smois.length==1) smois="0"+smois;
			sjour = jour.toString();
			if(sjour.length==1) sjour="0"+sjour;
			//document.getElementById(objet).value = sannee+"-"+smois+"-"+sjour;
			document.getElementById(objet).value = sjour+"/"+smois+"/"+sannee;
			document.getElementById(objet+"hidden").value = sannee+"-"+smois+"-"+sjour;
		}
	}

	function isInt(value) {
	  return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
	}

	function checkchampmanuel(nom,type){
		valeur = document.getElementById(nom).value;
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
    ?> !");
				document.getElementById(nom).value = "01";
			}
			if(valeur.length=="1") document.getElementById(nom).value = "0"+valeur;
		}
		if(type=="m"){
			if(valeur=="") {
				document.getElementById(nom).value = "01";
				valeur = "01";
			}
			if(!isInt(valeur)){
				alert("<?php 
    if($malangue == "NL")
        echo "opgegeven datum bestaat niet";
    else{
        ?>Le mois n'est pas un nombre<?php 
    }
    ?> !");
				document.getElementById(nom).value = "01";
			}
			if(valeur<0 || valeur>12) {
				alert("<?php 
    if($malangue == "NL")
        echo "opgegeven datum bestaat niet";
    else{
        ?>Le mois est hors des limites (0-12)<?php 
    }
    ?> !");
				document.getElementById(nom).value = "01";
			}
			if(valeur.length=="1") document.getElementById(nom).value = "0"+valeur;
		}
		if(type=="a"){
			if(valeur=="") {
				document.getElementById(nom).value = "2000";
				valeur = "2000";
			}
			if(!isInt(valeur)){
				alert("<?php 
    if($malangue == "NL")
        echo "opgegeven datum bestaat niet";
    else{
        ?>L'annee n'est pas un nombre<?php 
    }
    ?> !");
				document.getElementById(nom).value = "2000";
			}
			if(valeur<1900 || valeur><?php 
    echo year() + 100 ?>) {
				alert("<?php 
    if($malangue == "NL")
        echo "opgegeven datum bestaat niet";
    else{
        ?>L'annee est hors des limites (1900-<?php 
        echo year() + 100 ?>)<?php 
    }
    ?> !");
				document.getElementById(nom).value = "2000";
			}
		}
	}
</SCRIPT><?php 
}
///<summary>Represente checkattack function</summary>
///<param name="ip"></param>
///<param name="login"></param>
function checkattack($ip, $login){
    global $db;
    $response=true;
    if(in_array($ip, array_filter(explode("|", IPB_WHITE_LIST_IPS)))){
        return $response;
    }
    $query="SELECT id FROM $db.bannedip WHERE ip='$ip' AND DATEDIFF(CURDATE(), date_) < 1";
    $resultset=query($query);
    if($row=fetch($resultset))
        $response=false;
    $query="SELECT COUNT(id) AS nb FROM $db.logsconnexion WHERE ip='$ip' AND date_=CURDATE() AND succes='0'";
    $resultset=query($query);
    if($row=fetch($resultset)){
        if($row["nb"] > 10){
            $response=false;
            $queryi="INSERT INTO $db.bannedip (ip, date_, heure) VALUES ('$ip', CURDATE(), CURTIME())";
            query($queryi);
        }
    }
    return $response;
}
///<summary>Represente checkbob function</summary>
function checkbob(){
    $query="SELECT id FROM ZOOM.campagnebob WHERE debut<=CURDATE() AND CURDATE()<=fin";
    $resultset=query($query);
    $found=false;
    if($row=fetch($resultset))
        $found=true;
    if(!$found){
        $an=year();
        $ann=year() + 1;
        $a=year()-1;
        $query="INSERT INTO ZOOM.campagnebob (debut, fin, periode) VALUES (CURDATE(), ";
        if(month() == "02" || month() == "03" || month() == "04" || month() == "05")
            $query .= "'$an-05-31', 'Hors periode $an - 1')";
        if(month() == "06" || month() == "07" || month() == "08")
            $query .= "'$an-08-31', 'Ete $an')";
        if(month() == "09" || month() == "10" || month() == "11")
            $query .= "'$an-11-30', 'Hors periode $an - 2')";
        if(month() == "12")
            $query .= "'$ann-01-31','Hiver $an-$ann')";
        if(month() == "01")
            $query .= "'$an-01-31', 'Hiver $a-$an')";
        query($query);
    }
}
///<summary>Represente checkfile function</summary>
///<param name="filename"></param>
function checkfile($filename){
    $fichier=
    $valide=false;
    if(!isset($_FILES[$filename])){
        ipb_die("'{$filename}' not set");
    }
    $fichier=$_FILES[$filename];
    $repertoire=$fichier['tmp_name'];
    $nomfichier=basename($fichier['name']);
    $extension=strrchr($nomfichier, '.');
    if(!empty($repertoire) && SecurExt($extension)){
        $valide=true;
        $finfo=new finfo(FILEINFO_MIME, MAGICDIR);
        $diagnostic=$finfo->file($repertoire);
        if(strrpos($diagnostic, "script") > -1)
            $valide=false;
        if(strrpos($diagnostic, "html") > -1)
            $valide=false;
        if(strrpos($diagnostic, "java") > -1)
            $valide=false;
        $ext=strtolower($extension);
        if($ext == "gif" || $ext == "jpg" || $ext == "jepg" || $ext == "png" || $ext == "jpe" || $ext == "tif"){
            if(strrpos($diagnostic, "image") == -1)
                $valide=false;
            if(strrpos($diagnostic, "application") > -1)
                $valide=false;
        }
        if($ext == "pdf"){
            if(strrpos($diagnostic, "pdf") == -1)
                $valide=false;
        }
        if($ext == "doc" || $ext == "xls" || $ext == "ppt" || $ext == "rtf"){
            if(strrpos($diagnostic, "ms-office") == -1)
                $valide=false;
        }
        if($ext == "docx" || $ext == "xlsx" || $ext == "pptx"){
            if(strrpos($diagnostic, "zip") == -1)
                $valide=false;
        }
        if($ext == "csv" || $ext == "txt"){
            if(strrpos($diagnostic, "text/plain") == -1)
                $valide=false;
            if(strrpos($diagnostic, "application") > -1)
                $valide=false;
        }
        if($ext == "zip"){
            if(strrpos($diagnostic, "application") > -1)
                $valide=false;
        }
    }
    return $valide;
}
///<summary>Represente checkname function</summary>
///<param name="entree"></param>
///<param name="type"></param>
function checkname($entree, $type){
    $entree=strtolower($entree);
    $sortie="";
    $autorises="abcdefghijklmnopqrstuvwxyz- '";
    $conv=array(
            "&agrave;"=>"a",
            "&acirc;"=>"a",
            "&auml;"=>"a",
            "&ccedil;"=>"c",
            "&eacute;"=>"e",
            "&egrave;"=>"e",
            "&ecirc;"=>"e",
            "&euml;"=>"e",
            "&icirc;"=>"i",
            "&iuml;"=>"i",
            "&ocirc;"=>"o",
            "&ouml;"=>"o",
            "&ugrave;"=>"u",
            "&ucirc;"=>"u",
            "&uuml;"=>"u",
            "&szlig;"=>"ss"
        );
    foreach($conv as $key=>$value){
        $entree=str_replace($key, $value, $entree);
    }
    $taille=strlen($entree);
    $premier=$taille == 1;
    $apresep=false;
    for($i=0; $i < $taille; $i++){
        $car=substr($entree, $i, 1);
        if(strpos($autorises, $car) !== false){
            if($i == 0 && ($car == "-" || $car == " " || $car == "'"))
                $sortie="";
            else{
                if($type == "n"){
                    $sortie .= strtoupper($car);
                }
                if($type == "p"){
                    if($i == 0)
                        $sortie .= strtoupper($car);
                    else if($apresep){
                        $sortie .= strtoupper($car);
                    }
                    else{
                        $sortie .= strtolower($car);
                    }
                }
            }
            $apresep=($car == "-" || $car == " " || $car == "'");
        }
    }
    return $sortie;
}
///<summary> check rback auto </summary>
///<remark></remark>
function checkrbacauthentication($userId, $password){
    global $db;
    $response=false;
    $ip=$_SERVER["REMOTE_ADDR"];
    if(!($auth_error=ipb_app()->session->autherror)){
        $auth_error=(object)array();
        ipb_app()->session->auth_error=$auth_error;
    }
    $bypass=(ipb_environment()->is("DEV") && ($password == IPB_DEV_BYPASSPWD));
    if(CheckAttack($ip, $userId)){
        $toser=new IPB_Toser("");
        if($bypass || $toser->CheckAccess($userId, $password)){
            $response=GetRBACIdentification($userId);
            $queryi="INSERT INTO $db.logsconnexion (nom, login, succes, ip, date_, heure) ";
            $queryi .= " VALUES ('', '{$userId}', '1', '$ip', CURDATE(), CURTIME())";
            query($queryi);
        }
        else{
            $auth_error->code=2;
            $auth_error->message="Login or Password Invalid";
            ipb_ilog("Login Or Passs Invalid - [".$userId."]");
        }
    }
    else{
        $auth_error->code=3;
        $auth_error->message="Banned IP";
        ipb_ilog("Banned IP - ".$ip);
    }
    return $response;
}
///<summary>Represente checksession function</summary>
///<param name="module"></param>
function checksession($module){
    global $con, $db;
    $res=array("0", "");
    $userid=@$_SESSION["globalidentifiedid"];
    switch($module){
        case "PA":
        ObtenirDroits("PA.user", "type", $userid, $res);
        break;
        case "PCUI":
        ObtenirDroits("PCUI.utilisateurs", "profil", $userid, $res);
        break;
        case "PCUI-EDIT":
        $pcuiuserid=$_SESSION["pcuiidentifiedid"];
        $query="SELECT themeid FROM PCUI.entites WHERE id='".$pcuiuserid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["themeid"];
        }
        else
            $res=CheckSession("PCUI-EDIT-LIMITE");
        break;
        case "PCUI-EDIT-LIMITE":
        $userid=$_SESSION["pcuiidentifiedid"];
        $query="SELECT entiteid FROM PCUI.pcuiuser WHERE id='".$userid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["entiteid"];
        }
        break;
        case "RGP":
        ObtenirDroits("RGP.utilisateurs", "profil", $userid, $res);
        break;
        case "RGPMOB":
        $query="SELECT droits.type ";
        $query .= " FROM RGP.droits ";
        $query .= " INNER JOIN RGP.utilisateurs ON (droits.userid=utilisateurs.id) ";
        $query .= " WHERE droits.module=7 AND utilisateurs.userid='".$userid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["type"];
        }
        break;
        case "REP":
        ObtenirDroits("USERS.access", "profil", $userid, $res);
        break;
        case "VAC":
        ObtenirDroits("VAC.access", "profil", $userid, $res);
        break;
        case "COM":
        ObtenirDroits("COM.access", "type", $userid, $res);
        break;
        case "ARRO":
        ObtenirDroits("ARRO.access", "type", $userid, $res);
        break;
        case "RAP":
        ObtenirDroits("RAP.access", "type", $userid, $res);
        break;
        case "ZOOM":
        if($userid == "")
            $userid=$_SESSION["login1"];
        $query="SELECT service FROM USERS.utilisateurs WHERE type=0 AND service<>'' AND id='".$userid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["service"];
        }
        break;
        case "ZOOMADM":
        ObtenirDroits("ZOOM.access", "type", $userid, $res);
        break;
        case "BS":
        ObtenirDroits("BS.access", "profil", $userid, $res);
        break;
        case "CIA2":
        $ciauserid=@$_SESSION["logincia"];
        $query="SELECT unitid FROM $db.users WHERE id='".$ciauserid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["unitid"];
        }
        break;
        case "CIATEST":
        $query="SELECT unitid FROM iTEST.users WHERE id='".$ciauserid."'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $res[0]="1";
            $res[1]=$row["unitid"];
        }
        break;
        case "DOM":
        ObtenirDroits("PROXI.access", "type", $userid, $res);
        break;
        case "ASA":
        ObtenirDroits("PROXI.accessasa", "type", $userid, $res);
        break;
        case "RH":
        ObtenirDroits("RH.access", "type", $userid, $res);
        break;
        case "PANEL":
        ObtenirDroits("PANEL.access", "type", $userid, $res);
        break;
        case "DOC":
        ObtenirDroits("DOC.access", "type", $userid, $res);
        break;
        case "POLL":
        if($userid == "46" || $userid == "13" || $userid == "5" || $userid == "45"){
            $res[0]="1";
        }
        break;
        case "ARCH":
        ObtenirDroits("ARCH.access", "type", $userid, $res);
        break;
        case "PREV":
        ObtenirDroits("PREV.access", "type", $userid, $res);
        break;
        case "FICHE":
        ObtenirDroits("FICHES.acces", "profil", $userid, $res);
        break;
        case "PV":
        ObtenirDroits("PV.access", "type", $userid, $res);
        break;
        case "GPI":
        ObtenirDroits("GPI.access", "type", $userid, $res);
        break;
        case "SAI":
        ObtenirDroits("USERS.saisieuser", "profil", $userid, $res);
        break;
        case "COLCON":
        ObtenirDroits("COLCON.access", "type", $userid, $res);
        break;
    }
    return $res;
}
///<summary>Represente checksessionrgp function</summary>
///<param name="sousmodule"></param>
function checksessionrgp($sousmodule){
    $res=array("0", "");
    $userid=$_SESSION["globalidentifiedid"];
    $query="SELECT type FROM RGP.droits WHERE module='".$sousmodule."' AND userid='".$userid."'";
    $resultset=query($query);
    if($row=fetch($resultset)){
        $res[0]="1";
        $res[1]=$row["type"];
    }
    return $res;
}
///ERROR: finfo warning on continuation level for magicdir
function checkstrongpwd($pwd){
    $res=false;
    $points=0;
    $taille=
    $lettre=
    $minuscule=
    $majuscule=
    $chiffre=
    $special=
    $dollar=false;
    if(strlen($pwd)>=8)
        $taille=true;;
    for($i=0; $i < strlen($pwd); $i++){
        $car=$pwd[$i];
        if(ctype_alpha($car))
            $lettre=true;
        if(ctype_digit($car))
            $chiffre=true;
        if(!ctype_alpha($car) && !ctype_digit($car) && $car != "$")
            $special=1;
        if(ctype_upper($car))
            $majuscule=1;
        if(ctype_lower($car))
            $minuscule=1;
        if($car == "$")
            $dollar=true;
    }
    if($taille)
        $points++;
    if($lettre)
        $points++;
    if($majuscule && $minuscule)
        $points++;
    if($chiffre)
        $points++;
    if($special)
        $points++;
    if($dollar || !$taille)
        $points=0;
    if($points > 3)
        $res=true;
    return $res;
}
///<summary>Represente checktfl function</summary>
function checktfl(){
    global $db;
    $query="UPDATE $db.tflpersonne SET categorisation='C', datecat=CURDATE() WHERE categorisation='B' AND DATEDIFF(CURDATE(),datecat)>180";
    query($query);
}
///<summary>Represente checkvacancier function</summary>
function checkvacancier(){
    $sujet="Vacanciers - alerte";
    $headers='From:Police Comines-Warneton<info@polcom.be>'."\n";
    $headers .= 'Content-Type:text/html;charset="iso-8859-1"'."\n";
    $headers .= 'Content-Transfer-Encoding:8bit';
    $query="SELECT id, reference, depart, retour ";
    $query .= " FROM VAC.absence ";
    $query .= " WHERE depart < CURDATE() AND NOT EXISTS (SELECT id FROM ZOOM.vac WHERE vac.ref=absence.reference) ";
    $query .= " AND datediff(CURDATE(),depart)>datediff(retour,depart)*3/4 AND alerte='0'";
    $resultset=query($query);
    while($row=fetch($resultset)){
        $id=$row["id"];
        $message="Le vacancier ".$row["reference"]." (du ".$row["depart"]." au ".$row["retour"].") n'a toujours pas eu de passage !";
        mail("police.comines@gmail.com", $sujet, $message, $headers);
        mail("appui.5318@gmail.com", $sujet, $message, $headers);
        $query2="UPDATE VAC.absence SET alerte='1' WHERE id='$id'";
        query($query2);
    }
}
///<summary>Represente cleanback function</summary>
///<param name="text"></param>
function cleanback($text){
    $text=str_replace("<br>", "\n", $text);
    $text=str_replace("&eacute;", "é", $text);
    $text=str_replace("&Eacute;", "É", $text);
    $text=str_replace("&egrave;", "è", $text);
    $text=str_replace("&agrave;", "à", $text);
    $text=str_replace("&ugrave;", "ù", $text);
    $text=str_replace("&ecirc;", "ê", $text);
    $text=str_replace("&euml;", "ë", $text);
    $text=str_replace("&ocirc;", "ô", $text);
    $text=str_replace("&ouml;", "ö", $text);
    $text=str_replace("&ucirc;", "û", $text);
    $text=str_replace("&acirc;", "â", $text);
    $text=str_replace("&uuml;", "ü", $text);
    $text=str_replace("&icirc;", "î", $text);
    $text=str_replace("&iuml;", "ï", $text);
    $text=str_replace("&ccedil;", "ç", $text);
    $text=str_replace("&oelig;", "oe", $text);
    $text=str_replace("&aelig;", "ae", $text);
    $text=str_replace("&#39;", "'", $text);
    return $text;
}
///<summary>Represente cleanbackjs function</summary>
///<param name="text"></param>
function cleanbackjs($text){
    $text=str_replace("&eacute;", "é", $text);
    $text=str_replace("&egrave;", "è", $text);
    $text=str_replace("&agrave;", "à", $text);
    $text=str_replace("&ugrave;", "ù", $text);
    $text=str_replace("&ecirc;", "ê", $text);
    $text=str_replace("&euml;", "ë", $text);
    $text=str_replace("&ocirc;", "ô", $text);
    $text=str_replace("&ouml;", "ö", $text);
    $text=str_replace("&ucirc;", "û", $text);
    $text=str_replace("&acirc;", "â", $text);
    $text=str_replace("&uuml;", "ü", $text);
    $text=str_replace("&icirc;", "î", $text);
    $text=str_replace("&iuml;", "ï", $text);
    $text=str_replace("&ccedil;", "ç", $text);
    $text=str_replace("&oelig;", "oe", $text);
    $text=str_replace("&aelig;", "ae", $text);
    return $text;
}
///<summary>Represente cleantext function</summary>
///<param name="text"></param>
function cleantext($text){
    if(empty($text))
        return $text;
    $text=str_replace("'", addslashes("'"), $text);
    $text=str_ireplace("&lt;", "<", $text);
    $text=str_ireplace("&gt;", ">", $text);
    $text=str_ireplace("<script", "", $text);
    $text=str_ireplace("<object", "", $text);
    $text=str_ireplace("<applet", "", $text);
    $text=str_ireplace("<embed", "", $text);
    $text=str_ireplace("&#039;", "''", $text);
    $text=str_ireplace("&quot;", "''", $text);
    $text=str_ireplace("&amp;quot;", "''", $text);
    return $text;
}
///<summary>Represente close function</summary>
///<param name="con"></param>
function close($con){
    if($con)
        @$con->close();
}
///<summary>Represente connect function</summary>
///<param name="host"></param>
///<param name="user"></param>
///<param name="password"></param>
///<param name="db"></param>
function connect($host, $user, $password, $db){
    $con=new mysqli($host, $user, $password, $db);
    if(mysqli_connect_errno()){
        return null;
    }
    else{
        mysqli_select_db($con, $db);
    }
    $con->set_charset("UTF8");
    return $con;
}
///<summary>Represente convertdate function</summary>
///<param name="date"></param>
function convertdate($date){
    $result=$date;
    if(strlen($date) == 10){
        if(strpos($date, "-") > -1)
            $result=substr($date, 8, 2)."/".substr($date, 5, 2)."/".substr($date, 0, 4);
        else if(strpos($date, "/") !== false)
            $result=substr($date, 6, 4)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
    }
    return $result;
}
///<summary>Represente converttime function</summary>
///<param name="date"></param>
function converttime($date){
    $res="";
    if(strlen($date) == 14){
        $res=substr($date, 4, 2)."/".substr($date, 6, 2)."/".substr($date, 0, 4)." ";
        $res .= substr($date, 8, 2).":".substr($date, 10, 2).":".substr($date, 12, 2);
    }
    return $res;
}
///<summary>Represente ctrltel function</summary>
///<param name="num"></param>
///<param name="type"></param>
///<param name="name"></param>
///<param name="id" default=""></param>
///<param name="mobile" default="0"></param>
function ctrltel($num, $type, $name, $id="", $mobile="0"){
    if($id == "")
        $id=$name;
    if($type == "")
        $type="0";
    ?>
		<script>
			function ShowCtrlTel_<?php echo $id ?>(type){
				//alert(type);
				document.getElementById('divctrl_<?php echo $id ?>').style.display="none";
				document.getElementById('divctrl1_<?php echo $id ?>').style.display="none";
				document.getElementById('divctrl2_<?php echo $id ?>').style.display="none";
				document.getElementById('divctrl3_<?php echo $id ?>').style.display="none";
				document.getElementById('divctrl4_<?php echo $id ?>').style.display="none";
				switch(type){
					case "0":
						document.getElementById('divctrl_<?php echo $id ?>').style.display="block";
						break;
					case "1":
						document.getElementById('divctrl1_<?php echo $id ?>').style.display="block";
						break;
					case "2":
						document.getElementById('divctrl2_<?php echo $id ?>').style.display="block";
						break;
					case "3":
						document.getElementById('divctrl3_<?php echo $id ?>').style.display="block";
						break;
					case "4":
						document.getElementById('divctrl4_<?php echo $id ?>').style.display="block";
						break;
				}
			}
			function Focalise_<?php echo $id ?>(obj,id,size){
				if(obj.value.length==size) {
					document.getElementById(id).focus();
				}
			}
		</script>
		<select name="type-<?php echo $name ?>" onchange="ShowCtrlTel_<?php echo $id ?>(this.value)">
			<option value="1"<?php 
    if($type == "1")
        echo "SELECTED";
    ?>>Fixe belge</option>
			<option value="2"<?php 
    if($type == "2")
        echo "SELECTED";
    ?>>GSM belge</option>
			<option value="3"<?php 
    if($type == "3")
        echo "SELECTED";
    ?>>Fixe fran&ccedil;ais</option>
			<option value="4"<?php 
    if($type == "4")
        echo "SELECTED";
    ?>>GSM fran&ccedil;ais</option>
			<option value="0"<?php 
    if($type == "0")
        echo "SELECTED";
    ?>>Format libre</option>
		</select>
			<div id="divctrl_<?php echo $id ?>"<?php 
    if($type != "0"){
        ?>style="display:none"<?php 
    }
    ?>><input type="text" name="<?php echo $name ?>" id="<?php echo $id ?>" placeholder="Format libre" value="<?php echo $num ?>"></div>
			<div id="divctrl1_<?php echo $id ?>"<?php 
    if($type != "1"){
        ?>style="display:none"<?php 
    }
    ?>><?php 
    $val1=
    $val2=
    $val3=
    $val4=
    $val5="";
    if($type == 1 && strlen($num)>=12){
        $val1=substr($num, 0, 3);
        $val2=substr($num, 4, 2);
        $val3=substr($num, 7, 2);
        $val4=substr($num, 10, 2);
    }
    ?>
				<input type="text" name="pref1_<?php echo $name ?>" id="pref1_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "35";
    else
        echo "60";
    ?>px" maxlength="3" placeholder="056" value="<?php echo $val1 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num11_<?php echo $id ?>',3)">
				<input type="text" name="num11_<?php echo $name ?>" id="num11_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val2 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num21_<?php echo $id ?>',2)">
				<input type="text" name="num21_<?php echo $name ?>" id="num21_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val3 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num31_<?php echo $id ?>',2)">
				<input type="text" name="num31_<?php echo $name ?>" id="num31_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val4 ?>">
			</div>
			<div id="divctrl2_<?php echo $id ?>"<?php 
    if($type != "2"){
        ?>style="display:none"<?php 
    }
    ?>><?php 
    if($type == 2 && strlen($num)>=13){
        $val1=substr($num, 0, 4);
        $val2=substr($num, 5, 2);
        $val3=substr($num, 8, 2);
        $val4=substr($num, 11, 2);
    }
    ?>
				<input type="text" name="pref2_<?php echo $name ?>" id="pref2_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "35";
    else
        echo "75";
    ?>px" maxlength="4" placeholder="0470" value="<?php echo $val1 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num12_<?php echo $id ?>',4)">
				<input type="text" name="num12_<?php echo $name ?>" id="num12_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val2 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num22_<?php echo $id ?>',2)">
				<input type="text" name="num22_<?php echo $name ?>" id="num22_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val3 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num32_<?php echo $id ?>',2)">
				<input type="text" name="num32_<?php echo $name ?>" id="num32_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val4 ?>">
			</div>
			<div id="divctrl3_<?php echo $id ?>"<?php 
    if($type != "3"){
        ?>style="display:none"<?php 
    }
    ?>><?php 
    if(($type == 3 || $type == "4") && strlen($num)>=18){
        $val1=substr($num, 5, 1);
        $val2=substr($num, 7, 2);
        $val3=substr($num, 10, 2);
        $val4=substr($num, 13, 2);
        $val5=substr($num, 16, 2);
    }
    ?>
				0033
				<input type="text" name="pref3_<?php echo $name ?>" id="pref3_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "15";
    else
        echo "30";
    ?>px" maxlength="1" placeholder="3" value="<?php echo $val1 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num13_<?php echo $id ?>',1)">
				<input type="text" name="num13_<?php echo $name ?>" id="num13_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val2 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num23_<?php echo $id ?>',2)">
				<input type="text" name="num23_<?php echo $name ?>" id="num23_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val3 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num33_<?php echo $id ?>',2)">
				<input type="text" name="num33_<?php echo $name ?>" id="num33_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val4 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num43_<?php echo $id ?>',2)">
				<input type="text" name="num43_<?php echo $name ?>" id="num43_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val5 ?>">
			</div>
			<div id="divctrl4_<?php echo $id ?>"<?php 
    if($type != "4"){
        ?>style="display:none"<?php 
    }
    ?>>
				0033
				<input type="text" name="pref4_<?php echo $name ?>" id="pref4_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "15";
    else
        echo "30";
    ?>px" maxlength="1" placeholder="6" value="<?php echo $val1 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num14_<?php echo $id ?>',1)">
				<input type="text" name="num14_<?php echo $name ?>" id="num14_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val2 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num24_<?php echo $id ?>',2)">
				<input type="text" name="num24_<?php echo $name ?>" id="num24_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val3 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num34_<?php echo $id ?>',2)">
				<input type="text" name="num34_<?php echo $name ?>" id="num34_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val4 ?>" onkeyup="Focalise_<?php echo $id ?>(this,'num44_<?php echo $id ?>',2)">
				<input type="text" name="num44_<?php echo $name ?>" id="num44_<?php echo $id ?>" style="width:<?php 
    if($mobile == "0")
        echo "25";
    else
        echo "40";
    ?>px" maxlength="2" placeholder="00" value="<?php echo $val5 ?>">
			</div><?php 
}
///<summary>Represente day function</summary>
function day(){
    $date=getdate();
    $temp=$date["mday"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    return $temp;
}
///<summary>Represente debugtrace function</summary>
///<param name="txt"></param>
///<param name="userid"></param>
function debugtrace($txt, $userid){
    global $db;
    $txt=str_replace("'", "''", $txt);
    $query="INSERT INTO $db.bug (date_, heure, userid, texte, type_)  VALUES (CURDATE(), CURTIME(), $userid, '$txt', 1)";
    query($query);
}
///<summary>Represente existintest function</summary>
///<param name="userid"></param>
function existintest($userid){
    global $db;
    $response=false;
    if(CIA_ENVIRONMENT == "COM"){
        $pcuser=@$_SESSION["polcomsoftuser"];
        if($pcuser == "1"){
            $query="SELECT login FROM USERS.utilisateurs WHERE id='$userid'";
        }
        else{
            $query="SELECT login FROM $db.users WHERE id='$userid'";
        }
        $resultset=query($query);
        $login="";
        if($row=fetch($resultset)){
            $login=$row["login"];
        }
        $query="SELECT id FROM iTEST.users WHERE login='$login'";
        $resultset=query($query);
        if($row=fetch($resultset)){
            $response=true;
        }
    }
    return $response;
}
///<summary>Represente fetch function</summary>
///<param name="resultat"></param>
function fetch($resultat){
    if($resultat === null){
        ipb_ilog(ipb_ob_get_func("ipb_trace", 1));
        return null;
    }
    if(is_bool($resultat)){
        return $resultat;
    }
    return mysqli_fetch_assoc($resultat);
}
///<summary>Represente fill function</summary>
///<param name="val"></param>
///<param name="max"></param>
///<param name="car"></param>
function fill($val, $max, $car){
    while(strlen($val) < $max)
        $val=$car.$val;
    return $val;
}
///<summary>Represente get function</summary>
///<param name="paramName"></param>
///<param name="get"></param>
///<param name="post"></param>
///<param name="defaultvalue" default=""></param>
function get($paramName, $get, $post, $defaultvalue=""){
    global $con;
    $temp="";
    if(array_key_exists($paramName, $get))
        $temp=$get[$paramName];
    else if(array_key_exists($paramName, $post))
        $temp=$post[$paramName];
    if($temp == "")
        $temp=$defaultvalue;
    if($con != "")
        mysqli_real_escape_string($con, $temp);
    $temp=ajustGet($temp);
    return $temp;
}
///<summary>Represente get_months function</summary>
///<param name="lang"></param>
function get_months($lang){
    $lib=array(
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre"
        );
    if($lang == "NL")
        $lib=array(
            "Januari",
            "Februari",
            "Maart",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Augustus",
            "September",
            "October",
            "November",
            "December"
        );
    if($lang == "DE")
        $lib=array(
            "Januar",
            "Februar",
            "März",
            "April",
            "Mai",
            "Juni",
            "Juli",
            "August",
            "September",
            "Oktober",
            "November",
            "Dezember"
        );
    return $lib;
}
///<summary>Represente getcalendar function</summary>
///<param name="nom"></param>
///<param name="valeur"></param>
///<param name="nomid" default=""></param>
function getcalendar($nom, $valeur, $nomid=""){
    $valeurshow=ConvertDate($valeur);
    if($nomid == "")
        $nomid=$nom;
    ?>
<TABLE border="0" cellpadding="0" cellspacing="2">
	<tr>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
	<tr>
		<TD colspan="3">
			<input type="hidden" name="<?php echo $nom ?>" id="<?php echo $nomid ?>hidden" value="<?php echo $valeur ?>">
			<input type="text" id="<?php echo $nomid ?>" maxlength="10" size="10" value="<?php echo $valeurshow ?>" disabled="true" style="font-weight: bold">
		</td>
		<td><a href="javascript:NewCal('<?php echo $nomid ?>','ddmmyyyy',false,24)"><img src="/assets/img/cal.gif" height="25px" class="calendar"></a></td>
	</tr>
	<tr>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
</table><?php 
}
///<summary>Represente getcalendarabs function</summary>
///<param name="nom"></param>
///<param name="valeur"></param>
///<param name="nomid" default=""></param>
function getcalendarabs($nom, $valeur, $nomid=""){
    $valeurshow=ConvertDate($valeur);
    if($nomid == "")
        $nomid=$nom;
    ?>
<TABLE border="0" cellpadding="0" cellspacing="2">
	<tr>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
	<tr>
		<TD colspan="3">
			<input type="hidden" name="<?php echo $nom ?>" id="<?php echo $nomid ?>hidden" value="<?php echo $valeur ?>">
			<input type="text" id="<?php echo $nomid ?>" maxlength="10" size="10" value="<?php echo $valeurshow ?>" disabled="true" style="font-weight: bold">
		</td>
		<td><a href="javascript:NewCal('<?php echo $nomid ?>','ddmmyyyy',false,24)"><img src="/assets/img/cal.gif" height="100%"></a></td>
	</tr>
	<tr>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
</table><?php 
}
///<summary>Represente getcalendaripb function</summary>
///<param name="nom"></param>
///<param name="valeur"></param>
///<param name="nomid"></param>
///<param name="autreid"></param>
function getcalendaripb($nom, $valeur, $nomid, $autreid){
    $valeurshow=ConvertDate($valeur);
    ?>
<TABLE border="0" cellpadding="0" cellspacing="2">
	<tr>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',3);Add('<?php echo $autreid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',2);Add('<?php echo $autreid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/up.png" onclick="Add('<?php echo $nomid ?>',1);Add('<?php echo $autreid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
	<tr>
		<TD colspan="3">
			<input type="hidden" name="<?php echo $nom ?>" id="<?php echo $nomid ?>hidden" value="<?php echo $valeur ?>">
			<input type="text" id="<?php echo $nomid ?>" maxlength="10" size="10" value="<?php echo $valeurshow ?>" disabled="true" style="font-weight: bold">
		</td>
		<td><a href="javascript:NewCal('<?php echo $nomid ?>','ddmmyyyy',false,24)"><img src="/assets/img/cal.gif" height="25px" class="calendar"></a></td>
	</tr>
	<tr>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',3);Sub('<?php echo $autreid ?>',3)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',2);Sub('<?php echo $autreid ?>',2)" width="100%"></td>
		<td><img src="/assets/img/down.png" onclick="Sub('<?php echo $nomid ?>',1);Sub('<?php echo $autreid ?>',1)" width="100%"></td>
		<td></td>
	</tr>
</table><?php 
}
///<summary>Represente getcalendarmanuel function</summary>
///<param name="nom"></param>
///<param name="valeur"></param>
///<param name="nomid" default=""></param>
function getcalendarmanuel($nom, $valeur, $nomid=""){
    global $malangue;
    $jour=
    $mois=
    $an="";
    if($valeur != ""){
        $jour=substr($valeur, 8, 2);
        $mois=substr($valeur, 5, 2);
        $an=substr($valeur, 0, 4);
    }
    $jj="JJ";
    $mm="MM";
    $aa="AAAA";
    if($malangue == "NL"){
        $jj="DD";
        $mm="MM";
        $aa="JJJJ";
    }
    if($malangue == "DE"){
        $jj="TT";
        $mm="MM";
        $aa="JJJJ";
    }
    if($nomid == "")
        $nomid=$nom;
    ?>
<script>
	function nextcal_<?php echo $nomid ?>(step, val){
		if(val.length==2){
			if(step=="j") document.getElementById('<?php echo $nomid ?>-m').focus();
			if(step=="m") document.getElementById('<?php echo $nomid ?>-a').focus();
		}
	}
</script>
<table border="0px solid black" cellpadding="0" cellspacing="2">
	<tr>
		<td><input type="text" name="<?php echo $nom ?>-j" id="<?php echo $nomid ?>-j" value="<?php echo $jour ?>" style="width:50px" placeholder="<?php 
    echo $jj;
    ?>" onkeyup="nextcal_<?php echo $nomid ?>('j',this.value)" onblur="checkchampmanuel('<?php echo $nomid ?>-j','j')" maxlength="2"></td>
		<td><input type="text" name="<?php echo $nom ?>-m" id="<?php echo $nomid ?>-m" value="<?php echo $mois ?>" style="width:50px" placeholder="<?php 
    echo $mm;
    ?>" onkeyup="nextcal_<?php echo $nomid ?>('m',this.value)" onblur="checkchampmanuel('<?php echo $nomid ?>-m','m')" maxlength="2"></td>
		<td><input type="text" name="<?php echo $nom ?>-a" id="<?php echo $nomid ?>-a" value="<?php echo $an ?>" style="width:80px" placeholder="<?php 
    echo $aa;
    ?>" onblur="checkchampmanuel('<?php echo $nomid ?>-a','a')" maxlength="4"></td>
	</tr>
</table><?php 
}
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
///<summary>Represente getcountries function</summary>
///<param name="lang" default="NULL"></param>
function getcountries($lang=NULL){
        static $langC=array("NL"=>"D", "FR"=>"F", "DE"=>"G");
    if((empty($lang))){
        $lang=ipb_app()->getCurrentLang();
    }
    if(!ipb_settings("no_reftab")){
        $m=new Model_Reftab();
        $tab=array();
        $tab[]=array("code"=>"", "lib"=>"---");
        if($cd=$m->GetrCou()){
            foreach($cd as $rows){
                /// TODO : Countries CHECK FOR DATE

                $tab[]=array(
                        "code"=>$rows["CouIso"],
                        "lib"=>$rows["CouTextB". $langC[$lang]]
                    );
            }
        }
        else{
            //+ not connexions to reftab or empty list
        }
        return $tab;
    }
    $country=array();
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="";
    $country[$u]["lib"]="---";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AD";
    $country[$u]["lib"]="Andorra";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AE";
    $country[$u]["lib"]="United Arab Emirates";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AF";
    $country[$u]["lib"]="Afghanistan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AG";
    $country[$u]["lib"]="Antigua & Barbuda";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AI";
    $country[$u]["lib"]="Anguilla";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AL";
    $country[$u]["lib"]="Albania";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AM";
    $country[$u]["lib"]="Armenia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AN";
    $country[$u]["lib"]="Netherlands Antilles";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AO";
    $country[$u]["lib"]="Angola";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AQ";
    $country[$u]["lib"]="Antarctica";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AR";
    $country[$u]["lib"]="Argentina";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AS";
    $country[$u]["lib"]="American Samoa";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AT";
    $country[$u]["lib"]="Austria";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AU";
    $country[$u]["lib"]="Australia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AW";
    $country[$u]["lib"]="Aruba";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="AZ";
    $country[$u]["lib"]="Azerbaijan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BA";
    $country[$u]["lib"]="Bosnia and Herzegovina";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BB";
    $country[$u]["lib"]="Barbados";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BD";
    $country[$u]["lib"]="Bangladesh";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BE";
    $country[$u]["lib"]="Belgium";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BF";
    $country[$u]["lib"]="Burkina Faso";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BG";
    $country[$u]["lib"]="Bulgaria";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BH";
    $country[$u]["lib"]="Bahrain";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BI";
    $country[$u]["lib"]="Burundi";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BJ";
    $country[$u]["lib"]="Benin";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BM";
    $country[$u]["lib"]="Bermuda";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BN";
    $country[$u]["lib"]="Brunei Darussalam";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BO";
    $country[$u]["lib"]="Bolivia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BR";
    $country[$u]["lib"]="Brazil";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BS";
    $country[$u]["lib"]="Bahama";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BT";
    $country[$u]["lib"]="Bhutan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BV";
    $country[$u]["lib"]="Bouvet Island";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BW";
    $country[$u]["lib"]="Botswana";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BY";
    $country[$u]["lib"]="Belarus";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="BZ";
    $country[$u]["lib"]="Belize";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CA";
    $country[$u]["lib"]="Canada";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CC";
    $country[$u]["lib"]="Cocos (Keeling) Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CF";
    $country[$u]["lib"]="Central African Republic";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CG";
    $country[$u]["lib"]="Congo";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CH";
    $country[$u]["lib"]="Switzerland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CI";
    $country[$u]["lib"]="C&ocirc;te D'ivoire";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CK";
    $country[$u]["lib"]="Cook Iislands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CL";
    $country[$u]["lib"]="Chile";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CM";
    $country[$u]["lib"]="Cameroon";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CN";
    $country[$u]["lib"]="China";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CO";
    $country[$u]["lib"]="Colombia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CR";
    $country[$u]["lib"]="Costa Rica";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CU";
    $country[$u]["lib"]="Cuba";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CV";
    $country[$u]["lib"]="Cape Verde";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CX";
    $country[$u]["lib"]="Christmas Island";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CY";
    $country[$u]["lib"]="Cyprus";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="CZ";
    $country[$u]["lib"]="Czech Republic";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DE";
    $country[$u]["lib"]="Germany";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DJ";
    $country[$u]["lib"]="Djibouti";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DK";
    $country[$u]["lib"]="Denmark";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DM";
    $country[$u]["lib"]="Dominica";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DO";
    $country[$u]["lib"]="Dominican Republic";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="DZ";
    $country[$u]["lib"]="Algeria";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="EC";
    $country[$u]["lib"]="Ecuador";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="EE";
    $country[$u]["lib"]="Estonia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="EG";
    $country[$u]["lib"]="Egypt";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="EH";
    $country[$u]["lib"]="Western Sahara";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ER";
    $country[$u]["lib"]="Eritrea";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ES";
    $country[$u]["lib"]="Spain";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ET";
    $country[$u]["lib"]="Ethiopia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FI";
    $country[$u]["lib"]="Finland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FJ";
    $country[$u]["lib"]="Fiji";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FK";
    $country[$u]["lib"]="Falkland Islands (Malvinas)";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FM";
    $country[$u]["lib"]="Micronesia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FO";
    $country[$u]["lib"]="Faroe Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FR";
    $country[$u]["lib"]="France";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="FX";
    $country[$u]["lib"]="France, Metropolitan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GA";
    $country[$u]["lib"]="Gabon";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GB";
    $country[$u]["lib"]="United Kingdom (Great Britain)";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GD";
    $country[$u]["lib"]="Grenada";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GE";
    $country[$u]["lib"]="Georgia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GF";
    $country[$u]["lib"]="French Guiana";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GH";
    $country[$u]["lib"]="Ghana";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GI";
    $country[$u]["lib"]="Gibraltar";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GL";
    $country[$u]["lib"]="Greenland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GM";
    $country[$u]["lib"]="Gambia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GN";
    $country[$u]["lib"]="Guinea";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GP";
    $country[$u]["lib"]="Guadeloupe";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GQ";
    $country[$u]["lib"]="Equatorial Guinea";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GR";
    $country[$u]["lib"]="Greece";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GS";
    $country[$u]["lib"]="South Georgia and the South Sandwich Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GT";
    $country[$u]["lib"]="Guatemala";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GU";
    $country[$u]["lib"]="Guam";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GW";
    $country[$u]["lib"]="Guinea-Bissau";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="GY";
    $country[$u]["lib"]="Guyana";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HK";
    $country[$u]["lib"]="Hong Kong";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HM";
    $country[$u]["lib"]="Heard & McDonald Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HN";
    $country[$u]["lib"]="Honduras";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HR";
    $country[$u]["lib"]="Croatia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HT";
    $country[$u]["lib"]="Haiti";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="HU";
    $country[$u]["lib"]="Hungary";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ID";
    $country[$u]["lib"]="Indonesia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IE";
    $country[$u]["lib"]="Ireland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IL";
    $country[$u]["lib"]="Israel";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IN";
    $country[$u]["lib"]="India";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IO";
    $country[$u]["lib"]="British Indian Ocean Territory";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IQ";
    $country[$u]["lib"]="Iraq";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IR";
    $country[$u]["lib"]="Islamic Republic of Iran";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IS";
    $country[$u]["lib"]="Iceland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="IT";
    $country[$u]["lib"]="Italy";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="JM";
    $country[$u]["lib"]="Jamaica";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="JO";
    $country[$u]["lib"]="Jordan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="JP";
    $country[$u]["lib"]="Japan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KE";
    $country[$u]["lib"]="Kenya";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KG";
    $country[$u]["lib"]="Kyrgyzstan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KH";
    $country[$u]["lib"]="Cambodia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KI";
    $country[$u]["lib"]="Kiribati";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KM";
    $country[$u]["lib"]="Comoros";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KN";
    $country[$u]["lib"]="St. Kitts and Nevis";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KP";
    $country[$u]["lib"]="Korea, Democratic People's Republic of";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KR";
    $country[$u]["lib"]="Korea, Republic of";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KW";
    $country[$u]["lib"]="Kuwait";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KY";
    $country[$u]["lib"]="Cayman Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="KZ";
    $country[$u]["lib"]="Kazakhstan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LA";
    $country[$u]["lib"]="Lao People's Democratic Republic";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LB";
    $country[$u]["lib"]="Lebanon";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LC";
    $country[$u]["lib"]="Saint Lucia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LI";
    $country[$u]["lib"]="Liechtenstein";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LK";
    $country[$u]["lib"]="Sri Lanka";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LR";
    $country[$u]["lib"]="Liberia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LS";
    $country[$u]["lib"]="Lesotho";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LT";
    $country[$u]["lib"]="Lithuania";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LU";
    $country[$u]["lib"]="Luxembourg";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LV";
    $country[$u]["lib"]="Latvia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="LY";
    $country[$u]["lib"]="Libyan Arab Jamahiriya";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MA";
    $country[$u]["lib"]="Morocco";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MC";
    $country[$u]["lib"]="Monaco";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MD";
    $country[$u]["lib"]="Moldova, Republic of";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MG";
    $country[$u]["lib"]="Madagascar";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MH";
    $country[$u]["lib"]="Marshall Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ML";
    $country[$u]["lib"]="Mali";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MN";
    $country[$u]["lib"]="Mongolia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MM";
    $country[$u]["lib"]="Myanmar";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MO";
    $country[$u]["lib"]="Macau";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MP";
    $country[$u]["lib"]="Northern Mariana Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MQ";
    $country[$u]["lib"]="Martinique";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MR";
    $country[$u]["lib"]="Mauritania";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MS";
    $country[$u]["lib"]="Monserrat";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MT";
    $country[$u]["lib"]="Malta";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MU";
    $country[$u]["lib"]="Mauritius";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MV";
    $country[$u]["lib"]="Maldives";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MW";
    $country[$u]["lib"]="Malawi";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MX";
    $country[$u]["lib"]="Mexico";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MY";
    $country[$u]["lib"]="Malaysia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="MZ";
    $country[$u]["lib"]="Mozambique";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NA";
    $country[$u]["lib"]="Namibia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NC";
    $country[$u]["lib"]="New Caledonia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NE";
    $country[$u]["lib"]="Niger";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NF";
    $country[$u]["lib"]="Norfolk Island";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NG";
    $country[$u]["lib"]="Nigeria";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NI";
    $country[$u]["lib"]="Nicaragua";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NL";
    $country[$u]["lib"]="Netherlands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NO";
    $country[$u]["lib"]="Norway";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NP";
    $country[$u]["lib"]="Nepal";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NR";
    $country[$u]["lib"]="Nauru";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NU";
    $country[$u]["lib"]="Niue";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="NZ";
    $country[$u]["lib"]="New Zealand";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="OM";
    $country[$u]["lib"]="Oman";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PA";
    $country[$u]["lib"]="Panama";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PE";
    $country[$u]["lib"]="Peru";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PF";
    $country[$u]["lib"]="French Polynesia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PG";
    $country[$u]["lib"]="Papua New Guinea";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PH";
    $country[$u]["lib"]="Philippines";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PK";
    $country[$u]["lib"]="Pakistan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PL";
    $country[$u]["lib"]="Poland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PM";
    $country[$u]["lib"]="St. Pierre & Miquelon";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PN";
    $country[$u]["lib"]="Pitcairn";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PR";
    $country[$u]["lib"]="Puerto Rico";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PT";
    $country[$u]["lib"]="Portugal";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PW";
    $country[$u]["lib"]="Palau";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="PY";
    $country[$u]["lib"]="Paraguay";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="QA";
    $country[$u]["lib"]="Qatar";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="RE";
    $country[$u]["lib"]="R&eacute;union";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="RO";
    $country[$u]["lib"]="Romania";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="RU";
    $country[$u]["lib"]="Russian Federation";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="RW";
    $country[$u]["lib"]="Rwanda";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SA";
    $country[$u]["lib"]="Saudi Arabia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SB";
    $country[$u]["lib"]="Solomon Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SC";
    $country[$u]["lib"]="Seychelles";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SD";
    $country[$u]["lib"]="Sudan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SE";
    $country[$u]["lib"]="Sweden";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SG";
    $country[$u]["lib"]="Singapore";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SH";
    $country[$u]["lib"]="St. Helena";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SI";
    $country[$u]["lib"]="Slovenia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SJ";
    $country[$u]["lib"]="Svalbard & Jan Mayen Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SK";
    $country[$u]["lib"]="Slovakia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SL";
    $country[$u]["lib"]="Sierra Leone";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SM";
    $country[$u]["lib"]="San Marino";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SN";
    $country[$u]["lib"]="Senegal";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SO";
    $country[$u]["lib"]="Somalia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SR";
    $country[$u]["lib"]="Suriname";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ST";
    $country[$u]["lib"]="Sao Tome & Principe";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SV";
    $country[$u]["lib"]="El Salvador";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SY";
    $country[$u]["lib"]="Syrian Arab Republic";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="SZ";
    $country[$u]["lib"]="Swaziland";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TC";
    $country[$u]["lib"]="Turks & Caicos Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TD";
    $country[$u]["lib"]="Chad";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TF";
    $country[$u]["lib"]="French Southern Territories";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TG";
    $country[$u]["lib"]="Togo";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TH";
    $country[$u]["lib"]="Thailand";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TJ";
    $country[$u]["lib"]="Tajikistan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TK";
    $country[$u]["lib"]="Tokelau";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TM";
    $country[$u]["lib"]="Turkmenistan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TN";
    $country[$u]["lib"]="Tunisia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TO";
    $country[$u]["lib"]="Tonga";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TP";
    $country[$u]["lib"]="East Timor";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TR";
    $country[$u]["lib"]="Turkey";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TT";
    $country[$u]["lib"]="Trinidad & Tobago";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TV";
    $country[$u]["lib"]="Tuvalu";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TW";
    $country[$u]["lib"]="Taiwan, Province of China";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="TZ";
    $country[$u]["lib"]="Tanzania, United Republic of";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="UA";
    $country[$u]["lib"]="Ukraine";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="UG";
    $country[$u]["lib"]="Uganda";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="UM";
    $country[$u]["lib"]="United States Minor Outlying Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="US";
    $country[$u]["lib"]="United States of America";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="UY";
    $country[$u]["lib"]="Uruguay";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="UZ";
    $country[$u]["lib"]="Uzbekistan";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VA";
    $country[$u]["lib"]="Vatican City State";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VC";
    $country[$u]["lib"]="St. Vincent & the Grenadines";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VE";
    $country[$u]["lib"]="Venezuela";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VG";
    $country[$u]["lib"]="British Virgin Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VI";
    $country[$u]["lib"]="United States Virgin Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VN";
    $country[$u]["lib"]="Viet Nam";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="VU";
    $country[$u]["lib"]="Vanuatu";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="WF";
    $country[$u]["lib"]="Wallis & Futuna Islands";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="WS";
    $country[$u]["lib"]="Samoa";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="YE";
    $country[$u]["lib"]="Yemen";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="YT";
    $country[$u]["lib"]="Mayotte";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="YU";
    $country[$u]["lib"]="Yugoslavia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ZA";
    $country[$u]["lib"]="South Africa";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ZM";
    $country[$u]["lib"]="Zambia";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ZR";
    $country[$u]["lib"]="Zaire";
    $u=count($country);
    $country[$u]=array();
    $country[$u]["code"]="ZW";
    $country[$u]["lib"]="Zimbabwe";
    return $country;
}
///<summary>Represente getctrltel function</summary>
///<param name="name"></param>
function getctrltel($name){
    $reponse="";
    $type=get("type-$name", $_GET, $_POST);
    switch($type){
        case "0":
        $reponse=CleanText(get($name, $_GET, $_POST));
        break;
        case "1";
        $reponse=CleanText(get("pref1_$name", $_GET, $_POST))."/";
        $reponse .= CleanText(get("num11_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num21_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num31_$name", $_GET, $_POST));
        break;
        case "2";
        $reponse=CleanText(get("pref2_$name", $_GET, $_POST))."/";
        $reponse .= CleanText(get("num12_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num22_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num32_$name", $_GET, $_POST));
        break;
        case "3";
        $reponse="0033.".CleanText(get("pref3_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num13_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num23_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num33_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num43_$name", $_GET, $_POST));
        break;
        case "4";
        $reponse="0033.".CleanText(get("pref4_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num14_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num24_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num34_$name", $_GET, $_POST)).".";
        $reponse .= CleanText(get("num44_$name", $_GET, $_POST));
        break;
    }
    return $reponse;
}
///get communes from reftab
function getgender(){
    return array("M", "F");
}
///<summary>Represente getislpquery1 function</summary>
///<param name="unitid" default="%"></param>
function getislpquery1($unitid="%"){
    global $db;
    $reponse="SELECT betrokkene_n.naam BTN_naa, betrokkene_n.vnaam BTN_vna, betrokkene_n.g_datum BTN_gda, melding.cr_datum MEL_crd, get_aardfeit_ben(konkreet_feit.typfeit, konkreet_feit.aardfeit,'F')  FEI_fei_ben FROM betrokkene_n, melding, konkreet_feit WHERE (betrokkene_n.eljaar = melding.eljaar AND betrokkene_n.elnr = melding.elnr AND betrokkene_n.eltype = melding.eltype ) AND (";
    $query="SELECT libpersonne.nom, libpersonne.prenom ";
    $query .= " FROM $db.libpersonne ";
    $query .= " INNER JOIN $db.liberation ON (liberation.libpersonneid=libpersonne.id) ";
    $query .= " INNER JOIN $db.libunit ON (libunit.libid=liberation.id AND libunit.unitid LIKE '$unitid' ) ";
    $resultset=query($query);
    $sep="";
    while($row=fetch($resultset)){
        $nom=str_replace("'", "''", $row["nom"]);
        $prenom=str_replace("'", "''", $row["prenom"]);
        $reponse .= $sep."(betrokkene_n.naam='$nom' AND betrokkene_n.vnaam %matches '$prenom*')";
        $sep=" OR ";
    }
    $reponse .= ") AND melding.cr_datum >= '' AND melding.cr_datum <= '' AND ( betrokkene_n.eljaar = konkreet_feit.eljaar AND betrokkene_n.elnr = konkreet_feit.elnr AND betrokkene_n.eltype = konkreet_feit.eltype)";
    return $reponse;
}
///<summary>Represente getislpquery2 function</summary>
///<param name="unitid" default="%"></param>
function getislpquery2($unitid="%"){
    $query="query 2 pour $unitid";
    return $query;
}
///<summary>Represente getislpquery3 function</summary>
///<param name="unitid" default="%"></param>
function getislpquery3($unitid="%"){
    $query="query 3 pour $unitid";
    return $query;
}
///<summary>Represente getislpquery4 function</summary>
///<param name="unitid" default="%"></param>
function getislpquery4($unitid="%"){
    $query="query 4 pour $unitid";
    return $query;
}
///<summary>Represente getlangtext function</summary>
function getlangtext(){
    static $langC=null;
    if($langC == null){
        $langC=array("NL"=>"D", "FR"=>"F", "DE"=>"G");
    }
    return $langC;
}
///<summary>Represente getnoel function</summary>
///<param name="type"></param>
function getnoel($type){
    $valeur="";
    $typeval="1";
    if($type == "fin")
        $typeval="2";
    $query="SELECT valeur FROM USERS.configdate WHERE type=$typeval";
    $resultset=query($query);
    if($row=fetch($resultset))
        $valeur=$row["valeur"];
    return $valeur;
}
///<summary>Represente getpermissions function</summary>
function getpermissions(){
    return ipb_app()->session->cia_userunit["rights"];
}
///<summary>get Rbac : Auth </summary>
function getrbacidentification($userId=""){
    global $db, $debugid;
    $ip=$_SERVER["REMOTE_ADDR"];
    $id=
    $names=
    $unitid=
    $unitname=
    $unitcode=
    $unittype=
    $useradmin=
    $bngtype=
    $provinceid=
    $sousid=
    $lang=
    $mdj="";
    $response=false;
    $debug=false;
    $_cipb=ipb_environment()->controller;
    //+ log as

    if(empty($userId) && ($log=ipb_app()->session->logAs)){
        $userId=$log;
    }
    if($userId == ""){
        if(isset($_SERVER['HTTP_SSO'])){
            $userId=substr($_SERVER['HTTP_SSO'], 0, 9);
            $_SESSION["RBACAuthentication"]="1";
        }
    }
    $loader=ipb_environment()->controller->loader;
    $unit_model=$loader->model("Units");
    $user_model=$loader->model("Users");
    $userunit_model=$loader->model("UserUnit");
    $droit_model=$loader->model("Droits");
    //+ bypass

    $u_mdj=0;
    if($userId != ""){
        $userType="44";
        $response=true;
        $rbac=new IPB_Rbac();
        $authenticationdata=$rbac->LogIn(substr($userId, 0, 9), $userType);
        unset($authenticationdata->rightsPerUnits["7362"]);
        unset($authenticationdata->unitlist["7362"]);
        unset($authenticationdata->rolesPerUnitList["7362"]);
        if(!$authenticationdata){
            ipb_ilog("rbac auth failed");
            return false;
        }
        $_have_roles=(count($authenticationdata->rolesPerUnitList) > 0);
        $is_working_unit=$_have_roles && in_array($authenticationdata->unit, $unit_list=array_keys($authenticationdata->rolesPerUnitList));
        if(!$_have_roles){
            ipb_environment()->set("rbac_error", "no roles founds for : ".$userId);
            return false;
        }
        $unit=$authenticationdata->unit;
        if(!$is_working_unit){
            $unit=$unit_list[0];
        }
        $unitname=$authenticationdata->unitlist[$unit]->names[$authenticationdata->language];
        $query="SELECT users.id, users.nom, users.prenom, users.unitid, unit.nom as unite, unit.type_, users.type_ as admin, users.pwd, users.essai, users.last_ ";
        $query .= " , users.lang, users.bngtype, users.provinceid, users.sousid, unit.mdj, unit.code_ ";
        $query .= " FROM $db.users ";
        $query .= " INNER JOIN $db.unit ON (users.unitid=unit.id) ";
        $query .= " WHERE '$userId' IN (users.login, users.loginext)";
        if($debug)
            DeBugTrace("check user ".$query, $debugid);
        $resultset=query($query);
        if($row=fetch($resultset)){
            $id=$row["id"];
            $names=$row["nom"]." ".$row["prenom"];
            $unitid=$row["unitid"];
            empty($unitname) && ($unitname=$row["unite"]);
            $unitcode=$row["code_"];
            $unittype=$row["type_"];
            $useradmin=$row["admin"];
            $lang=$row["lang"];
            $bngtype=$row["bngtype"];
            $provinceid=$row["provinceid"];
            $sousid=$row["sousid"];
            $mdj=
            $u_mdj=$row["mdj"];
            if($debug)
                DeBugTrace("log correct ".$query, $debugid);
            $identified=true;
            $_cipb->loader->model("Users")->resetTryConnection($userId);
            $corresp=false;
            if($unitcode == $authenticationdata->unit)
                $corresp=true;
            $queryu="SELECT unit.code_ ";
            $queryu .= " FROM $db.userunit ";
            $queryu .= " INNER JOIN $db.unit ON (unit.id=userunit.unitid) ";
            $queryu .= " WHERE userunit.userid='$id'";
            $resultsetu=query($queryu);
            while($rowu=fetch($resultsetu)){
                if($rowu["code_"] == $unit)
                    $corresp=true;
            }
            $mdj="0";
            if(!$corresp){
                $unittype="2";
                $unitcode=$unit;
                if($authenticationdata->roles->houseOfJustice == "1" || $authenticationdata->roles->houseOfJusticeAdmin == "1")
                    $mdj="1";
                $querysu="SELECT id FROM $db.unit WHERE code_='".$unit."'";
                $resultsetsu=query($querysu);
                $found=false;
                if($rowsu=fetch($resultsetsu)){
                    $found=true;
                    $unitid=$rowsu["id"];
                }
                if(!$found){
                    $queryiu="INSERT INTO $db.unit (nom, type_, code_, langue, mdj, provinceid) VALUES ";
                    $queryiu .= " ('$unitname', '$unittype', '$unitcode', 'FR', '$mdj', '0')";
                    query($queryiu);
                    $unitid=LastId();
                }
                //+ ajout de l'unite a l'utilisateur
$userunit_model->insert(["userid"=>$id, "unitid"=>$unitid]);
            }
            //+ update : 05112019: gestion des autres unites

            if(count($authenticationdata->unitlist) > 0){
                foreach($authenticationdata->unitlist as $unitcode=>$list){
                    $querytt="SELECT id FROM $db.unit WHERE code_='$unitcode'";
                    $resultsettt=query($querytt);
                    $otherunitid="";
                    if($rowtt=fetch($resultsettt)){
                        $otherunitid=$rowtt["id"];
                    }
                    if($otherunitid == ""){
                        $unitname=$list->names["FR"];
                        $queryit="INSERT INTO $db.unit (nom, type_, code_, langue, mdj, provinceid) VALUES ";
                        $queryit .= " ('$unitname', '$unittype', '$unitcode', 'FR', '$mdj', '0')";
                        query($queryit);
                        $otherunitid=LastId();
                    }
                    $userunit_model->insertIfNotExists(["userid"=>$id, "unitid"=>$otherunitid]);
                }
            }
            //+ end update

        }
        else{
            $row2=$unit_model->select_row_fetch($unit_model->getTable(), ["code_"=>$unit], ["Columns"=>['id', 'nom', 'type_', 'provinceid']]);
            if($row2){
                $unitid=$row2["id"];
                $unitname=$row2["nom"];
                $unittype=$row2["type_"];
                $provinceid=$row2["provinceid"];
            }
            else{
                $unittype="2";
                $provinceid="1";
                $unit_model->insert(["nom"=>$unitname, "type_"=>$unittype, "code_"=>$unit]);
                $unitid=$unit_model->LastId();
            }
            $names=$authenticationdata->lastName." ".$authenticationdata->firstName;
            $useradmin=$authenticationdata->roles->policeAdmin ? "0":
            "1";
            $lang=$authenticationdata->language;
            $user_model->insert(ipb_query_args('nom, prenom, login, unitid, type_, lang, bngtype, provinceid, sousid', $authenticationdata->lastName, $authenticationdata->firstName, $userId, $unitid, $useradmin, $lang, '0', '0', '0'));
            $id=LastId();
            $bngtype="0";
            $sousid="0";
            $mdj="0";
            $userunit_model->insertIfNotExists(["userid"=>$id, "unitid"=>$unit]);
            $droits="0";
            if($authenticationdata->roles->policeUser)
                $droits="1";
            if($authenticationdata->roles->policeAdmin)
                $droits="2";
            if($authenticationdata->roles->policeValidator)
                $droits="4";
            $mdj=$authenticationdata->roles->houseOfJustice || $authenticationdata->roles->houseOfJustice;
            $droit_model->insert(ipb_query_args('userid, module, type_', [$id, '10', $droits]));
        }
        $_SESSION["userId"]=$userId;
        $_SESSION["login1"]=$id;
        $_SESSION["logincia"]=$id;
        $_SESSION["names"]=$names;
        $_SESSION["myunit"]=$unitid;
        $_SESSION["myunitname"]=$unitname;
        $_SESSION["myunittype"]=$unittype;
        $_SESSION["useradmin"]=$useradmin;
        $_SESSION["CIAbngtype"]=$bngtype;
        $_SESSION["CIAprovinceid"]=$provinceid;
        $_SESSION["CIAsousid"]=$sousid;
        $_SESSION["CIAlang"]=$lang;
        $_SESSION["CIAmdj"]=$u_mdj;
        $_SESSION["cia_app"]=ipb_create_appinfo($userId, $names, $unitid, $lang, $authenticationdata->unit, $unit);
        $user_model->drop($id);
        ipb_wln_e(__FILE__.":".__LINE__, $unit, $_SESSION, $authenticationdata);
        if(strpos($names, "Maison") > -1)
            $_SESSION["myunitnew"]="1";
        else
            $_SESSION["myunitnew"]="0";
        $_SESSION["polcomsoftuser"]="0";
        $_SESSION["RBACData"]=serialize($authenticationdata);
        $query="UPDATE $db.unit SET active='1' WHERE id='$unitid'";
        query($query);
        if($debug)
            DeBugTrace("Tout est ok - fin", $debugid);
    }
    return $response;
}
///<summary> retrieve the current roles</summary>
function getroles(){
    return ipb_app()->session->cia_userunit["roles"];
}
///<summary>Represente getrt function</summary>
///<param name="paramName"></param>
///<param name="defaultvalue" default=""></param>
function getrt($paramName, $defaultvalue=""){
    global $_GET, $_POST, $con;
    $temp="";
    if(array_key_exists($paramName, $_GET))
        $temp=$_GET[$paramName];
    else if(array_key_exists($paramName, $_POST))
        $temp=$_POST[$paramName];
    if($temp == "")
        $temp=$defaultvalue;
    $temp=CleanText($temp);
    if($con != "")
        mysqli_real_escape_string($con, $temp);
    return $temp;
}
///<summary>Represente gettime function</summary>
function gettime(){
    $date=getdate();
    $res=$date["year"];
    $temp=$date["mon"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    $res .= $temp;
    $temp=$date["mday"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    $res .= $temp;
    $temp=$date["hours"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    $res .= $temp;
    $temp=$date["minutes"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    $res .= $temp;
    $temp=$date["seconds"];
    if(strlen($temp) == 1){
        $temp="0$temp";
    }
    $res .= $temp;
    return $res;
}
///<summary>Represente givedate function</summary>
function givedate(){
    return year()."-".month()."-".day();
}
///<summary>Represente givetime function</summary>
function givetime(){
    return hour().":".minutes();
}
///<summary>Represente gpi_cron function</summary>
function gpi_cron(){
    global $db;
    $date=GiveDate();
    $query="INSERT INTO $db.testcron (val) VALUES ('TEST CRON $date')";
    query($query);
}
///<summary>Represente hashpwd function</summary>
///<param name="pwd"></param>
function hashpwd($pwd){
    global $saltSECU;
    return sha1($saltSECU.$pwd.$saltSECU);
}
///<summary>Represente head function</summary>
///<param name="str"></param>
///<param name="sep"></param>
function head($str, $sep){
    $res="";
    $str=trim($str);
    $pos=strpos($str, $sep);
    if(strlen($str) > 0){
        if($pos > -1)
            $res=substr($str, 0, $pos);
        else
            $res=$str;
    }
    return $res;
}
///<summary>Represente hour function</summary>
function hour(){
    $date=getdate();
    $temp=$date["hours"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    return $temp;
}
///<summary>Represente importuser function</summary>
///<param name="unitid"></param>
///<param name="mdp"></param>
///<param name="in"></param>
///<param name="table"></param>
///<param name="out"></param>
///<param name="lang" default="FR"></param>
function importuser($unitid, $mdp, $in, $table, $out, $lang="FR"){
    $pwd=HashPwd($mdp);
    $query="SELECT login, nom, prenom, profil ";
    if($mdp == "")
        $query .= ", pwd ";
    $query .= " FROM $in.$table";
    echo "<b>".$query."</b><br>";
    $resultset=query($query);
    while($row=fetch($resultset)){
        $type="1";
        $nom=CleanText($row["nom"]);
        $prenom=CleanText($row["prenom"]);
        $login=$row["login"];
        $profil=$row["profil"];
        if($mdp == ""){
            $pwd=HashPwd($row["pwd"]);
        }
        $query2="SELECT id, unitid FROM $out.users WHERE login='$login'";
        echo $query2."<br>";
        $resultset2=query($query2);
        $doublon=false;
        while($row2=fetch($resultset2)){
            $query3="INSERT INTO $in.doublon (login, unitid, date, heure) VALUE ('$login', '".$row2["unitid"]."', CURDATE(), CURTIME())";
            query($query3);
            $doublon=true;
            echo "<font color='red'>Doublon - $login (".$row2["unitid"].")</font><br>";
        }
        if(!$doublon){
            $queryi="INSERT INTO $out.users (nom, prenom, login, pwd, unitid, lang, type_) VALUES ";
            $queryi .= " ('$nom', '$prenom', '$login', '$pwd', '$unitid', '$lang', '$type') ";
            echo $queryi."<br>";
            query($queryi);
            $userid=LastId();
            $droit="1";
            switch($profil){
                case "consulent":
                $droit="1";
                break;
                case "beheerder";
                $droit="2";
                break;
                case "validator":
                $droit="4";
                break;
            }
            $queryii="INSERT INTO $out.droits (userid, module, type_) VALUES ('$userid', '10', '$droit')";
            echo $queryii."<br>";
            query($queryii);
        }
    }
}
///<summary>Represente ipblog function</summary>
///<param name="action"></param>
///<param name="val"></param>
///<param name="persid" default="0"></param>
///<param name="libid" default="0"></param>
///<param name="unitid" default="0"></param>
///<param name="msgid" default="0"></param>
function ipblog($action, $val, $persid="0", $libid="0", $unitid="0", $msgid="0"){
    global $db;
    if(!ipb_settings("no_loggin_central")){
        IPB_LogginCentral::Init()->Log(IPB_LogEntity::APPLICATION, $action, $val);
    }
    $user1=$_SESSION["login1"];
    $names=$_SESSION["names"];
    $unit=$_SESSION["myunitname"];
    $action=str_replace("'", "''", $action);
    $val=str_replace("'", "''", $val);
    $query="INSERT INTO $db.ipblogs (userid, usernom, datelog, heurelog, actionlog, rem, libid, persid, unitid, msgid) VALUES ";
    $query .= " ('$user1', '$names $unit', CURDATE(), CURTIME(), '$action', '$val', '$libid', '$persid', '$unitid', '$msgid')";
    query($query);
}
///<summary>Represente isin function</summary>
///<param name="chaine"></param>
///<param name="pattern"></param>
function isin($chaine, $pattern){
    $resp=false;
    if(strpos($chaine, $pattern) === false)
        $resp=false;
    else
        $resp=true;
    return $resp;
}
///<summary>Represente jourdelasemaine function</summary>
///<param name="date"></param>
function jourdelasemaine($date){
    $timestamp=mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
    $pos=date("w", $timestamp);
    switch($pos){
        case "1":
        return "Lundi";
        break;
        case "2":
        return "Mardi";
        break;
        case "3":
        return "Mercredi";
        break;
        case "4":
        return "Jeudi";
        break;
        case "5":
        return "Vendredi";
        break;
        case "6":
        return "Samedi";
        break;
        case "0":
        return "Dimanche";
        break;
    }
}
///<summary>Represente joursem function</summary>
///<param name="m"></param>
///<param name="j" default=""></param>
///<param name="a" default=""></param>
function joursem($m, $j="", $a=""){
    if($j == "" || $a == ""){
        $a=substr($m, 0, 4);
        $j=substr($m, 8, 2);
        $m=substr($m, 5, 2);
    }
    $timestamp=mktime(0, 0, 0, $m, $j, $a);
    return date("w", $timestamp);
}
///<summary>Represente lastid function</summary>
function lastid(){
    global $con;
    return mysqli_insert_id($con);
}
///<summary>Represente logaffectunit function</summary>
///<param name="unitid"></param>
///<param name="libid"></param>
function logaffectunit($unitid, $libid){
    global $db;
    $userid=$_SESSION["login1"];
    $username=$_SESSION["names"];
    $query="INSERT INTO $db.logunit (libid, unitid, date_, heure, userid, username) VALUES ('$libid', '$unitid', CURDATE(), CURTIME(), '$userid', '$username')";
    query($query);
}
///<summary>Represente minutes function</summary>
function minutes(){
    $date=getdate();
    $temp=$date["minutes"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    return $temp;
}
///<summary>Represente month function</summary>
function month(){
    $date=getdate();
    $temp=$date["mon"];
    if(strlen($temp) == 1)
        $temp="0$temp";
    return $temp;
}
///<summary>Represente nbjours function</summary>
///<param name="debut"></param>
///<param name="fin"></param>
function nbjours($debut, $fin){
    $nbSecondes=60 * 60 * 24;
    $debut_ts=strtotime($debut);
    $fin_ts=strtotime($fin);
    $diff=$fin_ts - $debut_ts;
    return round($diff / $nbSecondes);
}
///<summary>Represente newfilename function</summary>
///<param name="id"></param>
///<param name="ext"></param>
function newfilename($id, $ext){
    $res=$id."-";
    $chaine="abcdefghijklmnpqrstuvwxy0123456789";
    for($i=0; $i < 5; $i++)
        $res .= $chaine[rand() % strlen($chaine)];
    return $res.$ext;
}
///<summary>Represente nommois function</summary>
///<param name="mois"></param>
function nommois($mois){
    $res="";
    switch($mois){
        case 1:
        $res="Janvier";
        break;
        case 2:
        $res="F&eacute;vrier";
        break;
        case 3:
        $res="Mars";
        break;
        case 4:
        $res="Avril";
        break;
        case 5:
        $res="Mai";
        break;
        case 6:
        $res="Juin";
        break;
        case 7:
        $res="Juillet";
        break;
        case 8:
        $res="Aout";
        break;
        case 9:
        $res="Septembre";
        break;
        case 10:
        $res="Octobre";
        break;
        case 11:
        $res="Novembre";
        break;
        case 12:
        $res="D&eacute;cembre";
        break;
    }
    return $res;
}
///<summary>Represente obtenirdroits function</summary>
///<param name="table"></param>
///<param name="champ"></param>
///<param name="userid"></param>
///<param name="res" ref="true"></param>
function obtenirdroits($table, $champ, $userid, & $res){
    $query="SELECT ".$champ." FROM ".$table." WHERE userid='".$userid."'";
    $resultset=query($query);
    if($row=fetch($resultset)){
        $res[0]="1";
        $res[1]=$row[$champ];
    }
}
///<summary>Represente pclmodification function</summary>
///<param name="modifid"></param>
function pclmodification($modifid){
    global $db, $user1;
    $query="INSERT INTO $db.liberation (typeid, type2id, date_, debut, fin, fichier, libpersonneid, libpeineid, finalite, userid, ack, ackadp, arrestation, ";
    $query .= " modifications, condmaj, daterapport, remarque, mandat, dateecheance, reference, libsimple, decision, parquetctrl, conditions, juge, infraction, ";
    $query .= " peine, pv, jugeinstr, pvjuge, pvdossier, statut, auteur, prolongation) ";
    $query .= " SELECT typeid, '2', CURDATE(), debut, fin, fichier, libpersonneid, libpeineid, finalite, '$user1', ack, ackadp, arrestation, ";
    $query .= " modifications, condmaj, daterapport, remarque, mandat, dateecheance, reference, libsimple, decision, parquetctrl, conditions, juge, infraction, ";
    $query .= " peine, pv, jugeinstr, pvjuge, pvdossier, statut, auteur, prolongation FROM $db.liberation WHERE id='$modifid' ";
    query($query);
    $id=LastId();
    $query="INSERT INTO $db.libunit (libid, unitid, quartierid, agentid, remarque, agentid2, agentid2, ";
    $query .= " agentid3, date_, heure) ";
    $query .= " SELECT '$id', unitid, quartierid, agentid, remarque, agentid2, agentid2, agentid3, ";
    $query .= " date_, heure FROM $db.libunit WHERE libid='$modifid'";
    query($query);
    return $id;
}
///<summary>Represente pclrevocation function</summary>
///<param name="revocid"></param>
///<param name="enddate"></param>
///<param name="decision"></param>
function pclrevocation($revocid, $enddate, $decision){
    global $db, $trans, $user1;
    $query="UPDATE $db.liberation SET fin='$enddate' WHERE id='$revocid'";
    query($query);
    $query="SELECT id FROM $db.typeliberation WHERE lib LIKE 'R%vocation'";
    $resultset=query($query);
    $typeid="0";
    if($row=fetch($resultset)){
        $typeid=$row["id"];
    }
    $query="INSERT INTO $db.liberation (typeid, date_, debut, fin, libpersonneid, userid, parquetctrl, auteur, conditions, reference, infraction, peine, pv, jugeinstr, pvjuge, pvdossier, dateecheance, decision) ";
    $query .= " SELECT '$typeid', CURDATE(), debut, fin, libpersonneid, '$user1', parquetctrl, auteur, conditions, reference, infraction, peine, pv, jugeinstr, pvjuge, pvdossier, dateecheance, decision ";
    $query .= " FROM $db.liberation WHERE id='$revocid'";
    query($query);
    $id=LastId();
    $nomfichier=basename($decision['name']);
    $repertoire=$decision['tmp_name'];
    $extension=strrchr($nomfichier, '.');
    $nouveaunom=NewFileName("R".$id, $extension);
    if(CheckFile("decision")){
        if($trans->Stock($repertoire, $nouveaunom, $trans->CIA2_lib)){
            $query="UPDATE $db.liberation SET fichier='".$nouveaunom."' WHERE id='".$id."'";
            query($query);
        }
        else{
            $err="1";
        }
    }
    $query="INSERT INTO $db.libunit (libid, unitid, quartierid, agentid, remarque, agentid2, agentid2, ";
    $query .= " agentid3, date_, heure) ";
    $query .= " SELECT '$id', unitid, quartierid, agentid, remarque, agentid2, agentid2, agentid3, ";
    $query .= " date_, heure FROM $db.libunit WHERE libid='$revocid'";
    query($query);
    return $id;
}
///<summary>Represente petitsconges function</summary>
function petitsconges(){
    global $db;
    $query="SELECT liberation.id, liberation.debut, liberation.fin, libpersonne.nom, libpersonne.prenom, libpersonne.photo ";
    $query .= " FROM $db.liberation ";
    $query .= " INNER JOIN $db.libunit ON (libunit.libid=liberation.id) ";
    $query .= " INNER JOIN $db.libpersonne ON (liberation.libpersonneid=libpersonne.id) ";
    $query .= " WHERE libunit.unitid='5' ";
    $query .= " AND DATEDIFF(CURDATE(),liberation.date_)=1 ";
    $query .= " AND DATEDIFF(liberation.fin, liberation.debut) <= 3 ";
    $resultset=query($query);
    $conges=array();
    while($row=fetch($resultset)){
        $u=count($conges);
        $conges[$u]=array();
        $conges[$u]["id"]=$row["id"];
        $conges[$u]["nom"]=$row["nom"];
        $conges[$u]["prenom"]=$row["prenom"];
        $conges[$u]["photo"]=$row["photo"];
        $conges[$u]["dates"]="Entre le ".ConvertDate($row["debut"])." et le ".ConvertDate($row["fin"]);
    }
    for($i=0; $i < count($conges); $i++){
        $texte="Nouvelle personne &agrave; suivre (i+Belgium)<br>".$conges[$i]["dates"];
        $query="INSERT INTO ZOOM.cico (nom, prenom, redacteur, remarque, date, heure, userid, usernom) VALUES ";
        $query .= " ('".$conges[$i]["nom"]."', '".$conges[$i]["prenom"]."', 'polcom-soft', '$texte', CURDATE(), CURTIME(), 0, 'polcom-soft') ";
        query($query);
        $id=LastId();
        $fichier=$conges[$i]["photo"];
        if($fichier != ""){
            $tab=explode(".", $fichier);
            $extension=explode(".", $fichier)[1];
            exec("cp /var/www/FILES/OUT/CIA2/lib/$fichier /var/www/FILES/OUT/ZOOM/cito/$id.$extension");
            $query="UPDATE ZOOM.cico SET photo='$id.$extension' WHERE id='$id'";
            query($query);
        }
        $texte .= "<br>".$conges[$i]["nom"]." ".$conges[$i]["prenom"];
        $query="INSERT INTO ZOOM.info (date, debut, picid, texte1, texte2, phenomid, source, userid, maj) VALUES ";
        $query .= " (CURDATE(), CURTIME(), '13', 'Personne &agrave; suivre sous condition', '$texte', '1', 'i+Belgium', '0', CURDATE())";
        query($query);
        $infoid=LastId();
        if($fichier != ""){
            $query="INSERT INTO ZOOM.picgen (fichier, date) VALUES ('',CURDATE())";
            query($query);
            $id=LastId();
            exec("cp /var/www/FILES/OUT/CIA2/lib/$fichier /var/www/ZOOM/fichiers/general/$id.$extension");
            $query="UPDATE ZOOM.picgen SET fichier='$id.$extension' WHERE id='$id'";
            query($query);
            $query="UPDATE ZOOM.info SET pic1='$id' WHERE id='$infoid'";
            query($query);
        }
    }
}
///<summary>Represente portal function</summary>
function portal(){
    $response=false;
    if(isset($_SERVER['HTTP_sso']))
        $response=true;
    return $response;
}
///<summary>Represente pv_cron function</summary>
function pv_cron(){
    $delai=15;
    $query="SELECT id, statutid, typeid, num FROM PV.pv WHERE (statutid='1' OR (statutid='3' AND typeid='2')) AND rappel='0' AND DATEDIFF(CURDATE(), envoi) >= '$delai'";
    $resultset=query($query);
    while($row=fetch($resultset)){
        $id=$row["id"];
        $statut=$row["statut"];
        $typeid=$row["typeid"];
        $numpv=$row["num"];
        $query2="UPDATE PV.pv SET rappel='1' WHERE id='".$id."'";
        query($query);
        $parquetid="203";
        $provinceid="204";
        $sujet="Police de Comines - rappel";
        $message="Rappel : le pv ".$id." requiert votre d&eacute;cision (pn n&deg;$numpv)";
        $headers='From:Police Comines-Warneton<info@polcom.be>'."\n";
        $headers .= 'Content-Type:text/html;charset="iso-8859-1"'."\n";
        $headers .= 'Content-Transfer-Encoding:8bit';
        if($typeid == "1"){
            $query="SELECT mail FROM USERS.utilisateurs WHERE id='".$provinceid."'";
            $resultset=query($query);
            if($row=fetch($resultset))
                $mail=$row["mail"];
        }
        if($typeid == "2"){
            if($statut == "1"){
                $query="SELECT mail FROM USERS.utilisateurs WHERE id='".$parquetid."'";
                $resultset=query($query);
                if($row=fetch($resultset))
                    $mail=$row["mail"];
            }
            else{
                $query="SELECT mail FROM USERS.utilisateurs WHERE id='".$provinceid."'";
                $resultset=query($query);
                if($row=fetch($resultset))
                    $mail=$row["mail"];
            }
        }
        mail($mail, $sujet, $message, $headers);
    }
}
///<summary>Represente query function</summary>
///<param name="query"></param>
function query($query){
    global $con;
    if(ipb_environment()->is("DEV")){
        ipb_environment()->env_count("query");
        if(ipb_environment()->get("querydebug")){
            ipb_ilog($query);
            ipb_wln($query);
        }
    }
    if($con){
        $r=$con->query($query);
        if(!$r){
            if(ipb_environment()->is("DEV")){
                ipb_trace();
                ipb_wln_e($query, mysqli_error($con));
            }
            die("Query Error : ".mysqli_error($con));
        }
        return $r;
    }
    return null;
}
///<summary>Represente randomstring function</summary>
///<param name="size"></param>
function randomstring($size){
    $res="";
    $chaine="abcdefghijklmnpqrstuvwxy0123456789";
    for($i=0; $i < $size; $i++)
        $res .= $chaine[rand() % strlen($chaine)];
    return $res;
}
///<summary>Represente recurrent function</summary>
function recurrent(){
    $query="SELECT id, concerne FROM PROJ.recurrent WHERE date_echeance<=CURDATE() AND notif=0 ";
    $resultat=query($query);
    while($row=fetch($resultat)){
        $id=$row["id"];
        $concerne=$row["concerne"];
        $sujet="Dossiers recurrents - rappel";
        $message="Rappel : le dossier ".$concerne." est arriv&eacute; &agrave; &eacute;ch&eacute;ance!";
        $headers='From:Police Comines-Warneton<info@polcom.be>'."\n";
        $headers .= 'Content-Type:text/html;charset="iso-8859-1"'."\n";
        $headers .= 'Content-Transfer-Encoding:8bit';
        mail("police.comines@gmail.com", $sujet, $message, $headers);
        $query2=" UPDATE PROJ.recurrent SET notif=1 WHERE id=".$id;
        query($query2);
    }
    $query="UPDATE USERS.sortieaps SET etat='0'";
    query($query);
}
///<summary>Represente recurticks function</summary>
function recurticks(){
    $temp=microtime();
    $end=strpos($temp, " ")."<br>";
    $temp=time().substr($temp, 2, $end-2);
    return $temp;
}
///<summary>Represente resizepicture function</summary>
///<param name="img"></param>
///<param name="taillemax"></param>
function resizepicture($img, $taillemax){
    $result="";
    return $result;
}
///<summary>Represente responsivemenu function</summary>
function responsivemenu(){
    global $home;
    ?>
<script>
	function resize(){
		if(document.body.clientWidth<1000) {
			document.getElementById('fullmenu').style.display="none";
			document.getElementById('lightmenu').style.display="block";
		} else {
			document.getElementById('fullmenu').style.display="block";
			document.getElementById('lightmenu').style.display="none";
			document.getElementById('lightdiv').style.display="none";
		}
	}
	window.addEventListener('resize', function(event){
		resize();
	});
	resize();
</script><?php 
    if(!$home){
        ?>
<script>
	function showbug(){
		document.getElementById("bugreport").style.display = "inline";
	}
	function hidebug(){
		document.getElementById("bugreport").style.display = "none";
	}
</script>
<div id="bugreport" style="display:none;position:fixed;top:700px;left:600px;width:600px;height:300px;">
	<TABLE bgcolor="#eeeeff" border="1px solid black">
		<tr>
			<TH align="left">Personne de contact en cas de probl&egrave;me :</TH>
		</tr>
		<tr>
			<td align="left">
				Fr&eacute;d&eacute;ric DEJAEGERE - frederic.dejaegere@police.belgium.eu<br />
				Tel : 056/55.00.38 - Fax : 056/55.00.39
			</td>
		</tr>
	</table>
</div><?php 
    }
}
///<summary>Represente securext function</summary>
///<param name="ext"></param>
function securext($ext){
    $reponse=false;
    switch(strtolower($ext)){
        case ".pdf":
        $reponse=true;
        break;
        case ".doc":
        $reponse=true;
        break;
        case ".odt":
        $reponse=true;
        break;
        case ".docx":
        $reponse=true;
        break;
        case ".xls":
        $reponse=true;
        break;
        case ".xlsx":
        $reponse=true;
        break;
        case ".csv":
        $reponse=true;
        break;
        case ".ppt":
        $reponse=true;
        break;
        case ".pptx":
        $reponse=true;
        break;
        case ".gif":
        $reponse=true;
        break;
        case ".jpg":
        $reponse=true;
        break;
        case ".tif":
        $reponse=true;
        break;
        case ".jpe":
        $reponse=true;
        break;
        case ".jpeg":
        $reponse=true;
        break;
        case ".png":
        $reponse=true;
        break;
        case ".txt":
        $reponse=true;
        break;
        case ".rtf":
        $reponse=true;
        break;
        case ".zip":
        $reponse=true;
        break;
    }
    return $reponse;
}
///<summary>Represente services function</summary>
///<param name="code"></param>
function services($code){
    $rep="";
    switch($code){
        case "INT":
        $rep="Intervention";
        break;
        case "GRA":
        $rep="Grad&eacute;s";
        break;
        case "SER":
        $rep="SER";
        break;
        case "QUA":
        $rep="Quartier";
        break;
        case "DIR":
        $rep="Direction";
        break;
        case "APP":
        $rep="Appui";
        break;
        case "CONS":
        $rep="Consultation";
        break;
        case "ALL":
        $rep="Equipe mixte";
        break;
    }
    return $rep;
}
///<summary>Represente sess function</summary>
///<param name="varname"></param>
///<param name="sessname"></param>
///<param name="get"></param>
///<param name="post"></param>
///<param name="defaultvalue" default=""></param>
function sess($varname, $sessname, $get, $post, $defaultvalue=""){
    $var=get($varname, $get, $post);
    if($var == ""){
        if(isset($_SESSION[$sessname."-".$varname]) && $_SESSION[$sessname."-".$varname] != ""){
            $var=$_SESSION[$sessname."-".$varname];
        }
        else{
            $var=$defaultvalue;
            $_SESSION[$sessname."-".
            $varname]=$defaultvalue;
        }
    }
    else
        $_SESSION[$sessname."-".
    $varname]=$var;
    return $var;
}
///<summary>Represente session function</summary>
///<param name="name"></param>
///<param name="defaultvalue" default=""></param>
function session($name, $defaultvalue=""){
    $resultat=$defaultvalue;
    if(isset($_SESSION[$name]))
        $resultat=$_SESSION[$name];
    return $resultat;
}
///<summary>Represente showlogunit function</summary>
///<param name="libpersid"></param>
function showlogunit($libpersid){
    global $db;
    $trad=new TradLib();
    $query="SELECT COUNT(DISTINCT logunit.id) AS NB ";
    $query .= " FROM $db.logunit ";
    $query .= " INNER JOIN $db.liberation ON (liberation.id=logunit.libid AND liberation.statut='0') ";
    $query .= " WHERE liberation.libpersonneid='$libpersid' ";
    $resultset=query($query);
    $nb=0;
    if($row=fetch($resultset)){
        $nb=$row["NB"];
    }
    if($nb > 0){
        ?><br><img src="/assets/img/log.png" height="30" style="cursor:pointer" title="<?php 
        echo $trad->list_logunit ?>" onclick="window.open('ListAffectation.php?libpersid=<?php 
        echo $libpersid;
        ?>')"><?php 
    }
}
///<summary>Represente showmenu function</summary>
///<param name="menu"></param>
///<param name="selection" default=""></param>
function showmenu($menu, $selection=""){
    global $home;
    ?>
<table border="0px solid black" cellpadding="0" cellspacing="0" class="tablemenu" id="fullmenu" width="100%">
	<tr><?php 
    $sel="";
    for($i=0; $i < count($menu); $i++){
        if($menu[$i]["sel"])
            $sel="sel";
        else
            $sel="";
        ?>
		<td onclick="<?php 
        if($menu[$i]["new"]){
            ?>window.open('<?php echo $menu[$i]["link"] ?>');<?php 
        }
        else{
            ?>window.location.href='<?php echo $menu[$i]["link"] ?>'<?php 
        }
        ?>" class="<?php echo $sel ?>topmenu" title="<?php echo $menu[$i]["title"] ?>"<?php 
        if($menu[$i]["new"])
            echo "target='_blank'";
        ?>><?php echo $menu[$i]["lib"] ?></td><?php 
    }
    ?>
	</tr>
</table>
<table border="0px solid black" cellpadding="5" cellspacing="0" class="tablemenu" width="100%" id="lightmenu" style="display:none">
	<tr>
		<td><img src="<?php 
    if(!$home)
        echo "../";
    ?>images/light.png" style="cursor:pointer" onclick="OpenMenu()"></td>
	</tr>
</table>
<table border="0px solid black" cellpadding="5" cellspacing="0" class="tablemenu" id="lightdiv" style="display:none;position:absolute">
	<tr>
		<td><?php 
    for($i=0; $i < count($menu); $i++){
        ?>
	<input type="button" value="<?php echo $menu[$i]["lib"] ?>" onclick="<?php 
        if($menu[$i]["new"]){
            ?>window.open('<?php echo $menu[$i]["link"] ?>');<?php 
        }
        else{
            ?>window.location.href='<?php echo $menu[$i]["link"] ?>'<?php 
        }
        ?>" class="<?php echo $sel ?>topmenu" title="<?php echo $menu[$i]["title"] ?>"><br><?php 
    }
    ?>
		</td>
	</tr>
</table>
<script>
	function OpenMenu(){
		if(document.getElementById('lightdiv').style.display=='block') document.getElementById('lightdiv').style.display='none';
		else document.getElementById('lightdiv').style.display='block';
	}
</script><?php 
}
///<summary>Represente size function</summary>
///<param name="resultset"></param>
function size($resultset){
    return mysqli_num_rows($resultset);
}
///<summary>Represente strposinv function</summary>
///<param name="string"></param>
///<param name="pattern"></param>
function strposinv($string, $pattern){
    $temp=strrev($string);
    $pat=strrev($pattern);
    $offset=strlen($pattern) - 1;
    $pos=strpos($temp, $pat) + $offset;
    if(!$pos)
        $pos=0;
    else
        $pos=strlen($string) - $pos - 1;
    return $pos;
}
///<summary>Represente suivibng function</summary>
function suivibng(){
    global $db, $dbbng;
    $query="SELECT DISTINCT bng FROM $db.unit WHERE NOT ISNULL(bng)";
    $resultset=query($query);
    $dep="00";
    while($row=fetch($resultset)){
        $dep=$row["bng"];
        $query="UPDATE $dbbng.input$dep SET execid='0'";
        query($query);
    }
    $jourdelasemaine=joursem(GiveDate());
    $ok=true;
    if($jourdelasemaine == "0" || $jourdelasemaine == "1" || $jourdelasemaine == "6")
        $ok=false;
    $query="SELECT COUNT(id) AS nb FROM COM.conges WHERE DATEDIFF(CURDATE(),date) IN (0,1)";
    $resultset=query($query);
    if($row=fetch($resultset)){
        if($row["nb"] == "0" || $row["nb"] == "1")
            $ok=false;
    }
    if($ok){
        $query="UPDATE $dbbng.configuration ";
        $query .= " INNER JOIN $db.users ON (users.id=configuration.userid) ";
        $query .= " SET configuration.langue=users.lang, situation='0' ";
        $query .= " WHERE DATEDIFF(CURDATE(),configuration.maj)>1 AND users.bngtype<'2'";
        query($query);
    }
    $query="UPDATE $dbbng.situation SET prioriteid='2', nb='0'";
    query($query);
    $algo=array();
    $algo[0]=array("2", "23");
    $algo[1]=array("3", "26");
    $algo[2]=array("4", "27");
    $algo[3]=array("14", "28");
    $algo[4]=array("15", "28");
    $algo[5]=array("6", "29");
    $algo[6]=array("7", "29");
    $algo[7]=array("8", "29");
    $algo[8]=array("9", "29");
    $algo[9]=array("10", "29");
    $algo[10]=array("11", "29");
    $algo[11]=array("12", "29");
    $query="SELECT DISTINCT bng FROM $db.unit WHERE NOT ISNULL(bng)";
    $resultset=query($query);
    while($row=fetch($resultset)){
        $dep=$row["bng"];
        for($i=0; $i < count($algo); $i++){
            $query="UPDATE $dbbng.input$dep SET prioriteid='".$algo[$i][1]."' ";
            $query .= " WHERE status='0' AND prioriteid='".$algo[$i][0]."' AND DATEDIFF(CURDATE(), daterecep) > 6";
        }
    }
}
///<summary>Represente tail function</summary>
///<param name="str"></param>
///<param name="sep"></param>
function tail($str, $sep){
    $res="";
    $str=trim($str);
    $pos=strpos($str, $sep);
    if(strlen($str) > 0 && $pos > -1)
        $res=substr($str, $pos + 1);
    return $res;
}
///<summary>Represente ticks function</summary>
function ticks(){
    $temp=recurTicks();
    while($temp == recurTicks())
        $temp=recurTicks();
    return $temp;
}
///<summary>Represente tolongint function</summary>
///<param name="num"></param>
function tolongint($num){
    $size=10;
    while(strlen($num) < $size)
        $num="0".$num;
    return $num;
}
///<summary>Represente toppage function</summary>
///<param name="appname"></param>
function toppage($appname){
    global $home;
    ?>
	<table border="0px solid black" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="20px"><br><img src="../images/logopol.jpg"></td>
			<td>
				<h1>POLICE COMINES - <i><?php echo $appname ?></i></h1>
			</td>
			<td valign="top" align="right">
				<input type="button" value="<?php 
    if($home)
        echo "D&eacute;connexion";
    else
        echo "Retour";
    ?>" onclick="window.location.href='../index.php'" class="boutongristop">
			</td>
		</tr>
	</table><?php 
}
///<summary>Represente ventilationlibereflux function</summary>
function ventilationlibereflux(){
    global $db;
    $query="UPDATE $db.liberation SET statut='1' WHERE fin<>'' AND DATEDIFF(CURDATE(),FIN) > 21";
    query($query);
    $query="SELECT id, nom, COALESCE(diff,1000) FROM ( ";
    $query .= " 	SELECT unit.id, unit.nom, FLOOR((".time()."-MAX(users.last_))/86400) AS diff ";
    $query .= " 	FROM $db.unit ";
    $query .= " 	LEFT OUTER JOIN $db.users ON (users.unitid=unit.id AND COALESCE(users.last_,'')<>'') ";
    $query .= " 	WHERE unit.active=1 ";
    $query .= " 	GROUP BY unit.id, unit.nom ";
    $query .= " ) AS liste ";
    $query .= " WHERE COALESCE(diff,1000)>15 ";
    $query .= " ORDER BY COALESCE(diff,1000), nom ";
    $resultset=query($query);
    while($row=fetch($resultset)){
        $id=$row["id"];
        $queryu=" UPDATE $db.unit SET active=0 WHERE id='$id' ";
        query($queryu);
    }
}
///<summary>Represente year function</summary>
function year(){
    $date=getdate();
    $temp=$date["year"];
    return $temp;
}
///<summary>Represente class: Transfert</summary>
class Transfert{
    private $NAS="/mnt/partage/FILES/OUT/", $NASBNG="/mnt/partage/FILES/BNG/", $NASOBUS="/mnt/partage/FILES2/ZOOM/fichiers/obus/", $NASPVPIC="/mnt/pic/", $REP=APPLICATION_FILES."/OUT/", $REPBNG=APPLICATION_FILES."/OUT/CIA2/BNG/", $REPOBUS="/var/www/ZOOM/fichiers/obus/", $REPPIC="/var/www/FILES/PIC/", $REPPVPIC="/var/www/FILES/PV/";
    public $ASA="ASA/", $CHAT_fichiers="CHAT/fichiers/", $CHAT_photos="CHAT/photos/", $CIA2_lib="CIA2/lib/", $CIA2_panel="CIA2/PANEL/", $CIA2_tfl="CIA2/TFL/", $COLCON="COLCON/", $COM_doc="COM/docs/", $COM_sipp="COM/sipp/", $DOC_doc="DOC/docs/", $FICHES_bgm="FICHES/bgm/", $FICHES_bouches="FICHES/bouches/", $FICHES_sec="FICHES/sec/", $PANEL="PANEL/", $PA_act="PA/act/", $PCUI_actu="PCUI/actu/", $PCUI_communaute="PCUI/communaute/", $PCUI_doc="PCUI/doc/", $PCUI_ets="PCUI/ets/", $PCUI_forum="PCUI/forum/", $PCUI_pgui="PCUI/pgui/", $PCUI_rapports="PCUI/rapports/", $PCUI_reflexe="PCUI/reflexe/", $PROJ="PROJ/", $RAP="RAP/", $RGPMOB="RGPMOB/", $RGP_ANNEXESPUB="RGP/annexespub/", $RGP_AUTODEM="RGP/autodem/", $RGP_AVIS="RGP/avis/", $RGP_CTRL="RGP/ctrl/", $RGP_DC="RGP/dc/", $RGP_GDP="RGP/gdp/", $RGP_MULTI="RGP/multi/", $RGP_pv="RGP/pv/", $RH="RH/", $ZOOM_ASA="ZOOM/asa/", $ZOOM_arrpol="ZOOM/arrpol/", $ZOOM_circ="ZOOM/circ/", $ZOOM_cito="ZOOM/cito/", $ZOOM_commerces="ZOOM/commerces/", $ZOOM_dirops="ZOOM/dirops/", $ZOOM_doc="ZOOM/doc/", $ZOOM_map="ZOOM/map/", $ZOOM_objets="ZOOM/objets/", $ZOOM_obus="ZOOM/obus/", $ZOOM_plp="ZOOM/plp/", $ZOOM_pm="ZOOM/pm/", $ZOOM_pop="ZOOM/pop/", $ZOOM_proxi="ZOOM/proxi/", $ZOOM_pv="ZOOM/pv/", $ZOOM_rap="ZOOM/rap/", $ZOOM_rir="ZOOM/rir/", $ZOOM_velos="ZOOM/velos/";
    ///<summary>Represente DeleteBNG function</summary>
    ///<param name="nomfichier"></param>
    public function DeleteBNG($nomfichier){    }
    ///<summary>Represente DeStock function</summary>
    ///<param name="chemin"></param>
    ///<param name="nomfichier"></param>
    public function DeStock($chemin, $nomfichier){
        $image="";
        $base="/OUT/";
        if(file_exists($src=$this->REP.$chemin.$nomfichier)){
            $image=$base.$chemin.$nomfichier;
        }
        else{        }
        return $image;
    }
    ///<summary>Represente DeStockBng function</summary>
    ///<param name="nomfichier"></param>
    public function DeStockBng($nomfichier){
        $image="";
        $base="/FILES/OUT/CIA2/BNG/";
        if(file_exists($this->REPBNG.$nomfichier)){
            $image=$base.$nomfichier;
        }
        return $image;
    }
    ///<summary>Represente DeStockObus function</summary>
    ///<param name="nomfichier"></param>
    public function DeStockObus($nomfichier){
        $image="";
        $base="/ZOOM/fichiers/obus/";
        if(file_exists($this->REPOBUS.$nomfichier)){
            $image=$base.$nomfichier;
            exec("touch -m ".$this->REPOBUS.$nomfichier);
        }
        else{
            $sourcenas=$this->NASOBUS.$nomfichier;
            $ciblewww=$this->REPOBUS;
            exec("cp $sourcenas $ciblewww");
            if(file_exists($this->REPOBUS.$nomfichier)){
                $image=$base.$nomfichier;
            }
        }
        return $image;
    }
    ///<summary>Represente DeStockPic function</summary>
    ///<param name="fichier"></param>
    ///<param name="an"></param>
    ///<param name="mois"></param>
    ///<param name="jour"></param>
    public function DeStockPic($fichier, $an, $mois, $jour){
        $retour="/FILES/PV/";
        if(!file_exists($this->REPPVPIC.$fichier)){
            $sourcenas=$this->NASPVPIC.$an."/".$mois."/".$jour."/".$fichier;
            $ciblewww=$this->REPPVPIC;
            exec("cp $sourcenas $ciblewww");
            ResizePicture("/var/www/FILES/PV/".$fichier, 430);
            if(file_exists($this->REPPVPIC.$fichier)){
                $retour .= $fichier;
            }
        }
        else{
            $retour .= $fichier;
        }
        return $retour;
    }
    ///<summary>Represente Stock function</summary>
    ///<param name="fichierin"></param>
    ///<param name="nouveaunom"></param>
    ///<param name="chemin"></param>
    public function Stock($fichierin, $nouveaunom, $chemin){
        if(move_uploaded_file($fichierin, $this->REP.$chemin.$nouveaunom))
            return true;
        else
            return false;
    }
    ///<summary>Represente StockBng function</summary>
    ///<param name="fichierin"></param>
    ///<param name="nouveaunom"></param>
    public function StockBng($fichierin, $nouveaunom){
        if(move_uploaded_file($fichierin, $this->REPBNG.$nouveaunom))
            return true;
        else
            return false;
    }
    ///<summary>Represente StockObus function</summary>
    ///<param name="fichierin"></param>
    ///<param name="nouveaunom"></param>
    public function StockObus($fichierin, $nouveaunom){
        if(move_uploaded_file($fichierin, $this->REPOBUS.$nouveaunom))
            return true;
        else
            return false;
    }
    ///<summary>Represente StockPic function</summary>
    ///<param name="fichierin"></param>
    ///<param name="nouveaunom"></param>
    public function StockPic($fichierin, $nouveaunom){
        if(move_uploaded_file($fichierin, $this->REPPIC.$nouveaunom)){
            return true;
        }
        else
            return false;
    }
}
//+
//+
require_once(dirname(__FILE__)."/../../core.php");
require_once(IPB_APP_DIR."/library/utils/Model_Reftab.php");
defined("IPB_APP") || header("Content-Type: text/html; charset=utf-8");
$saltSECU="4d.j5&ccedil;9d43df^gsd4h4&eacute;72db";
$noeldebut="2017-12-21";
$noelfin="2018-01-06";
define('APPLICATION_PATH', $_SERVER['DOCUMENT_ROOT']);
defined('APPLICATION_FILES') || define('APPLICATION_FILES', $_SERVER['APPLICATION_FILES']);
defined('APPLICATION_NAME') || define('APPLICATION_NAME', 'iPlusBelgium');
if(!defined('APPLICATION_ENV')){
    $defv="ACC";
    switch($_SERVER['APPLICATION_ENV']){
        case "development";
        define("APPLICATION_ENV", 'DEV');
        break;
        case "testing":
        define("APPLICATION_ENV", 'TST');
        break;
        case "acceptance":
        define("APPLICATION_ENV", 'ACC');
        break;
        case "operation":
        case "production":
        define("APPLICATION_ENV", 'OPS');
        //+ fix

        $defv="OPS";
        break;
        case "training":
        define("APPLICATION_ENV", 'TRG');
        break;
    }
    !defined("APPLICATION_ENV_RBAC") && define('APPLICATION_ENV_RBAC', $defv);unset($defv);
}
$xnr=new IPB_XNR();
$rbacurl=$xnr->GetAppURL(IPB_REST_OP, IPB_CORE_UNIT, IPB_DEFAULT_MOC_CODE, IPB_RBAC_APPCODE);
$toserurl=$xnr->GetAppURL(IPB_REST_OP, IPB_CORE_UNIT, IPB_DEFAULT_MOC_CODE, IPB_TOSER_APPCODE);
$db=["db"=>$xnr->GetDBMSInfo(IPB_CORE_UNIT,
        IPB_DBMS_MOC_CODE,
        IPB_DBMS_APPCODE,
        ""),
        "bng"=>$xnr->GetDBMSInfo(IPB_CORE_UNIT,
        IPB_DBMS_BNGMOC_CODE,
        IPB_DBMS_APPCODE,
        "")
    ];
if($db["db"] && $db["bng"]){
    define('CONNECT_DB', $db["db"][0]->DBName);
    define('CONNECT_HOST', $db["db"][0]->IP);
    define('CONNECT_USER', $db["db"][0]->UID);
    define('CONNECT_PASSWORD', $db["db"][0]->PWD);
    define('CONNECT_DBBNG', $db["bng"][0]->DBName);
    define('IPB_RBAC_APPL', IPB_RBAC_APPCODE);
    define('IPB_TOSER_APPL', IPB_TOSER_APPCODE);
    define('IPB_DBMS_APPL', IPB_DBMS_APPCODE);
    !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES');
}
else{
    if(APPLICATION_ENV == "DEV"){
        define('CONNECT_DB', 'iplusbelgium');
        define('CONNECT_HOST', '10.27.101.115');
        define('CONNECT_USER', 'iplusb');
        define('CONNECT_PASSWORD', 'iplusb001');
        define('CONNECT_DBBNG', 'iplusbelgiumdjo');
        define('IPB_RBAC_APPL', '802S');
        define('IPB_TOSER_APPL', '800S');
        define('IPB_DBMS_APPL', '892S');
        !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES');
    }
    if(APPLICATION_ENV == "TST"){
        define('CONNECT_DB', 'iplusbelgium');
        define('CONNECT_HOST', '10.27.101.115');
        define('CONNECT_USER', 'iplusb');
        define('CONNECT_PASSWORD', 'iplusb001');
        define('CONNECT_DBBNG', 'iplusbelgiumdjo');
        define('IPB_RBAC_APPL', '802S');
        define('IPB_TOSER_APPL', '800S');
        define('IPB_DBMS_APPL', '892S');
        !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES/magic');
    }
    if(APPLICATION_ENV == "ACC"){
        define('CONNECT_DB', 'iplusbelgium');
        define('CONNECT_HOST', '10.27.201.58');
        define('CONNECT_USER', 'iplusb');
        define('CONNECT_PASSWORD', 'iplusb001');
        define('CONNECT_DBBNG', 'iplusbelgiumdjo');
        define('IPB_RBAC_APPL', '802S');
        define('IPB_TOSER_APPL', '800S');
        define('IPB_DBMS_APPL', '892S');
        !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES/magic');
    }
    if(APPLICATION_ENV == "OPS"){
        define('CONNECT_DB', 'iplusbelgium');
        define('CONNECT_HOST', '10.27.10.157');
        define('CONNECT_USER', 'iplusb');
        define('CONNECT_PASSWORD', 'iplusb001');
        define('CONNECT_DBBNG', 'iplusbelgiumdjo');
        define('IPB_RBAC_APPL', '802A');
        define('IPB_TOSER_APPL', '800A');
        define('IPB_DBMS_APPL', '892A');
        !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES/magic');
    }
    if(APPLICATION_ENV == "TRA"){
        define('CONNECT_DB', 'iplusbelgium');
        define('CONNECT_HOST', '10.27.101.115');
        define('CONNECT_USER', 'iplusb');
        define('CONNECT_PASSWORD', 'iplusb001');
        define('CONNECT_DBBNG', 'iplusbelgiumdjo');
        define('IPB_RBAC_APPL', '802S');
        define('IPB_TOSER_APPL', '800S');
        define('IPB_DBMS_APPL', '892S');
        !defined("MAGICDIR") && define('MAGICDIR', '/dstpol/swl/i_plus_belgium/www/FILES/magic');
    }
}
define('DEVICE_TYPE', 'HILDE');
defined("IPB_CIA_ENVIRONMENT") || define("IPB_CIA_ENVIRONMENT", 'DRI');
define("IPB_ENVIRONMENT", IPB_CIA_ENVIRONMENT);
$documentroot=APPLICATION_PATH;
if(APPLICATION_ENV == "DEV"){
    $documentroot=str_replace("/public", "", $documentroot);
}
else{
    $documentroot .= "/..";
}
if(!defined('IPB_APPLICATION_DIR')){
    define('IPB_APPLICATION_DIR', realpath(dirname(__FILE__)."/../../"));
}
include(IPB_APP_DIR."/library/utils/Authentication.php");
include(IPB_APP_DIR."/library/utils/Call_Controller.php");
define('IPB_URL_RBAC', $rbacurl.'/rbac/Api/v2/');
define('IPB_URL_TOSER', $toserurl.'/toser/Api/v1/');
define('IPB_URL_CROSS', 'http://dipophp56l03-acc.pol.be:8002/cross/Api/v1/');