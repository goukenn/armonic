<?php
// desc: balafon-module : php formatter armonic
// author: C.A.D BONDJE DOUE
// email: bondje.doue@igkdev.com
// version:1.0
// release: 22/03/2019
// copyright: igkdev @ 2019

use function igk_treat_lang_res as __;
///<summary>Represente igk_treat_append function</summary>
///<param name="options"></param>
///<param name="t"></param>
///<param name="indent" default="1"></param>
function igk_treat_append($options, $t, $indent=1){
    igk_debug_wln("append::: CTX:".$options->context." : LF:".$options->DataLFFlag." INDENT:".$indent." BDepth:".$options->bracketDepth. " cDEPTH:".$options->conditionDepth." t:".$t);
    $options->DataLFFlag=$options->DataLFFlag || igk_getv($options, "depthFlag");
    $indent=$indent || $options->DataLFFlag;
    $tab="";
    $g=0;
    $idx=0;
    if(empty($options->data) && ($options->mode == 0)){
        $g=& $options->output;
    }
    else{
        $g=& $options->data;
    }
    if(!empty($options->modifier) && !$options->modFlag){
        igk_treat_reset_modifier($options);
    }
    if(igk_getv($options, "multiLineFlag") == 1){
        $options->multiLineFlag=0;
    }
    if($options->DataLFFlag){
        $g .= $options->LF;
        $options->DataLFFlag=0;
        $indent=1;
    }
    if($indent){
        $idx=$options->bracketDepth + $options->offsetDepth;
        if($options->depthFlag){
            $idx++;
            $srt=$options->depthFlag;
            $options->depthFlag=0;
        }
        if($options->arrayBlockDepthFlag && ($options->arrayDepth > 0)){
            $idx += ($options->arrayDepth + 1);
        }
        if($idx > 0){
            $tab=str_repeat($options->IndentChar, $idx);
            $t=$tab.$t;
        }
    }
    $g .= $t;
}
///<summary>Represente igk_treat_bind_array function</summary>
///<param name="m"></param>
///<param name="offset"></param>
///<param name="start"></param>
///<param name="t" ref="true"></param>
///<param name="cancel" ref="true"></param>
function igk_treat_bind_array($m, $offset, $start, & $t, & $cancel){
    $g=igk_treat_init_array_reading($m, $start, $t, $cancel);
    if($cancel){
        $t=$g;
        return $t;
    }
    igk_treat_set_context($m->options, $m->matcher->name, 0, array("toread"));
    $m->options->depthFlag=0;
    $indent=0;
    $sp="";
    $f=substr($t, 0, $start).$sp;
    $t=substr($t, $offset);
    if(!empty($f))
        igk_treat_append($m->options, $f, $indent);
    $offset=0;
    $m->options->toread="array";
    return $t;
}
///<summary>Represente igk_treat_bind_data function</summary>
///<param name="command" default="null"></param>
function igk_treat_bind_data($command=null){
    if(!defined("ARMONIC_DATA_FILE"))
        return;
    $file=ARMONIC_DATA_FILE;
    $outfile=ARMONIC_DATA_OUTPUT_FILE;
    if(!file_exists($file)){
        igk_wln_e("file not exists");
        igk_exit();
    }
    if($command){
        $command->inputFile=$file;
        $command->outFile=isset($command->outFile) ? $command->outFile: $outfile;
        igk_treat_filecommand($command);
        return;
    }
    $g=exec("php -l ".realpath($file)." 2> NUL", $c, $o);
    if($o != 0){
        igk_wln_e($c);
    }
    $source=file_get_contents($file);
    $mp=igk_treat_source($source, function($out, $option){
        if(!empty($option->data)){
            igk_wln("cDepth: ".$option->conditionDepth);
            igk_wln("bDepth: ".$option->bracketDepth);
            igk_wln("oDepth: ".$option->openHook);
            igk_wln("context:".$option->context);
            igk_wln("context:".$option->context);
            if(is_object($option->toread))
                igk_wln($option->toread->type.":".$option->toread->name."<|||>".$option->tag);
            else if($option->toread){
                igk_wln($option->toread);
            }
            while($option->context && ($option->context != "global")){
                igk_treat_restore_context($option);
                if($option->context == "html"){
                    igk_wln_e("error html");
                }
                igk_wln("+context:".$option->context. " = ".$option->tag);
            }
            igk_wln_e("some: error: data is not empty:". $option->data);
        }
        else{
            $regx="/^\<\\?(php)?(\\s*|$)/";
            $s="";
            $lf=(empty($option->LF) ? $option->LF: IGK_LF);
            if(preg_match($regx, $out)){
                $def=igk_treat_outdef($option->definitions, $option);
                $s="<?php";
                $out=preg_replace($regx, "", $out);
                $s .= $lf.$def.$out;
            }
            else{
                $option->noFileDesc=1;
                $def=igk_treat_outdef($option->definitions, $option);
                igk_wln_e("OUTPUT:".$out);
                $s .= $out.$lf.$def;
            }
            igk_wln_e("OUTPUT:".$s);
            return $s;
        }
        return $out;
    });
    if(strlen($mp) < 250000){
        igk_wln("output:\n".$mp);
    }
    igk_io_w2file($outfile, $mp);
    $g=exec("php -l ".realpath($outfile)." 2> NUL", $c, $o);
    if($o != 0){
        igk_wln_e($c);
    }
}
///<summary>Represente igk_treat_check_command_handle function</summary>
///<param name="command"></param>
///<param name="throw" default="1"></param>
function igk_treat_check_command_handle($command, $throw=1){
    if(igk_gettsv($command, "commandHandle") == 1){
        if($throw){
            igk_ewln("\e[0;31m.misconfiguration command\e[0m");
            exit - 1;
        }
        return false;
    }
    return true;
}
///<summary>Represente igk_treat_command function</summary>
function igk_treat_command(){
    $c=igk_get_env("treat//command", array());
    $helps=igk_get_env("treat//command_help", array());
    if(!$c){
        $c["-o"]=function($v, $t){
            $t->outDir=igk_io_expand_path(trim($v));
        };
        $c["-v"]=function($v, $t){
            if(igk_count($t->command) == 1){
                igk_wln("version:1.0");
                igk_exit();
            }
        };
        $c["-h"]=function($v, $t){
            if(igk_count($t->command) == 1){
                igk_treat_show_usage();
                igk_exit();
            }
        };
    }
    return $c;
}
///<summary>Represente igk_treat_converttodockblocking function</summary>
///<param name="doc"></param>
///<param name="options"></param>
function igk_treat_converttodockblocking($doc, $options){
    $bs="";
    $d=igk_createxmlnode("dummy");
    $d->load($doc);
    foreach($d->getElementsByTagName("summary") as $n){
        $bs .= "* ".implode($options->LF."*", explode("\n", $n->getContent())).$options->LF;
    }
    foreach($d->getElementsByTagName("param") as $n){
        $bs .= "* @param ";
        $t=$n['type'];
        if($t)
            $bs .= $t." ";
        $bs .= $n["name"]." ";
        $bs .= implode($options->LF."*", explode("\n", $n->getContent())).$options->LF;
    }
    return $bs;
}
///<summary>Represente igk_treat_create_options function</summary>
///<param name="options" default="null"></param>
function igk_treat_create_options($options=null){
    $obj=(object)array(
            "context"=>"html",
            "mode"=>-1,
            "tag"=>null,
            "depthIndent"=>0,
            "output"=>"",
            "data"=>"",
            "mark"=>"@",
            "offset"=>0,
            "DataLF"=>1,
            "DataLFFlag"=>0,
            "depthFlag"=>0,
            "LF"=>IGK_LF,
            "arrayEntity"=>array(),
            "arrayDepth"=>0,
            "arrayMaxLength"=>60,
            "arrayBlockDepthFlag"=>0,
            "FormatText"=>1,
            "IndentChar"=>defined("ARMONIC_INDENT_CHAR") ? ARMONIC_INDENT_CHAR: "\t",
            "IgnoreEmptyLine"=>1,
            "RemoveComment"=>1,
            "toread"=>0,
            "bracketDepth"=>0,
            "bracketVarFlag"=>0,
            "operatorFlag"=>0,
            "mustPasLineFlag"=>0,
            "offsetDepth"=>0,
            "conditionDepth"=>0,
            "openHook"=>0,
            "lineNumber"=>0,
            "totalLines"=>0,
            "modifier"=>"",
            "modFlag"=>0,
            "modOffset"=>-1,
            "modifierArgs"=>null,
            "definitions"=>igk_createobj(),
            "documentation"=>null,
            "decorator"=>null,
            "noAutoParameter"=>0,
            "explodeVarDeclaration"=>0,
            "endMarkerFlag"=>0,
            "switchcaseFlag"=>0,
            "objectPointerFlag"=>0
        );
    if($options){
        foreach($obj as $k=>$v){
            if(isset($options->$k)){
                $obj->$k=$options->$k;
            }
        }
    }
    return $obj;
}
///<summary>Represente igk_treat_defaultheader function</summary>
///<param name="options"></param>
function igk_treat_defaultheader($options){
    static $defaultHeader=null;
    $mark=$options->mark;
    if($defaultHeader === null){
        $defaultHeader="";
        $hfile=igk_getv($options->command, "descriptionHeaderFile");
        if(!$hfile){
            $hfile=dirname(__FILE__)."/definition.php";
        }
        if(file_exists($hfile)){
            $defaultHeader=igk_str_format_bind("// ".$mark."{0|trim}".IGK_LF, explode(IGK_LF, igk_io_read_allfile($hfile))).IGK_LF;
        }
        else{
            $defaultHeader .= "// ".$mark."author: C.A.D. BONDJE DOUE".IGK_LF;
            $defaultHeader .= "// ".$mark."description: ".IGK_LF;
            $defaultHeader .= "// ".$mark."copyright: igkdev © ".date('Y').IGK_LF;
            $defaultHeader .= "// ".$mark."license: Microsoft MIT License. For more information read license.txt".IGK_LF;
            $defaultHeader .= "// ".$mark."company: IGKDEV".IGK_LF;
            $defaultHeader .= "// ".$mark."mail: bondje.doue@igkdev.com".IGK_LF;
            $defaultHeader .= "// ".$mark."url: https://www.igkdev.com".IGK_LF;
        }
    }
    return $defaultHeader;
}
///<summary>Represente igk_treat_end_array function</summary>
///<param name="m"></param>
///<param name="t" ref="true"></param>
///<param name="start" ref="true"></param>
///<param name="offset" ref="true"></param>
function igk_treat_end_array($m, & $t, & $start, & $offset){
    static $maxArray=null;
    if($maxArray == null)
        $maxArray=igk_gettsv($m->options, "command/maxArrayLength", $m->options->arrayMaxLength);
    else{
        $maxArray=$m->options->arrayMaxLength;
    }
    $m->options->arrayDepth--;
    $q=array_pop($m->options->arrayEntity);
    if($q){
        $v_o=igk_treat_get($m->options);
        $q_txt=$v_o.trim(substr($t, 0, $start));
        igk_treat_update_array_item($q, $t, $start, $m);
        $lvg=strlen($q_txt) - strlen($q->before);
        if($lvg > $maxArray){
            if(($v_cc=count($q->items)) > 1){
                $tq=array_merge($q->items);
                $inchar=$m->options->IndentChar;
                $indents=str_repeat($inchar, $q->depth);
                $indentd=$indents.$inchar;
                if(!$q->litteral){
                    $outtxt="";
                    if($v_cc > 1){
                        $outtxt .= ltrim($indentd.implode(",\n".$indentd, $tq)."\n".$indents);
                    }
                    else{
                        if(($pos=strpos($tq[0], "[")) !== false){
                            $v_o=rtrim($v_o);
                            $outtxt=substr($indents, strlen($inchar)-1).ltrim($q->beforeLine.$outtxt.substr($tq[0], $pos + 1));
                        }
                        else
                            $outtxt .= trim($tq[0]);
                        $m->options->DataLFFlag=0;
                    }
                }
                else{
                    $tq[0]=substr($tq[0], strpos($tq[0], "(") + 1);
                    $outtxt="array(";
                    if(($v_cc > 1) || (!empty(trim($tq[0])))){
                        $outtxt .= "\n".$indentd.implode(",\n".$indentd, $tq)."\n".$indents;
                    }
                }
                $new_o=substr($v_o, 0, $q->start).$outtxt;
                if($start > 0){
                    $t=ltrim(substr($t, $start));
                    $start=0;
                    $offset=1;
                }
                igk_treat_set($m->options, $new_o);
            }
        }
    }
    else{
        igk_wln("no items");
    }
}
///<summary>Represente igk_treat_execute_command function</summary>
///<param name="c"></param>
function igk_treat_execute_command($c){
    if(!($fc=$c->{"exec"})){
        igk_treat_show_usage();
        igk_exit();
    }
    if(igk_getv($c, "debug") == 1){
        igk_debug(1);
    }
    $c->reports=array();
    igk_start_time(__FUNCTION__);
    $fc($c);
    $ct=igk_execute_time(__FUNCTION__);
    igk_wln("time: ".$ct."s");
    if(!empty($c->reports)){
        igk_wln("Reports:");
        foreach($c->reports as $k=>$v){
            igk_wln($k."\n\t:".$v);
        }
    }
    $c_e=0;
    if(!empty($c->errors)){
        $c_e=igk_count($c->errors);
    }
    igk_ewln("Errors : ".$c_e);
}
///<summary>Represente igk_treat_filecommand function</summary>
///<param name="command"></param>
function igk_treat_filecommand($command){
    $file=$command->inputFile;
    if(!isset($command->noAutoCheck) || ($command->noAutoCheck == 0)){
        $g=exec("php -l ".realpath($file)." 2> NUL", $c, $o);
        if($o != 0){
            igk_ewln("\e[0;31mlint error: ");
            igk_ewln($c);
            igk_ewln("\e[0m");
            if(!isset($command->errors)){
                $command->errors=array();
            }
            $command->errors["{$file}"]=$c;
            return;
        }
    }
    $source=file_get_contents($file);
    $options=igk_treat_create_options();
    $options->command=$command;
    if(igk_getv($command, "leaveComment") == 1){
        $options->RemoveComment=0;
    }
    if(igk_getv($command, "noDefineHandle")){
        $options->noDefineHandle=1;
    }
    if(igk_getv($command, "genxmldoc")){
        $options->endDefinitionListener[]=function($def, $command){
            $dir=dirname($command->outFile);
            $v_sin="";
            if(isset($command->xmlOutDir)){
                $dir=$command->xmlOutDir;
                if(isset($command->inDir)){
                    $v_sin=substr(dirname($command->inputFile), strlen($command->inDir) + 1);
                    if(!empty($v_sin)){
                        $v_sin .= "/";
                    }
                }
            }
            $f=$dir."/".$v_sin.igk_io_basenamewithoutext($command->outFile).".xml";
            $xml_doc=igk_createxmlnode("doc");
            $xml_doc["xmlns"]="https://schema.igkdev.com/php/doc";
            $xml_doc["code"]="php";
            $xml_doc->add("script")->add("name")->Content=basename($command->outFile);
            $m=$xml_doc->add("members");
            $op=(object)array("mustclosetag"=>function(){
                        return 0;
                    });
            igk_treat_generator($def, ["function"=>function($tab, & $tdef, $m, $op){
                        $fc=$m->add("functions");
                        foreach($tab as $v){
                            $m=$fc->add("member");
                            $m["name"]=$v->name;
                            if($v->documentation){
                                foreach(explode(IGK_LF, $v->documentation) as $out){
                                    $t=igk_createtextnode($out);
                                    $m->add($t);
                                }
                            }
                            else{
                                $out=array();
                                if($v->name == "__construct"){
                                    $out[]="<summary>.ctr</summary>";
                                }
                                else
                                    $out[]="<summary>".__("represent")." ".$v->name." ".$v->type."</summary>";
                                if($v->readP){
                                    foreach($v->readP as $kv=>$vv){
                                        $gs="";
                                        $g=igk_createxmlnode("param");
                                        $g["name"]=$vv->name;
                                        if(isset($vv->default)){
                                            $g["default"]=$vv->default;
                                        }
                                        if(isset($vv->type)){
                                            $g["type"]=$vv->type;
                                        }
                                        if(isset($vv->ref) && $vv->ref){
                                            $g["ref"]="true";
                                        }
                                        $out[]=$g->render($op);
                                    }
                                }
                                if(($cond1=isset($v->ReturnType)) | ($cond2=(isset($v->options) && igk_getv($v->options, "ref")))){
                                    $g=igk_createxmlnode("return");
                                    if($cond1)
                                        $g["type"]=$v->ReturnType;
                                    if($cond2){
                                        $g["refout"]="true";
                                    }
                                    $out[]=$g->render($op);
                                }
                                foreach($out as $txt){
                                    $t=igk_createtextnode($txt);
                                    $m->add($t);
                                }
                            }
                        }
                    }
            , "class"=>function($tab, & $tdef, $m, $op=null, $gen_type=null){
                        $c=$m->add("classes");
                        $gen_type($tab, $tdef, $op, $c, $gen_type);
                    }
            , "interface"=>function($tab, & $tdef, $m, $op=null, $gen_type=null){
                        $c=$m->add("interfaces");
                        $gen_type($tab, $tdef, $op, $c, $gen_type);
                    }
            , "use"=>function($tab, & $tdef, $m, $op=null, $gen_type=null){
                        $c=$m->add("uses");
                        $gen_type($tab, $tdef, $op, $c, $gen_type);
                    }
            ], $m, $op, function($tab, & $tdef, $op, $fc, $gen_type){
                foreach($tab as $k=>$v){
                    $m=$fc->add("member");
                    $m["name"]=$v->name;
                    if($v->documentation)
                        $out=explode(IGK_LF, $v->documentation);
                    else{
                        $out=array("<summary>".__("represent")." ".$v->name." ".$v->type."</summary>");
                    }
                    foreach($out as $txt){
                        $t=igk_createtextnode($txt);
                        $m->add($t);
                    }
                    if(($v->type == "class") || ($v->type == "interface")){
                        if(isset($v->{"extends"})){
                            foreach($v->{"extends"} as $mt){
                                $t=igk_createtextnode("<extend>".$mt."</extend>");
                                $m->add($t);
                            }
                        }
                        if(isset($v->{"implements"})){
                            foreach($v->{"implements"} as $mt){
                                $t=igk_createtextnode("<implement>".$mt."</implement>");
                                $m->add($t);
                            }
                        }
                    }
                    if($v->definitions){
                        array_push($tdef, [$v->definitions, $m, $op, $gen_type]);
                    }
                }
            });
            igk_io_w2file($f, igk_xml_header()."\n".$xml_doc->render((object)array("Indent"=>1, "mustclosetag"=>$op->mustclosetag)));
        };
    }
    if($ctab=igk_getv($command, "defListener")){
        foreach($ctab as $k=>$v){
            $options->endDefinitionListener[]=$v;
        }
    }
    $mp=igk_treat_source($source, function($out, $option){
        if(!empty($option->data)){
            igk_wln("cDepth: ".$option->conditionDepth);
            igk_wln("bDepth: ".$option->bracketDepth);
            igk_wln("oDepth: ".$option->openHook);
            igk_wln("context:".$option->context);
            igk_wln("context:".$option->context);
            if(is_object($option->toread))
                igk_wln($option->toread->type.":".$option->toread->name."<!>".$option->tag);
            else if($option->toread){
                igk_wln($option->toread);
            }
            while($option->context && ($option->context != "global")){
                igk_treat_restore_context($option);
                if($option->context == "html"){
                    igk_wln_e("error html");
                }
                igk_wln("+context:".$option->context. " = ".$option->tag);
            }
            igk_wln_e("some: error: data is not empty:". $option->data);
        }
        else{
            if(isset($option->endDefinitionListener)){
                foreach($option->endDefinitionListener as $fallback){
                    $fallback($option->definitions, $option->command);
                }
            }
            if(igk_getv($option->command, "noTreat") != 1){
                $def="";
                if(igk_getv($option->command, "singleFilePerClass") == 1){
                    ($outdir=igk_getv($option->command, "singleFileOutput")) || ($outdir=igk_getv($option->command, "outDir")) || ($outdir=dirname($option->command->outFile));
                    if(!empty($outdir)){
                        $tdef=(object)array();
                        $globaloutput=array();
                        foreach($option->definitions as $k=>$v){
                            if($k == "lastTreat")
                                continue;
                            $NS_N="";
                            $defp=array((object)array("ns"=>"", "d"=>$v));
                            $gsrc="";
                            while($q=array_pop($defp)){
                                foreach($q->d as $def){
                                    switch(strtolower($def->type)){
                                        case "function":
                                        if(empty($q->ns)){
                                            $tdef->function[]=$def;
                                        }
                                        else{
                                            $globaloutput[$q->ns]["func"][]=$def;
                                        }
                                        continue 2;
                                        break;
                                        case "namespace":{
                                            if(isset($def->globalSrc) && !empty($gnssrc=$def->globalSrc)){
                                                $nsdec="";
                                                if(isset($def->def)){
                                                    $nsdec .= $def->def.";".IGK_LF;
                                                }
                                                else
                                                    $nsdec .= $def->src;
                                                $globaloutput[$def->name]["nsdec"]=$nsdec;
                                                $globaloutput[$def->name]["gsrc"][]=$gnssrc;
                                                unset($nsdec, $ngssrc);
                                            }
                                            foreach($def->definitions as $nt=>$mf){
                                                if($nt == "use"){
                                                    foreach($mf as $rr){
                                                        $gsrc .= $rr->src. IGK_LF;
                                                    }
                                                    continue;
                                                }
                                                array_push($defp, (object)array("ns"=>$def->name, "d"=>$mf, "p"=>$def, "src"=>& $gsrc));
                                            }
                                            continue 2;
                                        }
                                        break;
                                        case "use":
                                        if(empty($q->ns)){
                                            $tdef->use[]=$def;
                                        }
                                        continue 2;
                                        break;default: 
                                        break;
                                    }
                                    $src=$gsrc.$def->src;
                                    $nsdef="";
                                    if(!empty($ns=$q->ns)){
                                        $ns .= "/";
                                        if(isset($q->p->def)){
                                            $nsdef .= $q->p->def."{".IGK_LF.$src."}";
                                        }
                                        else
                                            $nsdef .= $q->p->src.$src;
                                    }
                                    else{
                                        $nsdef=$src;
                                    }
                                    $f=$outdir."/".$ns.$def->name.".".strtolower($def->type).".php";
                                    igk_io_w2file($f, "<?php\n".igk_treat_getfileheader($option, $f).$nsdef);
                                }
                            }
                        }
                        if(count($globaloutput) > 0){
                            $indent=str_repeat($option->IndentChar, 1);
                            foreach($globaloutput as $kk=>$tt){
                                $_tout="";
                                $_tout .= $tt["nsdec"].IGK_LF;
                                if(isset($tt["func"]) && ($funcs=$tt["func"])){
                                    usort($funcs, function($a, $b){
                                        return strcmp($a->name, $b->name);
                                    });
                                    foreach($funcs as $_gfc){
                                        $_tout .= $_gfc->src;
                                    }
                                }
                                foreach($tt["gsrc"] as $_t){
                                    $_tout .= $_t;
                                }
                                $_tout=preg_replace("#^".$indent."#im", "", $_tout);
                                $f=$outdir."/".$kk."/_global.ns.php";
                                igk_io_w2file($f, "<?php\n".igk_treat_getfileheader($option, $f).$_tout);
                            }
                        }
                        else{
                            $def=igk_treat_outdef($tdef, $option);
                        }
                    }
                }
                else
                    $def=igk_treat_outdef($option->definitions, $option);
                $regx="/^\<\\?(php)?(\\s*|$)/";
                $s="";
                $lf=(empty($option->LF) ? $option->LF: IGK_LF);
                if(preg_match($regx, $out)){
                    $s="<?php";
                    $out=preg_replace($regx, "", $out);
                    $s .= $lf.$def.$out;
                }
                else{
                    $option->noFileDesc=1;
                    if(($p=igk_getv($option, 'startCGIOffSet')) > 0){
                        $s .= substr($out, 0, $p).$lf.$def.substr($out, $p);
                    }
                    else
                        $s .= $out.$lf.$def;
                }
                return $s;
            }
            else{
                return implode($option->LF, $option->source);
            }
        }
        return $out;
    }
    , null, $options);
    if(igk_getv($command, "verbose", 0) == 1){
        if(strlen($mp) < 250000){
            igk_wln("output:\n".$mp);
        }
    }
    if(igk_getv($command, "singleFilePerClass") == 1){}$c=$command->outFile;
    if(isset($c)){
        igk_io_w2file($c, $mp);
        if(igk_getv($command, "noCheck") != 1){
            if(file_exists($c) && is_file($c)){
                exec("php -l ".realpath($c)." 2> NUL", $bc, $o);
                if($o != 0){
                    igk_wln($bc);
                    igk_wln_e("checking failed: ".$c);
                }
            }
            else{
                igk_wln("not file: ".$c);
            }
        }
    }
}
///<summary>Represente igk_treat_generator function</summary>
///<param name="def"></param>
///<param name="callbacks"></param>
function igk_treat_generator($def, $callbacks){
    $tab_args=array_slice(func_get_args(), 2);
    $tdef=array(array_merge([$def], $tab_args));
    while($hdef=array_pop($tdef)){
        $def=$hdef[0];
        $tab_args=array_slice($hdef, 1);
        $tab=igk_getv($def, "filedesc");
        if($tab && isset($callbacks["filedesc"])){
            call_user_func_array($callbacks["filedesc"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "FileInstruct");
        if($tab && isset($callbacks["FileInstruct"])){
            call_user_func_array($callbacks["FileInstruct"], array_merge(array($tab, & $tdef), $tab_args));
        }
        ///TASK: treat use
        $tab=igk_getv($def, "use");
        if($tab && isset($callbacks["use"])){
            usort($tab, function($a, $b){
                return $a->name <=> $b->name;
            });
            call_user_func_array($callbacks["use"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "global");
        if($tab && isset($callbacks["global"])){
            call_user_func_array($callbacks["global"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $q=igk_getv($def, "vars");
        if($q && isset($callbacks["vars"])){
            $tab=$q["tab"];
            usort($tab, function($a, $b){
                $r=strcmp($a->modifier, $b->modifier);
                if($r == 0){
                    $r=strcmp($a->name, $b->name);
                }
                return $r;
            });
            call_user_func_array($callbacks["vars"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "function");
        if($tab && isset($callbacks["function"])){
            usort($tab, function($a, $b){
                return $a->name <=> $b->name;
            });
            call_user_func_array($callbacks["function"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "interface");
        if($tab && isset($callbacks["interface"])){
            usort($tab, function($a, $b){
                $da=$a->{'@extends'} ?? "";
                $db=$b->{'@extends'} ?? "";
                if(($r=($da <=> $db)) == 0)
                    return $a->name <=> $b->name;
                return $r;
            });
            call_user_func_array($callbacks["interface"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "trait");
        if($tab && isset($callbacks["trait"])){
            usort($tab, function($a, $b){
                return $a->name <=> $b->name;
            });
            call_user_func_array($callbacks["trait"], array_merge(array($tab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "class");
        if($tab && isset($callbacks["class"])){
            $klist=array();
            $v_sroot="/";
            foreach($tab as $k=>$v){
                $n=$v_sroot;
                if(isset($v->{'@extends'})){
                    $p=$v->{'@extends'};
                    $key=$v->name;
                    $n=$v->{'@extends'};
                    while($p && isset($tab[$p])){
                        $key=$p."/".$key;
                        $p=igk_getv($tab[$p], '@extends');
                    }
                    $klist[$key]=$v;
                }
                else{
                    $klist[$v->name]=$v;
                }
            }
            $cl=array_keys($klist);
            sort($cl);
            $ktab=array();
            foreach($cl as $k){
                $ktab[]=$klist[$k];
            }
            call_user_func_array($callbacks["class"], array_merge(array($ktab, & $tdef), $tab_args));
        }
        $tab=igk_getv($def, "namespace");
        if(isset($callbacks["namespace"]) && (igk_count($tab) > 0)){
            usort($tab, function($a, $b){
                return $a->name <=> $b->name;
            });
            call_user_func_array($callbacks["namespace"], array_merge(array($tab, & $tdef), $tab_args));
        }
    }
}
///PRECEDENT + DataLF  [+ INDENT ] + $t
function igk_treat_get($options){
    if(($options->mode != 0) || !empty($options->data)){
        return $options->data;
    }
    return $options->output;
}
///<summary>Represente igk_treat_get_ignore_regex function</summary>
///<param name="command"></param>
function igk_treat_get_ignore_regex($command){
    $h=null;
    if(isset($command->ignorePattern)){
        $h=$command->ignorePattern;
    }
    else{
        if(igk_getv($command, "noGit") == 1){
            $h[]=".git";
        }
        if(igk_getv($command, "noVSCode") == 1){
            $h[]=".vscode";
        }
    }
    if($h){
        if(is_array($h)){
            $h=implode("|", $h);
        }
        $h=str_replace("/", "\\/", $h);
        $h=str_replace(".", "\.", $h);
        $h="/(".$h.")$/";
    }
    return $h;
}
///<summary>Represente igk_treat_getfileheader function</summary>
///<param name="options"></param>
///<param name="file"></param>
function igk_treat_getfileheader($options, $file){
    $defaultHeader=igk_treat_defaultheader($options);
    $s="// ".$options->mark."file: ".basename($file).IGK_LF;
    $s .= $defaultHeader.IGK_LF;
    return $s;
}
///<summary>Represente igk_treat_handle_char function</summary>
///<param name="t" ref="true"></param>
///<param name="start"></param>
///<param name="offset" ref="true"></param>
///<param name="d"></param>
///<param name="m"></param>
function igk_treat_handle_char(& $t, $start, & $offset, $d, $m){
    if(is_object($m->options->toread) && ($fc_hchar=$m->options->toread->handleChar)){
        return $fc_hchar($t, $start, $offset, $d, $m);
    }
    return false;
}
///<summary>Represente igk_treat_handle_funcparam function</summary>
///<param name="ch"></param>
///<param name="t"></param>
///<param name="start"></param>
///<param name="m"></param>
///<param name="cancel" ref="true"></param>
function igk_treat_handle_funcparam($ch, $t, $start, $m, & $cancel){
    $totreat=$m->options->toread;
    $cancel=1;
    $def="";
    $read_paramname=function($ch, $t, $start, $m){
        $totreat=$m->options->toread;
        $tc=substr($t, 0, $start);
        if(empty($tc)){
            return;}
        if(!isset($totreat->paramdef)){
            $totreat->paramdef="(";
        }
        $totreat->paramdef .= $tc;
        $pf=$totreat->paramdef;
        if(empty($pf)){
            return;}$rgx="/((?P<type>(".IGK_TREAT_NS_NAME."))\\s+)?(?P<refout>\\s*\&\\s*)?\\$(?P<name>(".IGK_TREAT_IDENTIFIER."))\\s*$/";
        $gtab=null;
        if(!preg_match($rgx, $pf, $gtab)){
            igk_wln_e("parameter not valid:  ".$pf. " DefToRead:".$totreat->def."| Line: ".$m->options->lineNumber);
        }
        $totreat->readP[]=(object)array(
            "name"=>$gtab["name"],
            "type"=>igk_getv($gtab,
            "type",
            null),
            "ref"=>trim(igk_getv($gtab,
            "refout",
            "")) == "&",
            "default"=>null
        );
    };
    if($totreat->readPMode == 1){
        if(!($ch == "]"))
            $totreat->paramdef .= substr($t, 0, $start);
    }
    switch($ch){
        case ",":
        if($m->options->conditionDepth<=1){
            if($totreat->readPMode === 1){
                $totreat->readP[count($totreat->readP)-1]->default=trim($totreat->paramdef);
            }
            else{
                $read_paramname($ch, $t, $start, $m);
            }
            $totreat->readPMode=0;
            $totreat->paramdef="";
            return;
        }
        break;
        case "=";
        if($m->options->conditionDepth<=1){
            $read_paramname($ch, $t, $start, $m);
            $totreat->readPMode=1;
            $totreat->paramdef="";
            return;
        }
        break;
        case ")":
        if($m->options->conditionDepth < 1){
            $def=substr($t, 0, $start);
            if($totreat->readPMode === 1){
                $pf=$totreat->paramdef;
                $totreat->readP[count($totreat->readP)-1]->default=trim($pf);
            }
            else{
                $read_paramname($ch, $t, $start, $m);
            }
            $cancel=0;
        }
        break;
        case "]":
        return;
        case "&":
        if($totreat->readPMode == 0){
            $v_t=trim(substr($t, 0, $start));
            if(!empty($v_t))
                $v_t .= " ";
            if(isset($totreat->paramdef))
                $totreat->paramdef .= $v_t."& ";
            else
                $totreat->paramdef=$v_t."& ";
            $cancel=0;
        }
        break;
    }
    if(($totreat->readPMode == 1)){
        if($ch == ','){
            $totreat->paramdef .= ", ";
        }
        else
            $totreat->paramdef .= substr($t, $start, 1);
    }
}
///<summary>Represente igk_treat_handle_html function</summary>
///<param name="t" ref="true"></param>
///<param name="start"></param>
///<param name="offset" ref="true"></param>
///<param name="m"></param>
///<param name="found" default="null" ref="true"></param>
function igk_treat_handle_html(& $t, $start, & $offset, $m, & $found=null){
    igk_treat_set_context($m->options, "html", 0);
    $is_start=empty($m->options->output);
    $tc=$m->data[0][0];
    $offset=0;
    $end_rgx="/\<\?(php)/";
    $o="";
    $before=substr($t, $start);
    $offset=$start + strlen($tc);
    $t=substr($t, $offset);
    $offset=0;
    $ln=& $m->options->lineNumber;
    $tn=$m->options->totalLines;
    $c=0;
    $s="";
    while($ln < $tn){
        if(preg_match($end_rgx, $t, $tab, PREG_OFFSET_CAPTURE, $offset)){
            $c=1;
            $s .= substr($t, 0, $tab[0][1]);
            $t=substr($t, $tab[0][1] + strlen($tab[0][0]));
            break;
        }
        $s .= trim($t);
        $t=rtrim($m->options->source[$ln]);
        $s .= $m->options->LF;
        $ln++;
        $offset=0;
    }
    if($c){
        $o=rtrim($s);
        if(!empty($o)){
            $o .= $m->options->LF;
        }
        $o .= "<?php";
        $offset=0;
    }
    else{
        $s .= rtrim($t);
        $o=$s;
        $t="";
        $offset=0;
    }
    igk_treat_restore_context($m->options);
    if(!empty($o=trim($o))){
        if($is_start){
            igk_treat_append($m->options, trim($m->data[0][0]).trim($o), 0);
        }
        else
            igk_treat_append($m->options, trim($m->data[0][0]).trim($o), 0);
        $m->options->DataLFFlag=1;
    }
    $found=$c;
    return $t;
}
///<summary>Represente igk_treat_handle_modargs function</summary>
///<param name="t" ref="true"></param>
///<param name="start"></param>
///<param name="offset" ref="true"></param>
///<param name="g"></param>
///<param name="m"></param>
function igk_treat_handle_modargs(& $t, $start, & $offset, $g, $m){
    if(($m->options->toread != "array") && $m->options->modFlag && ($m->options->conditionDepth<=0) && !empty($m->options->modifier)){
        switch($g){
            case ",":
            case "=":
            $modifier_def=igk_treat_get($m->options);
            if($m->options->modFlag == 2){
                igk_treat_modifier_getvalue($modifier_def, $t, $start, $m);
                return;
            }
            igk_treat_modifier_getname($modifier_def, $t, $start, $m);
            if($g == "="){
                $m->options->modFlag=2;
            }
            break;
        }
    }
}
///<summary>Represente igk_treat_handle_modifier function</summary>
///<param name="options"></param>
function igk_treat_handle_modifier($options){
    $mod_args=$options->modifierArgs;
    if(count($mod_args) > 0){
        if(!is_object($options->toread)){
            igk_wln_e("not and object type:".$options->toread." line:".$options->lineNumber);
            return 0;
        }
        $options->toread->definitions["vars"]=array(
                "depth"=>$options->bracketDepth + $options->offsetDepth,
                "tab"=>$mod_args
            );
        return 1;
    }
    return 0;
}
///<summary>Represente igk_treat_handle_operator_flag function</summary>
///<param name="m"></param>
///<param name="type"></param>
///<param name="t"></param>
///<param name="start"></param>
///<param name="offset" default="null" ref="true"></param>
function igk_treat_handle_operator_flag($m, $type, $t, $start, & $offset=null){
    if($opFlag=$m->options->operatorFlag){
        if(!(($type == "function") && preg_match("/(=|=\>|,|\?\?|:)/", $opFlag)) || (preg_match("/(::|-\>)/", $opFlag))){
            igk_wln("\e[0;41mwarning\e[0m ignore reserved operator use as instance member:\n type=".$type." text:".$t." Line:".$m->options->lineNumber." opFlag:[{$opFlag}] ? ".preg_match("/(::|-\>)/", $opFlag));
            $offset=$start + strlen($m->data[0][0]);
            return $t;
        }
    }
}
///<summary>Represente igk_treat_handle_use function</summary>
///<param name="m"></param>
///<param name="type"></param>
function igk_treat_handle_use($m, $type){
    $totreat=$m->options->toread;
    if(is_object($totreat)){
        switch($totreat->type){
            case "use":
            if(($type == "function") || ($type == "const"))
                igk_treat_append($m->options, $type." ", 0);
            return 1;
            break;
        }
    }
    return false;
}
///<summary>Represente igk_treat_init_array_reading function</summary>
///<param name="m"></param>
///<param name="start"></param>
///<param name="t" ref="true"></param>
///<param name="cancel" ref="true"></param>
function igk_treat_init_array_reading($m, $start, & $t, & $cancel=0){
    $cancel=0;
    if($m->options->context == "parameterReading"){
        if($m->options->toread->readPMode == 0){
            $cancel=1;
            $m->options->toread->paramdef="array ";
        }
    }
    if(is_object($m->options->toread) && ($m->options->toread->type == "function") && ($m->options->toread->readingMode == "3") && ($m->options->context == "definitionDeclaration")){
        $cancel=1;
    }
    if($cancel){
        igk_treat_append($m->options, "array ", 0);
        $t=substr($t, $start + strlen($m->data["operator"][0]));
        $offset=0;
        return $t;
    }
    igk_treat_start_array($m, $t, $start, 0);
}
///<summary>Represente igk_treat_lang_res function</summary>
///<param name="n"></param>
function igk_treat_lang_res($n){
    static $res=null;
    if($res == null){
        $res["represent"]="Represente";
    }
    return igk_getv($res, $n, $n);
}
///<summary>Represente igk_treat_lib function</summary>
///<param name="autocheck" default="1"></param>
///<param name="verbose" default="1"></param>
function igk_treat_lib($autocheck=1, $verbose=1){
    ini_set("max_execution_time", 0);
    $sdir="d://wamp/www/igkdev/Lib/igk/";
    $dir="d://wamp/www/igkdev/Lib/igk/";
    $outdir="d://temp/Lib/igk";
    $outdir="D://wamp/www/demoigk/Lib/igk";
    $count=0;
    $treat=0;
    $dln=strlen($sdir);
    $ignore_dir="/(".implode("|", ["\.git", "\.vscode"]).")$/";
    foreach(igk_io_getfiles($dir, "/\.(.)+$/i", true, $ignore_dir) as $k=>$v){
        if(preg_match("/\.(gkds)$/i", $v)){
            continue;
        }
        $n=substr($v, $dln);
        if(preg_match("/\.(php|phtml|pinc|pcss|inc)$/i", $v)){
            if($autocheck){
                exec("php -l ".$v, $out, $c);
                if($c != 0){
                    igk_wln("failed to check : ".igk_io_dir($v));
                    continue;
                }
            }
            if($verbose)
                igk_wln(igk_io_dir($v));
            $src=igk_io_read_allfile($v);
            $options=igk_str_read_createoptions();
            $options->treatClassSource=1;
            if(strpos($src, "<?php") === 0){
                $src=igk_str_read_treat_source($src, $options);
            }
            $outfile=$outdir."/".$n;
            igk_io_w2file($outfile, $src);
            $treat++;
        }
        else{
            $f=$outdir.'/'.$n;
            if(IGKIO::CreateDir(dirname($f))){
                if(file_exists($f)){
                    unlink($f);
                }
                copy($v, $f);
            }
            else{}
        }
        $count++;
    }
    igk_wln("total: ".$count);
    igk_wln("treat: ".$treat);
}
///<summary>Represente igk_treat_modifier function</summary>
///<param name="m"></param>
function igk_treat_modifier($m){
    static $ModiL=null;
    if(empty(trim($m))){
        return "";
    }
    if($ModiL == null){
        $ModiL=array(
                "abstract"=>0,
                "final"=>1,
                "protected"=>10,
                "private"=>11,
                "public"=>12,
                "static"=>50,
                "const"=>51,
                "var"=>53
            );
    }
    $tb=explode(" ", $m);
    usort($tb, function($a, $b) use ($ModiL){
        if(!isset($ModiL[$a])){
            $a="public";
        }
        if(!isset($ModiL[$b])){
            $b="public";
        }
        $c1=$ModiL[$a];
        $c2=$ModiL[$b];
        return $c1 < $c2 ? 1: ($c1 > $c2 ? 1: 0);
    });
    $s=implode(" ", $tb);
    return $s;
}
///<summary>Represente igk_treat_modifier_getname function</summary>
///<param name="base_def"></param>
///<param name="t"></param>
///<param name="start"></param>
///<param name="m"></param>
function igk_treat_modifier_getname($base_def, $t, $start, $m){
    $gt=$base_def.substr($t, 0, $start);
    $rgx="/(?P<name>(\\$)?(".IGK_TREAT_IDENTIFIER."))\\s*$/";
    if(preg_match($rgx, $gt, $tab)){
        $name=$tab["name"];
        $m->options->modifierArgs[]=(object)array(
                "modifier"=>igk_treat_modifier($m->options->modifier),
                "name"=>$name,
                "value"=>null,
                "offset"=>strlen($gt)
            );
    }
    else{
        igk_wln_e(__FILE__.":".__LINE__.":".__FUNCTION__, "target:".$t, "gt:".$gt, "base_def:".$base_def, "not name : ".$gt. " Line:".$m->options->lineNumber);
    }
}
///<summary>internal use. read modifier value on mode 2</summary>
function igk_treat_modifier_getvalue($base_def, $t, $start, $m){
    $md=igk_last($m->options->modifierArgs);
    if($md){
        $def=substr($t, 0, $start);
        $sv=(ltrim(substr($base_def, $md->offset))).$def;
        $rv=trim(substr($sv, strpos($sv, "=") + 1));
        $md->value=$rv;
    }
    $m->options->modFlag=1;
}
///<summary>treat output: </summary>
function igk_treat_outdef($def, $options, $nofiledesc=0){
    static $tlist=null;
    $out="";
    $tdef=array($def);
    $indent_l=-1;
    $indent="";
    $indent_c=function($v) use (& $indent, & $indent_l, $options){
        if(isset($v->indentLevel)){
            if($indent_l != $v->indentLevel){
                $idx=max(0, $v->indentLevel);
                $indent=str_repeat($options->IndentChar, $idx);
                $indent_l=$idx;
            }
        }
    };
    $def_file=0;
    if($tlist == null){
        $name_sort=function(& $tab){
            usort($tab, function($a, $b){
                return strcmp(strtolower($a->name), strtolower($b->name));
            });
        };
        $tlist=array(
                "use"=>(object)array(
                    "sort"=>$name_sort,
                    "doc"=>0,
                    "noXmlDoc"=>1,
                    "render"=>function($v){
                        return $v->src;
                    }
                ),
                "global"=>(object)array(
                    "doc"=>0,
                    "noXmlDoc"=>1,
                    "render"=>function($v){
                        return $v->src;
                    }
                ),
                "vars"=>(object)array(
                    "doc"=>0,
                    "noXmlDoc"=>1,
                    "sort"=>function(& $tab){
                        $q=$tab["tab"];
                        usort($q, function($a, $b){
                            $r=strcmp($a->modifier, $b->modifier);
                            if($r == 0){
                                $r=strcmp($a->name, $b->name);
                            }
                            return $r;
                        });
                        $tab["tab"]=$q;
                        $tab=array((object)array("tab"=>$tab, "indentLevel"=>-1));
                    },
                    "render"=>function($tab, $indent, & $tdef=null, $options=null){
                        $indent=str_repeat($options->IndentChar, $tab->tab["depth"]);
                        $inline_var=igk_gettsv($options, "command/noVarGroup") != 1;
                        $gp=igk_gettsv($options, "command/multilineVars");
                        $modifier=-1;
                        $sp="";
                        $mp='';
                        $lf="";
                        if($gp)
                            $lf=$options->LF.$indent;
                        $out="";
                        $tab=$tab->tab["tab"];
                        foreach($tab as $k=>$v){
                            if($inline_var && ($v->modifier != 'const')){
                                if(($modifier == -1) || ($modifier != $v->modifier)){
                                    if(($modifier !== -1) && ($modifier != "const")){
                                        $out .= ";".IGK_LF;
                                    }
                                    $modifier=$v->modifier;
                                    $out .= $indent.$modifier;
                                    if($gp)
                                        $mp=str_repeat(" ", strlen($modifier));
                                    else
                                        $mp="";
                                }
                                else{
                                    $out .= ",".$lf.$mp;
                                }
                                $out .= " ".$v->name;
                                if(($v->value !== null) && (!empty($v->value)))
                                    $out .= $sp."=".$sp.$v->value;
                            }
                            else{
                                $out .= $indent.$v->modifier." ".$v->name;
                                if($v->value !== null)
                                    $out .= "=".$v->value;
                                $out .= ";".IGK_LF;
                                $modifier=$v->modifier;
                            }
                        }
                        if($inline_var && ($modifier != "const")){
                            $out .= ";".IGK_LF;
                        }
                        return $out;
                    }
                ),
                "function"=>(object)array(
                    "sort"=>$name_sort,
                    "autodoc"=>function($v, $indent, $options){
                        if($_listname == null){
                            $_listname=["__construct"=>".ctr"];
                        }
                        $t_name=isset($_listname[$v->name]) ? $v->name: $v->name;
                        $t_out=$indent."///<summary>".__("represent")." ".$t_name." ".$v->type."</summary>".IGK_LF;
                        if(!isset($v->readP)){
                            igk_wln($v->src);
                            igk_wln("startline: ".$v->startLine);
                            igk_wln("function parameter not exists : ".$v->name);
                        }
                        if(!igk_getv($options, "noAutoParameter") && isset($v->readP)){
                            foreach($v->readP as $kv=>$vv){
                                $gs="";
                                $g=igk_createxmlnode("param");
                                $g["name"]=$vv->name;
                                if(isset($vv->default)){
                                    $g["default"]=$vv->default;
                                }
                                if(isset($vv->type)){
                                    $g["type"]=$vv->type;
                                }
                                if(isset($vv->ref) && $vv->ref){
                                    $g["ref"]="true";
                                }
                                $t_out .= $indent. "///".$g->render().IGK_LF;
                            }
                        }
                        if(($cond1=isset($v->ReturnType)) | ($cond2=(isset($v->options) && igk_getv($v->options, "ref")))){
                            $g=igk_createxmlnode("return");
                            if($cond1)
                                $g["type"]=$v->ReturnType;
                            if($cond2){
                                $g["refout"]="true";
                            }
                            $t_out .= $indent. "///".$g->render().IGK_LF;
                        }
                        if(isset($v->attributes)){
                            $t_out .= igk_str_format_bind($indent."//{0}".IGK_LF, explode(IGK_LF, $v->attributes));
                        }
                        return $t_out;
                    },
                    "doc"=>1
                ),
                "interface"=>(object)array(
                    "sort"=>function(& $tab){
                        usort($tab, function($a, $b){
                            $da=$a->{'@extends'} ?? "";
                            $db=$b->{'@extends'} ?? "";
                if(($r=($da <=> $db)) == 0)
                    return $a->name <=> $b->name;
                return $r;
            });
        },
                    "doc"=>1,
                    "render"=>function($v){
                        return $v->src;
                    }
                ),
                "trait"=>(object)array(
                    "sort"=>$name_sort,
                    "doc"=>1,
                    "render"=>function($v){
                        return $v->src;
                    }
                ),
                "class"=>(object)array(
                    "sort"=>function(& $tab){
                        $klist=array();
                        $nlist=array();
                        usort($tab, function($a, $b) use (& $nlist){
                            if(!isset($nlist[$a->name])){
                                    $nlist[$a->name]=$a;
                            }
                            if(!isset($nlist[$b->name])){
                                    $nlist[$b->name]=$b;
                            }
                            return strcmp($a->name, $b->name);
                        });
                        $v_sroot="/";
                        foreach($tab as $k=>$v){
                            $n=$v_sroot;
                            if(isset($v->{'@extends'})){
                                $p=$v->{'@extends'};
                                $n=$v_sroot.trim($v->{'@extends'});
                    $key=$v->name;
                    while($p && isset($nlist[$p])){
                                    $key=$p."/".$key;
                                    $p=igk_getv($nlist[$p], '@extends');
                                }
                    $klist[$key]=$v;
                }
                else{
                                    $klist[$v->name]=$v;
                            }
            }
            $cl=array_keys($klist);
            sort($cl);
            $outlist=array();
            foreach($cl as $k){
                            $v=$klist[$k];
                            $outlist[]=$v;
                        }
            $tab=$outlist;
        },
                    "doc"=>1,
                    "render"=>function($v, $indent, & $tdef=null){
                        return $v->src;
                    }
                ),
                "namespace"=>(object)array(
                    "sort"=>$name_sort,
                    "doc"=>1,
                    "render"=>function($v, $indent, & $tdef=null){
                        if($v->definitions && (strpos($v->src, "{") === false)){
                            array_push($tdef, $v->definitions);
                        }
                        return $v->src."// DIRECT RENDERING";
                    }
                )
            );
    }
    $gen_noxmldoc=igk_gettsv($options, "command/noXmlDoc");
    while($def=array_pop($tdef)){
        if(!$def_file && !$nofiledesc && igk_getv($options, "noFileDesc") != 1){
            $tab=igk_getv($def, "filedesc");
            $rpheader=$options->command && igk_getv($options->command, 'forceFileHeader', 0);
            if(!$rpheader && $tab && (count($tab) > 0)){
                foreach($tab as $k=>$v){
                    $out .= $v;
                }
                $out .= IGK_LF;
            }
            else{
                if($options->command){
                    $out .= igk_treat_getfileheader($options, basename($options->command->inputFile));
                }
            }
        }
        if(!$def_file){
            $tab=igk_getv($def, "FileInstruct");
            if($tab){
                foreach($tab as $line){
                    $out .= $line.IGK_LF;
                }
                $out .= IGK_LF;
            }
        }
        $def_file=1;
        ///TASK: treat use
        foreach($tlist as $k=>$v){
            $tab=igk_getv($def, $k);
            if(!$tab){
                continue;
            }
            if(isset($v->sort)){
                $sort=$v->sort;
                $sort($tab);
            }
            $doc=isset($v->doc) ? $v->doc: 0;
            $fc_autodoc=null;
            if(isset($v->autodoc)){
                $fc_autodoc=$v->autodoc;
            }
            else{
                $fc_autodoc=function($cv, $indent, $options){
                    if(!isset($cv->name)){
                        igk_wln($cv);
                        igk_die("name of the item not setup");
                    }
                    return $indent."///<summary>".__("represent")." ".$cv->type. ": ".$cv->name."</summary>".IGK_LF;
                };
            }
            $fc_render=isset($v->render) ? $v->render: function($v){
                return $v->src;
            };
            $noxml=igk_getv($v, "noXmlDoc") || $gen_noxmldoc;
            foreach($tab as $ck=>$cv){
                $indent_c($cv);
                if(!$noxml){
                    if($cv->documentation)
                        $out .= igk_str_format_bind($indent."///{0|trim}".IGK_LF, explode(IGK_LF, $cv->documentation));
                    else{
                        $out .= $fc_autodoc($cv, $indent, $options);
                    }
                }
                if($doc){
                    $out .= igk_treat_render_documentation($options, $cv, $indent);
                }
                $out .= $fc_render($cv, $indent, $tdef, $options);
            }
        }
    }
    return $out;
}
///<summary>use igk_treat_restore_context</summary>
function igk_treat_pop_state($option){
    $g=array_pop($option->states);
    if($g){
        foreach($g as $k=>$v){
            $option->$k=$v;
        }
    }
    return $g;
}
///<summary>use igk_treat_restore_context</summary>
function igk_treat_push_state($option, $keys){
    $obj=igk_createobj();
    if(!is_array($keys)){
        igk_wln_e(igk_show_trace());
    }
    foreach($keys as $k){
        if(isset($option->$k))
            $obj->$k=$option->$k;
    }
    $option->states[]=$obj;
    return $obj;
}
///<summary>Represente igk_treat_reg_command function</summary>
///<param name="k"></param>
///<param name="v"></param>
///<param name="help" default="null"></param>
function igk_treat_reg_command($k, $v, $help=null){
    $c=igk_treat_command();
    $cmd=explode(",", $k);
    foreach($cmd as $kk=>$vv){
        $c[trim($vv)
        ]=$v;
    }
    if($help){
        $ch=igk_get_env("treat//command_help", array());
        $ch[$k]=$help;
        igk_set_env("treat//command_help", $ch);
    }
    igk_set_env("treat//command", $c);
}
///<summary>Represente igk_treat_render_data function</summary>
///<param name="tab"></param>
///<param name="title" default="null"></param>
///<param name="r" ref="true"></param>
function igk_treat_render_data($tab, $title=null, & $r=0){
    $o="";
    foreach($tab as $k){
        if($title == null){
            $title=$k->type."s:";
        }
        $o .= ($k->type." ".$k->name." ").IGK_LF;
        $r++;
    }
    if($r > 0){
        igk_wln($title);
        igk_wln($o);
    }
}
///<summary>Represente igk_treat_render_documentation function</summary>
///<param name="options"></param>
///<param name="v"></param>
///<param name="indent"></param>
function igk_treat_render_documentation($options, $v, $indent){
    if(isset($options->documentationListener)){
        $o="";
        foreach($options->documentationListener as $k){
            $o .= call_user_func_array($k, array($options, $v, $indent));
        }
        return $o;
    }
    return null;
}
///<summary>Represente igk_treat_reset_flags function</summary>
///<param name="options"></param>
function igk_treat_reset_flags($options){
    $options->multiLineFlag=0;
}
///<summary>Represente igk_treat_reset_modifier function</summary>
///<param name="options"></param>
function igk_treat_reset_modifier($options){
    if($options->context == "modifierReading"){
        igk_treat_restore_context($options, 1);
    }
    $options->modifier="";
    $options->modifierArgs=null;
    $options->modFlag=0;
    $options->modOffset=-1;
}
///<summary>Represente igk_treat_restore_context function</summary>
///<param name="option"></param>
function igk_treat_restore_context($option){
    $inf=array_pop($option->chaincontext);
    if($inf){
        $option->context=$inf[0];
        $option->mode=$inf[1];
        $option->tag=$inf[2];
    }
    igk_treat_pop_state($option);
}
///<summaryr>set or replace current output</summmary>
///<param name="options">definition options</param>
///<param name="text">new text</param>
function igk_treat_set($options, $t){
    igk_debug_wln("settext :".$t);
    if(empty($options->data) && ($options->mode == 0)){
        $g=& $options->output;
    }
    else{
        $g=& $options->data;
    }
    $g=$t;
}
///<summary>Represente igk_treat_set_context function</summary>
///<param name="option"></param>
///<param name="context"></param>
///<param name="mode"></param>
///<param name="states" default="null"></param>
///<param name="tag" default="null"></param>
function igk_treat_set_context($option, $context, $mode=0, $states=null, $tag=null){
    $option->chaincontext[]=array($option->context, $option->mode, $option->tag);
    $option->context=$context;
    $option->mode=$mode;
    $option->tag=$tag;
    $states=$states ? $states: array();
    igk_treat_push_state($option, $states);
}
///<summary>Represente igk_treat_show_usage function</summary>
function igk_treat_show_usage(){
    igk_wln(<<<EOF
This is igkdev trait php source code CLI
author: C.A.D. BONDJE DOUE
copyright: IGKDEV @ 2019
version : 1.0

usage:

EOF
    );
    $helps=igk_get_env("treat//command_help");
    if($helps){
        $keys=array_keys($helps);
        usort($keys, function($a, $b){
            $p=0;
            $aa="";
            $bb="";
            while(($p < strlen($a)) && ($a[$p] == "-")){
                $aa .= "-";
                $p++;
            }
            $p=0;
            while(($p < strlen($a)) && ($b[$p] == "-")){
                $bb .= "-";
                $p++;
            }
            if($aa == $bb){
                return $a <=> $b;
            }
            else{
                if($aa == "-"){
                    return -1;
                }
                else{
                    return 1;
                }
            }
        });
        $lft=str_pad("", 36, " ");
        foreach($keys as $k){
            $v=$helps[$k];
            igk_wl(str_repeat(" ", 2)."\e[1;32m". str_pad($k, 30, " ")."\e[0m".str_repeat(" ", 2). ": ". $v."\n");
        }
    }
}
///<summary>Represente igk_treat_skip function</summary>
///<param name="t" ref="true"></param>
///<param name="start"></param>
///<param name="offset" ref="true"></param>
///<param name="m"></param>
function igk_treat_skip(& $t, $start, & $offset, $m){
    $offset=$start + strlen($m->data[0][0]);
    return $t;
}
///<summary>Represente igk_treat_source function</summary>
///<param name="source"></param>
///<param name="callback"></param>
///<param name="tab" default="null"></param>
///<param name="options" default="null" ref="true"></param>
function igk_treat_source($source, $callback, $tab=null, & $options=null){
    if(is_string($source)){
        $source=explode("\n", $source);
    }
    $options=$options ?? igk_treat_create_options();
    $tab=$tab ?? igk_treat_source_expression($options);
    $out=& $options->output;
    $offset=& $options->offset;
    $sline=& $options->lineNumber;
    $tline=igk_count($source);
    $options->totalLines=$tline;
    $options->source=$source;
    $options->{"@automatcher_flag"}=array();
    $flag=0;
    $autoreset_flag=& $options->{"@automatcher_flag"};
    while($sline < $tline){
        $t=$source[$sline];
        $sline++;
        if($options->IgnoreEmptyLine && (strlen(trim($t)) == 0)){
            continue;
        }
        if($flag){
            if($options->DataLFFlag && ($options->conditionDepth<=0)){
                $options->DataLFFlag=0;
                igk_treat_append($options, $options->LF, 0);
            }
            else{
                if(is_object($options->toread) && ($options->toread->mode == 0)){
                    $options->DataLFFlag=0;
                    igk_treat_append($options, " ", 0);
                }
            }
        }
        if(($hread=$options->toread) && isset($hread->newLineTreat) && ($n_fc=$hread->newLineTreat)){
            $n_fc($t, $sline, $options);
        }
        unset($hread);
        $flag=1;
        $matchFlag=0;
        $tq=array(rtrim($t));
        $offset=0;
        $auto_reset_list=["operatorFlag", "mustPasLineFlag"];
        while($t=array_pop($tq)){
            $matches=null;
            $mlist=null;
            foreach($tab as $k=>$v){
                if(((is_callable($gf=$v->mode) && $gf($options)) || ($v->mode === "*") || ($v->mode === $options->mode)) && preg_match($v->pattern, $t, $matches, PREG_OFFSET_CAPTURE, $offset)){
                    $start=$matches[0][1];
                    if(!$mlist || ($mlist->start > $start)){
                        if(!$mlist)
                            $mlist=(object)array();
                        $mlist->start=$start;
                        $mlist->matcher=$v;
                        $mlist->data=$matches;
                        $mlist->options=$options;
                    }
                }
            }
            if($mlist){
                foreach($auto_reset_list as $re){
                    if($options->$re){
                        if(isset($autoreset_flag[$re])){
                            $options->$re=0;
                            unset($autoreset_flag[$re]);
                        }
                        else
                            $autoreset_flag[$re]=1;
                    }
                }
                if($options->endMarkerFlag && isset($options->definitions->lastTreat)){
                    if(isset($autoreset_flag["endMarkerFlag"])){
                        $options->endMarkerFlag=0;
                        unset($autoreset_flag["endMarkerFlag"]);
                    }
                    else
                        $autoreset_flag["endMarkerFlag"]=1;
                }
                igk_debug_wln("matcher: ".$mlist->matcher->name);
                $fc=$mlist->matcher->callback;
                $t=$fc($t, $mlist->start, $offset, $mlist);
                if(!empty($t)){
                    array_push($tq, $t);
                    continue;
                }
            }
            break;
        }
        $s=trim($t);
        if((strlen($s) == 0) && $options->IgnoreEmptyLine){
            $flag=0;
        }
        else{
            igk_treat_append($options, ltrim($t), 0);
        }
    }
    unset($options->{"@automatcher_flag"});
    if($callback){
        return $callback($out, $options);
    }
    return $out;
}
///<summary>represent php treat expression</Summary>
function igk_treat_source_expression($options=null){
    static $defExpression=null;
    $tab=array();
    if($defExpression == null){
        $defExpression=1;
    }
    array_unshift($tab, (object)array(
            "name"=>"switchCaseOperatorHandle",
            "mode"=>"*",
            "pattern"=>"/(^|\\s+)(?P<operator>(case|default))(\\s+|$)/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $idx=$m->data["operator"];
                $op_n=$idx[0];
                $space=" ";
                if(igk_treat_handle_operator_flag($m, $op_n, $t, $start, $offset)){
                    return $t;
                }
                if($op_n == "default"){
                    $space="";
                }
                switch($op_n){
                    case "case":
                    case "default":
                    $m->options->switchcaseFlag=1;
                    break;
                }
                igk_treat_append($m->options, $op_n.$space, 1);
                $t=substr($t, $start + strlen($m->data[0][0]));
                $offset=0;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"controlConditionalHandle",
            "mode"=>"*",
            "pattern"=>"/(^|[^a-zA-Z0-9_ ]|\\s+|)(?P<operator>(for|if|elseif|while|do|switch|foreach|try|finaly|catch|array))\\s*(\(|\{|$)/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $idx=$m->data["operator"];
                $h=trim(substr($t, $offset, $start - $offset));
                if(($start != $offset) && !empty(trim(substr($t, $offset, $start - $offset)))){
                    $offset=$idx[1] + strlen($idx[0]);
                    return $t;
                }
                $offset=$idx[1] + strlen($idx[0]);
                $indent=1;
                $sp=" ";
                if(preg_match("/(for(each)?|while|catch|switch|if|elseif|array)/", $idx[0])){
                    $sp="";
                }
                $m->bracketVarFlag=1;
                switch($idx[0]){
                    case "do":
                    $m->bracketVarFlag=0;
                    $m->options->doMarkerFlags=1;
                    $m->options->DataLFFlag=1;
                    igk_treat_append($m->options, "do", 1);
                    $m->options->DataLFFlag=0;
                    $t=substr($t, $offset);
                    $offset=0;
                    return $t;
                    case "array":
                    $g=igk_treat_init_array_reading($m, $start, $t, $cancel);
                    if($cancel){
                        return $g;
                    }
                    break;
                }
                igk_treat_set_context($m->options, $m->matcher->name, 0, array("toread"));
                switch($idx[0]){
                    case "array":
                    $m->options->depthFlag=0;
                    $indent=0;
                    $sp="";
                    break;default: 
                    if(($idx[0] == "while") && isset($m->options->doMarkerFlags)){
                        $m->options->doMarkerFlags=0;
                        unset($m->options->doMarkerFlags);
                        $m->options->endDoWhileMarkerFlag=1;
                        $indent=0;
                        $m->options->DataLFFlag=0;
                        $idx[0]=" ".$idx[0];
                    }
                    else{
                        $m->options->DataLFFlag=1;
                    }
                    if($m->options->depthFlag){
                        $m->options->DataLFFlag=0;
                        $indent=0;
                        $m->options->depthFlag=0;
                        $idx[0]=" ".$idx[0];
                    }
                    break;
                }
                igk_treat_append($m->options, substr($t, 0, $start).$idx[0].$sp, $indent);
                $t=substr($t, $offset);
                $offset=0;
                $m->options->toread=$idx[0];
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"depthFlagHandle",
            "mode"=>"*",
            "pattern"=>"/(^|\\s+)(?P<operator>(else))(\\s*|[^a-zA-Z0-9]|$)/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $d=$m->data[0][0];
                $s=preg_replace("/\\s*/", "", $d);
                $m->options->DataLFFlag=1;
                igk_treat_append($m->options, $s, 1);
                $t=substr($t, $start + strlen($d));
                $offset=0;
                $m->options->depthFlag=1;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"operatorHandle",
            "mode"=>"*",
            "pattern"=>"/\\s*(?P<operator>(((=|\!)==|::|\>\>|\<\<|\+\+|\-\-|&&|\<=\>|\<=|\>=|\<\>|(=|-)\>(\{)?|(\|\|)|\?\?)|\<|\>|([\-\+\/\*%\<\>\=\.\|\&\!\^])?=|[\.&,:\+\-\*%\|\?\!]))\\s*/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $offset=$start + strlen($m->data[0][0]);
                $ln=strlen($m->data[0][0]);
                $g=preg_replace("/\\s+/", "", $m->data[0][0]);
                $ch=$g;
                $gx=empty(ltrim(substr($t, 0, $start)));
                if(strlen($ch) == 1){
                    igk_treat_handle_char($t, $start, $offset, $g, $m);
                }
                igk_treat_handle_modargs($t, $start, $offset, $g, $m);
                $v=trim(substr($t, 0, $start));
                $m->options->operatorFlag=$g;
                if($m->options->FormatText){
                    $v_indent=0;
                    $h=0;
                    switch($g){
                        case "&":
                        if((($m->options->context == "parameterReading") && ($m->options->toread) && isset($m->options->toread->readP) && ($start > 0)) || (($m->options->context == 'global') && !$m->options->mode)){
                            $g=" ".$g." ";
                            break;
                        }
                        $g="& ";
                        break;
                        case "=>":
                        if(isset($m->options->arrayDepth) && ($m->options->arrayDepth > 0)){
                            $q=$m->options->arrayEntity[count($m->options->arrayEntity)-1];
                            $q->isassoc=1;
                        }
                        break;
                        case ",":
                        $g .= " ";
                        if(($m->options->toread == "array") && isset($m->options->arrayDepth) && ($m->options->arrayDepth > 0)){
                            $q=$m->options->arrayEntity[count($m->options->arrayEntity)-1];
                            $h=1;
                            $m->options->DataLFFlag=0;
                            igk_treat_append($m->options, $v.$g, 0);
                            igk_treat_update_array_item($q, null,  - strlen($g), $m);
                        }
                        break;
                        case ":":
                        $m->options->DataLFFlag=0;
                        if($m->options->switchcaseFlag){
                            $m->options->switchcaseFlag=0;
                            $m->options->operatorFlag=0;
                            igk_treat_append($m->options, $v.$g, 0);
                            $m->options->DataLFFlag=1;
                            $h=1;
                        }
                        else{}$g=$g." ";
                        break;
                        case "=":
                        if(($cmd=$m->options->command) && isset($cmd->allowSpaceAffectation) && $cmd->allowSpaceAffectation){
                            $g=" ".$g." ";
                        }
                        if($m->options->bracketVarFlag){
                            $m->options->DataLFFlag=1;
                            if(!$m->options->conditionDepth)
                                igk_treat_append($m->options, "", 1);
                            $m->options->DataLFFlag=0;
                            $m->options->bracketVarFlag=0;
                        }
                        break;
                        case "->{":
                        $m->options->bracketVarFlag=1;
                        if($m->options->DataLFFlag){
                            igk_treat_append($m->options, $v.$g, 1);
                            $h=1;
                        }
                        $m->options->DataLFFlag=0;
                        $m->options->bracketDepth++;
                        break;
                        case "->":
                        $m->options->bracketVarFlag=0;
                        $m->options->objectPointerFlag=1;
                        break;
                        case "::":
                        $m->options->bracketVarFlag=0;
                        break;
                        case "<=":
                        case ">=":
                        case "++":
                        case "--":
                        case "!":
                        case "~":
                        break;
                        case '.':
                        if(preg_match("/(\\s+)/", $m->data[0][0]))
                            $g .= " ";
                        if(igk_gettsv($m->options, "command/textMultilineConcatenation")){
                            $v_indent=0;
                            $h=1;
                            igk_treat_append($m->options, $v.$g, $v_indent);
                            $m->options->DataLFFlag=1;
                        }
                        break;
                        case '-':
                        if($start < strlen($t)-1){
                            if(!preg_match("/^(\.)?[0-9]/", substr($t, $start + 1))){
                                $g=" ".$g." ";
                            }
                        }
                        break;default: $g=" ".$g." ";
                        break;
                    }
                    if(!$h)
                        igk_treat_append($m->options, $v.$g, $v_indent);
                    $t=substr($t, $start + $ln);
                    $offset=0;
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"specialOperator",
            "mode"=>"*",
            "pattern"=>"/(\\s+|^)(?P<operator>(OR|AND|XOR|as))(\\s+|$)/i",
            "callback"=>function(& $t, $start, & $offset, $m){
                $offset=$start + strlen($m->data[0][0]);
                $ln=strlen($m->data[0][0]);
                $g=preg_replace("/\\s+/", "", $m->data[0][0]);
                $ch=$g;
                $gx=empty(ltrim(substr($t, 0, $start)));
                if(strlen($ch) == 1){
                    igk_treat_handle_char($t, $start, $offset, $g, $m);
                }
                igk_treat_handle_modargs($t, $start, $offset, $g, $m);
                $v=trim(substr($t, 0, $start));
                $RP=array("OR"=>"||", "AND"=>"&&", "XOR"=>"|");
                $m->options->operatorFlag=$g;
                if($m->options->FormatText){
                    $h=0;
                    $g=" ".igk_getv($RP, strtoupper($g), $g)." ";
                    if(!$h)
                        igk_treat_append($m->options, $v.$g, 0);
                    $t=substr($t, $start + $ln);
                    $offset=0;
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"bracketHandle",
            "mode"=>'*',
            "pattern"=>"/\\s*(\{|\})/",
            "callback"=>function(& $t, $start, & $offset, $m){
                if($m->options->depthFlag){
                    $m->options->depthFlag=0;
                }
                $sd=$m->data[0][0];
                $d=trim($sd);
                $noffset=$start + strlen($sd);
                igk_treat_handle_char($t, $start, $offset, $d, $m);
                if($d == "}"){
                    $m->options->bracketDepth--;
                    $lf=1;
                    if($m->options->bracketVarFlag){
                        $m->options->bracketVarFlag=0;
                        $lf=0;
                        $m->options->DataLFFlag=0;
                    }
                    else{
                        $m->options->DataLFFlag=1;
                    }
                    if($m->options->FormatText){
                        $g=trim(substr($t, 0, $start));
                        if(!empty($g)){
                            igk_treat_append($m->options, $g."}", $lf);
                            $lf=0;
                        }
                        else{
                            igk_treat_append($m->options, "}", $lf);
                        }
                    }
                    else{
                        igk_treat_append($m->options, "}", 0);
                    }
                    $t=substr($t, $noffset);
                    $offset=0;
                    if($m->options->bracketDepth<=0){
                        if(!empty($d=$m->options->data)){
                            $m->options->data="";
                            if(is_object($m->options->toread)){
                                $fc=$m->options->toread->endTreat;
                                $fc($d, $m->options, $m->options->toread);
                            }
                        }
                    }
                    $m->options->DataLFFlag=$lf;
                    if($m->options->arrayDepth > 0){
                        $q=$m->options->arrayEntity[count($m->options->arrayEntity) - 1];
                        if(!isset($q->arrayBlockDepthFlag))
                            $q->arrayBlockDepthFlag=1;
                        $q->arrayBlockDepthFlag--;
                        if($q->arrayBlockDepthFlag<=0)
                            $m->options->arrayBlockDepthFlag=0;
                    }
                }
                else{
                    if($m->options->context == "controlConditionalHandle"){
                        igk_treat_restore_context($m->options, 1);
                    }
                    $ln_segment=1;
                    $m->options->bracketVarFlag=1;
                    if($m->options->objectPointerFlag){
                        $ln_segment=0;
                        $m->options->bracketVarFlag=1;
                        $m->options->objectPointerFlag=0;
                    }
                    $f=trim(substr($t, 0, $start).$d);
                    $bck=$m->options->DataLFFlag;
                    $m->options->DataLFFlag=0;
                    igk_treat_append($m->options, $f, 0);
                    $m->options->DataLFFlag=$bck;
                    if($m->options->FormatText){
                        $m->options->DataLFFlag=$ln_segment;
                    }
                    $t=ltrim(substr($t, $noffset));
                    $offset=0;
                    $m->options->bracketDepth++;
                    if($m->options->arrayDepth > 0){
                        $q=$m->options->arrayEntity[count($m->options->arrayEntity) - 1];
                        if(!isset($q->arrayBlockDepthFlag))
                            $q->arrayBlockDepthFlag=0;
                        $q->arrayBlockDepthFlag++;
                        $m->options->arrayBlockDepthFlag=1;
                    }
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"escapeMultistringSpace",
            "mode"=>"*",
            "pattern"=>"/\\s{2,}/",
            "callback"=>function(& $t, $start, & $offset, $m){
                if($m->options->context != 'html'){
                    $ln=strlen($m->data[0][0]);
                    if($start == 0){
                        $t=substr($t, $ln);
                    }
                    else
                        $t=igk_str_insert(" ", $t, $start, $start + strlen($m->data[0][0]));
                    $offset=$start;
                }
                else{
                    $offset=$start + strlen($m->data[0][0]);
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"hookDefinition",
            "mode"=>"*",
            "pattern"=>"/\\s*(\[\\s*|\])/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $d=trim($m->data[0][0]);
                if(!isset($m->options->openHook))
                    $m->options->openHook=0;
                if(!isset($m->options->arrayDepth)){
                    $m->options->arrayDepth=0;
                }
                if($d == "["){
                    $m->options->conditionDepth++;
                    $m->options->openHook++;
                    igk_treat_start_array($m, $t, $start);
                }
                else{
                    $m->options->conditionDepth--;
                    $m->options->openHook--;
                    igk_treat_end_array($m, $t, $start, $offset);
                }
                igk_treat_handle_char($t, $start, $offset, $d, $m);
                $offset=$start + strlen($m->data[0][0]);
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"controlCondition",
            "mode"=>"*",
            "pattern"=>"/\\s*(\(\\s*|\))/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $st=$m->data[0][0];
                $d=trim($st);
                $ln=strlen($m->data[0][0]);
                if($m->options->FormatText && ($ln > 1)){
                    $t=igk_str_insert($d, $t, $start, $start + strlen($m->data[0][0]));
                    $offset=$start + 1;
                }
                else{
                    $offset=$start + $ln;
                }
                if($d == ")"){
                    $m->options->conditionDepth--;
                    if($m->options->DataLFFlag){
                        if($m->options->mustPasLineFlag){
                            $m->options->DataLFFlag=1;
                        }
                        else
                            $m->options->DataLFFlag=0;
                    }
                }
                else{
                    $m->options->conditionDepth++;
                }
                if($m->options->conditionDepth < 0){
                    igk_wln_e("something wrong....condition depth equal to :".$m->options->conditionDepth. " line:".$m->options->lineNumber);
                }
                igk_treat_handle_char($t, $start, $offset, $d, $m);
                switch($m->options->context){
                    case "controlConditionalHandle":
                    $depthf=1;
                    if($m->options->toread == "array"){
                        $q=$m->options->arrayEntity[count($m->options->arrayEntity)-1];
                        $update_depth_litteral=0;
                        if($q->litteral){
                            if($d == ")"){
                                if(!isset($q->hookDepth)){
                                    igk_wln_e("ttt: hookDepth not define".$t);
                                }
                                else{
                                    $q->hookDepth--;
                                    if($q->hookDepth<=0){
                                        igk_treat_end_array($m, $t, $start, $offset);
                                        $depthf=0;
                                        $soutput=igk_treat_get($m->options);
                                        igk_treat_restore_context($m->options, 1);
                                        $s=trim(substr($t, 0, $start + 1));
                                        igk_treat_append($m->options, $s, 0);
                                        $t=ltrim(substr($t, $offset));
                                        $offset=0;
                                        return $t;
                                    }
                                }
                            }
                            else{
                                if(!isset($q->hookDepth))
                                    $q->hookDepth=1;
                                else
                                    $q->hookDepth++;
                            }
                            $update_depth_litteral=1;
                            $s=trim(substr($t, 0, $start + 1));
                            igk_treat_append($m->options, $s, 0);
                            $offset=0;
                            $t=substr($t, $start + 1);
                            return $t;
                        }
                    }
                    if($m->options->conditionDepth == 0){
                        igk_treat_restore_context($m->options, 1);
                        $s=substr($t, 0, $start + 1);
                        igk_treat_append($m->options, $s, 0);
                        $t=ltrim(substr($t, $offset));
                        $offset=0;
                        $m->options->depthFlag=$depthf;
                        if($m->options->objectPointerFlag){
                            $m->options->objectPointerFlag=0;
                        }
                    }
                    break;
                    case "globalConstant":
                    if($m->options->conditionDepth == 0){
                        $s=ltrim(substr($t, 0, $start + 1));
                        $t=ltrim(substr($t, $offset));
                        $offset=0;
                        igk_treat_append($m->options, $s, 0);
                    }
                    break;
                    case "parameterReading":default: $s=ltrim(substr($t, 0, $start + 1));
                    if(($d == ")") && ($m->options->conditionDepth<=0)){
                        if(igk_getv($m->options, "multiLineFlag") == 1){
                            $s=$m->options->LF.$s;
                            $m->options->multiLineFlag=0;
                        }
                    }
                    else if(($d == "(") && ($m->options->conditionDepth<=1)){
                        $s=ltrim(substr($t, 0, $start + 1));
                        if(igk_getv($m->options, "multiLineFlag") == 1){
                            $s=$m->options->LF.$s;
                            $m->options->multiLineFlag=0;
                        }
                    }
                    igk_treat_append($m->options, $s, 0);
                    $t=ltrim(substr($t, $offset));
                    $offset=0;
                    break;
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"endInstruction",
            "mode"=>"*",
            "pattern"=>"/\\s*(;)\\s*/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $d=$m->data[0][0];
                if(isset($m->options->lastOperand)){
                    $cc=ltrim(substr($t, 0, $start));
                    if(!empty($cc)){
                        $cc=" ".$cc.$d;
                    }
                    $m->options->DataLFFlag=0;
                    igk_treat_append($m->options, $cc, 0);
                    $m->options->DataLFFlag=1;
                    unset($m->options->lastOperand);
                    $t=ltrim(substr($t, $start + strlen($d)));
                    $offset=0;
                    $m->options->bracketVarFlag=0;
                    return $t;
                }
                $cbk=$m->options->DataLFFlag;
                $s=preg_replace("/\\s*/", "", $d);
                $ob_pointer=$m->options->objectPointerFlag;
                if($m->options->objectPointerFlag){
                    $m->options->objectPointerFlag=0;
                }
                if($m->options->DataLFFlag){
                    if(trim($t) == ";"){
                        $m->options->DataLFFlag=0;
                    }
                }
                if($m->options->bracketVarFlag)
                    $m->options->bracketVarFlag=0;
                if(($toread=$m->options->toread) && is_object($toread) && ($toread->mode < 4) && ($fc=$toread->endTreat)){
                    $s=rtrim(igk_treat_get($m->options).substr($t, 0, $start).$s).$m->options->LF;
                    $fc($s, $m->options, $toread);
                    $t=substr($t, $start + strlen($d));
                    $offset=0;
                    return $t;
                }
                switch($v_context=$m->options->context){
                    case "controlConditionalHandle":
                    $offset=$start + 1;
                    $indent=0;
                    if(igk_getv($m->options, "endDoWhileMarkerFlag") == 1){
                        $m->options->endDoWhileMarkerFlag=0;
                    }
                    igk_treat_append($m->options, substr($t, 0, $start).$s." ", $indent);
                    $t=substr($t, $start + strlen($d));
                    $offset=0;
                    return $t;
                    break;
                    case "globalConstant":
                    $m->options->DataLFFlag=0;
                    igk_treat_append($m->options, trim(substr($t, 0, $start)).$s, 0);
                    $totreat=$m->options->toread;
                    if($totreat == null){
                        igk_die("totreat is null");
                    }
                    $deff=igk_treat_get($m->options).$m->options->LF;
                    igk_treat_restore_context($m->options, 1);
                    if(!empty($deff)){
                        $objd=(object)array(
                        "src"=>$deff,
                        "line"=>$totreat->startLine,
                        "type"=>$totreat->type
                    );
                        if(is_object($m->options->toread)){
                            $m->options->toread->definitions["global"][]=$objd;
                        }
                        else
                            $m->options->definitions->{"global"}[]=$objd;
                    }
                    $t=substr($t, $start + strlen($d));
                    if($totreat->comment){
                        $t="";
                    }
                    $offset=0;
                    break;default: $gg=trim(substr($t, 0, $start)).$s;
                    $h=0;
                    $def=igk_treat_get($m->options);
                    if(!empty($m->options->modifier)){
                        if($m->options->modFlag == 2){
                            igk_treat_modifier_getvalue($def, $t, $start, $m);
                        }
                        else if($m->options->modFlag == 1){
                            igk_treat_modifier_getname($def, $t, $start, $m);
                        }
                        if(is_object($cop=$m->options->toread) && igk_treat_handle_modifier($m->options)){
                            $h=1;
                        }
                        igk_treat_reset_modifier($m->options);
                    }
                    if(!$h){
                        if($m->options->endMarkerFlag && isset($m->options->definitions->lastTreat)){
                            $m->options->endMarkerFlag=0;
                            $ls=$m->options->definitions->lastTreat;
                            if(!isset($ls->endMarker) && preg_match("/(use|function|define)/", $ls->type)){
                                if(($ls->type == "function") && (igk_getv($ls, "isanonymous") == 1)){
                                    $cc=trim($gg);
                                    if($cc !== ";"){
                                        igk_wln_e("not valid end marker:".$cc. " ".$m->options->lineNumber);
                                    }
                                    $m->options->DataLFFlag=0;
                                    igk_treat_append($m->options, $cc, 0);
                                    $m->options->DataLFFlag=$cbk;
                                }
                                else{
                                    $ls->src=rtrim($ls->src).$ls->type.":".trim($gg).$m->options->LF;
                                }
                                $h=1;
                                $ls->endMarker=1;
                            }
                        }
                        if(!$h){
                            $indent=0;
                            if(igk_getv($m->options, "endDoWhileMarkerFlag") == 1){
                                $m->options->endDoWhileMarkerFlag=0;
                                $m->options->depthFlag=0;
                            }
                            igk_treat_append($m->options, trim($gg), $indent);
                        }
                    }
                    $t=ltrim(substr($t, $start + strlen($d)));
                    $offset=0;
                    break;
                }
        $m->options->DataLFFlag=1;
        $m->options->bracketVarFlag=0;
        return $t;
    }
        ));
    array_unshift($tab, (object)array(
            "name"=>"uncollapsestring",
            "mode"=>'*',
            "pattern"=>"/(\"|')/i",
            "callback"=>function(& $t, $start, & $offset, $m){
                $lis=$start;
                $ch=$t[$start];
                $s="";
                $multilinestart=($ch == "'");
                $ln=& $m->options->lineNumber;
                $tln=$m->options->totalLines;
                $before=substr($t, 0, $start);
                $x=substr($t, $start + 1);
                $start=0;
                $escaped=0;
                while((($pos=strpos($x, $ch, $start)) === false) && ($ln < $tln) || ($escaped=(($pos > 0) && $x[$pos-1] == '\\'))){
                    if($escaped){
                        if($pos > 1){
                            if($x[$pos-2] == "\\"){
                                break;
                            }
                        }
                        $start=$pos + 1;
                        $escaped=0;
                        continue;
                    }
                    $s .= substr($x, $start).$m->options->LF;
                    $x=$m->options->source[$ln];
                    $ln++;
                    $start=0;
                    $escaped=0;
                }
                if($pos !== false){
                    $t=substr($x, $pos + 1);
                    $offset=0;
                    $s .= substr($x, 0, $pos);
                    $s=$before.$ch.$s.$ch;
                    $offset=strlen($s);
                    $t=$s.$t;
                }
                else{
                    igk_wln_e("something wrong ... string litteral", $t);
                }
                return $t;
                $offset=$lis + 1;
                $m->options->reading=$s;
                if($m->options->context != 'globalConstant'){
                    $m->options->stringReading[]=(object)array("data"=>$s, "line"=>$m->options->lineNumber);
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"modifierDeclaration",
            "mode"=>"*",
            "pattern"=>"/(^|\\s+)(?P<modifier>((private|public|protected|final|abstract|const|static|global|var)(\\s+|$))+)/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $skip=0;
                if(isset($m->options->inlineFunctionReadingFlag)){
                    if($m->options->inlineFunctionReadingFlag<=$m->options->bracketDepth){
                        $skip=1;
                    }
                    else
                        unset($m->options->inlineFunctionReadingFlag);
                }
                $modifier=$m->data["modifier"][0];
                if((trim($modifier) == "const")){
                    if(igk_treat_handle_use($m, 'const')){
                        igk_treat_reset_modifier($m->options);
                        $t=ltrim(substr($t, $m->data["modifier"][1] + 5));
                        $offset=0;
                        return $t;
                    }
                    if(($m->options->mode == 0) || (!$m->options->toread)){
                        $skip=1;
                        igk_wln("skippingmode: ".$m->options->mode);
                    }
                }
                if($skip){
                    return igk_treat_skip($t, $start, $offset, $m);
                }
                $offset=$start + strlen($m->data[0][0]);
                $ln=& $m->options->lineNumber;
                $tn=$m->options->totalLines;
                if($offset>=strlen($t)){
                    $toffset=0;
                    while($ln < $tn){
                        $st=trim($m->options->source[$ln]);
                        $ln++;
                        $offset=0;
                        if(empty(trim($st))){
                            continue;
                        }
                        $t=$st;
                        if(preg_match($m->matcher->pattern, $st, $ctab, PREG_OFFSET_CAPTURE, $offset)){
                            if($ctab[0][1] != 0){
                                break;
                            }
                            else{
                                $modifier .= " ".trim(preg_replace("/\\s+/", " ", $ctab["modifier"][0]));
                                $offset=$ctab[0][1] + strlen($ctab[0][0]);
                                $t=ltrim(substr($t, $offset));
                                $offset=0;
                                if(empty($t)){
                                    continue;
                                }
                                break;
                            }
                        }
                        else{
                            break;
                        }
                    }
                }
                if($offset > 0){
                    $t=substr($t, $offset);
                    $offset=0;
                }
                $o=null;
                if(is_object($m->options->toread))
                    $o=igk_getv($m->options->toread->definitions, "vars");
                igk_treat_set_context($m->options, "modifierReading", $m->options->mode, array("data", "DataLFFlag", "bracketVarFlag"));
                $mod=trim($modifier);
                $m->options->data=$mod." ";
                $m->options->DataLFFlag=0;
                $m->options->modifierArgs=$o ? $o["tab"]: array();
                $m->options->modifier=$mod;
                $m->options->modFlag=1;
                $m->options->modOffset=strlen(rtrim(igk_treat_get($m->options))) + $start;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"definitionDeclaration",
            "mode"=>"*",
            "pattern"=>"/(^|\\s+)(?P<type>(interface|class|trait|function|namespace|use))(\\s+|\\(|$)/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $type=$m->data["type"][0];
                $type_offset=$m->data["type"][1] + strlen($m->data["type"][0]);
                $tab=array();
                $sub=substr($t, $offset, $start - $offset);
                $modifier=igk_treat_modifier($m->options->modifier);
                $totreat=$m->options->toread;
                igk_treat_reset_modifier($m->options);
                if(igk_treat_handle_use($m, "function")){
                    $t=ltrim(substr($t, $type_offset));
                    $offset=0;
                    return $t;
                }
                $initContext_callback=function($m){
                    igk_treat_set_context($m->options, $m->matcher->name, $m->options->mode, ["toread", "mode", "data", "bracketDepth", "conditionDepth", "offsetDepth", "DataLFFlag"]);
                    $level=$m->options->offsetDepth + $m->options->bracketDepth;
                    $m->options->offsetDepth=$level;
                    $m->options->bracketDepth=0;
                    $m->options->mode=1;
                    $m->options->data="";
                    $m->options->DataLFFlag=0;
                    $m->options->conditionDepth=0;
                    $m->options->openHook=0;
                };
                if(igk_treat_handle_operator_flag($m, $type, $t, $start, $offset)){
                    return $t;
                }
                if($type == "use"){
                    igk_treat_reset_modifier($m->options);
                    if(($treat=$m->options->toread) && ($treat->type == "function") && isset($treat->isanonymous) && ($treat->isanonymous)){
                        $offset=$m->data["type"][1] + strlen($type);
                        $treat->usingDefition=1;
                        $u=igk_createobj();
                        $u->type="useparameter";
                        $u->definitions=array();
                        $u->src="";
                        $u->mode=0;
                        $u->readPMode=0;
                        $u->startLine=$m->options->lineNumber;
                        $u->parent=$treat;
                        $u->def="(";
                        $fdef=" ".$type." (";
                        igk_treat_append($m->options, substr($t, 0, $start).$fdef, 0);
                        $t=substr($t, $offset);
                        $offset=0;
                        $u->startTreat=function(){igk_wln_e("operatation not allowed");
                        };
                        $u->endTreat=function($src, $options, $totreat){igk_wln_e("operatation not allowed");
                        };
                        $u->handleChar=function(& $t, $start, & $offset, $ch, $m){
                            $u=$m->options->toread;
                            if(($ch == ")") && ($m->options->conditionDepth<=0)){
                                igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);
                                $endef=igk_treat_get($m->options);
                                igk_treat_restore_context($m->options);
                                igk_treat_restore_context($m->options);
                                $n=trim(substr($endef, 1));
                                if(!empty($n)){
                                    igk_treat_append($m->options, $n." ", 0);
                                }
                                $endef=igk_treat_get($m->options);
                                $u->mode=0;
                            }
                            else if(($ch == "(") && ($m->options->conditionDepth<=1)){
                                igk_treat_set_context($m->options, "parameterReading", $m->options->mode, null, "useParameterReading: ".$m->options->lineNumber);
                                $u->mode=1;
                            }
                            if($u->mode > 1){
                                $cancel=0;
                                igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);
                                if($cancel)
                                    return;
                            }
                            else if($u->mode == 1){
                                $u->mode=2;
                            }
                        };
                        $initContext_callback($m);
                        $m->options->toread=$u;
                        $m->options->DataLFFlag=0;
                        $offset=0;
                        return $t;
                    }
                }
                $treatmodifier=1;
                if(strpos("interface,namespace,use", $type) !== false){
                    $treatmodifier=0;
                }
                $def=$type;
                if($treatmodifier){
                    if(!empty($modifier))
                        $def=$modifier." ".$def;
                }
                $ln=& $m->options->lineNumber;
                $tn=$m->options->totalLines;
                $pos=false;
                $startTreat_Callback=function(& $def, $options, $totreat=null){
                    $name="";
                    $extends="";
                    $implements="";
                    $totreat=$totreat == null ? $options->toread: $totreat;
                    $type=$totreat->type;
                    $ctab=null;
                    $trtn="";
                    $ns=IGK_TREAT_IDENTIFIER;
                    if($type == "use"){
                        $s_ns_rg="(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?";
                        if(preg_match("/".$type."\\s+(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?/", $def, $ctab)){
                            $name=$ctab["name"];
                            $pos=strpos($def, $name);
                            $totreat->name=preg_replace("/\\s+/", "", $name);
                            $def=substr($def, 0, $pos).$totreat->name.substr($def, $pos + strlen($name));
                            if(isset($ctab["as"])){
                                $totreat->definitions["as"]=$ctab["as"];
                            }
                            $s_ns_rg=",\\s(?<data>(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?)";
                            if($c=preg_match_all("/".$s_ns_rg. "/", $def, $gtg)){
                                $indent=str_repeat($options->IndentChar, $totreat->indentLevel);
                                for($i=0; $i < $c; $i++){
                                    $rt=$gtg[0][$i];
                                    $pos=strpos($def, $rt);
                                    if($pos === false){
                                        igk_wln_e("not found ". $i. " ".$rt);
                                    }
                                    $def=substr($def, 0, $pos).substr($def, $pos + strlen($rt));
                                    $name=$gtg["name"][$i];
                                    $data=$gtg["data"][$i];
                                    $e=igk_createobj();
                                    $e->type=$type;
                                    $e->name=preg_replace("/\\s+/", "", $name);
                                    $rt=$data;
                                    $pos=strpos($rt, $name);
                                    $rt=substr($rt, 0, $pos).$e->name.substr($rt, $pos + strlen($name));
                                    $e->src=$indent.$e->type." ".rtrim($rt).";".$options->LF;
                                    if(isset($gtg["as"][$i])){
                                        $e->definitions["as"]=$gtg["as"][$i];
                                    }
                                    if($totreat->parent){
                                        $totreat->parent->definitions[$e->type][]=$e;
                                    }
                                    else
                                        $options->definitions->{$e->
                                    type}[]=$e;
                                }
                            }
                            return;
                        }
                        else{
                            igk_wln_e("global use not detected: Line: ".$options->lineNumber." t:".$def);
                        }
                        return;
                    }
                    $is_ns=($type == 'namespace');
                    if($type == "function"){
                        $trtn="(?P<reffunction>\\s*\&\\s*)?";
                    }
                    if($is_ns){
                        $ns=IGK_TREAT_NS_NAME;
                    }
                    if(preg_match("/".$type."\\s+".$trtn."(?P<name>(".$ns."))((?P<extra>(\\s+)(.)+$))?/", $def, $ctab)){
                        $name=$ctab["name"];
                        if(($totreat->type == "function") && !($totreat->parent)){
                            $idx=strpos($ctab[0], $name);
                            $name=strtolower($name);
                            ($epos=strpos($def, '(', $idx)) || ($epos=($idx + strlen($name)));
                            $def=substr($def, 0, $idx).$name.substr($def, $epos);
                        }
                        $totreat->name=$name;
                        if($is_ns){
                            $totreat->name=preg_replace("/\\s+/", "", $name);
                            $def=str_replace($name, $totreat->name, $def);
                            return;
                        }
                        if(isset($ctab["reffunction"])){
                            $totreat->options["ref"]=trim($ctab["reffunction"]) == "&";
                        }
                        if(isset($ctab["extra"]) && !empty($ext=ltrim($ctab["extra"]))){
                            $imp="";
                            $c=preg_match_all("/(?P<name>(extends|implements))\\s+(?P<data>((\\\\\\s*)?[_a-z][_0-9a-z]*)((\\s*\\\\\\s*)[_a-z][_0-9a-z]*)*(\\s*,\\s*((\\\\\\s*)?[_a-z][_0-9a-z]*)((\\s*\\\\\\s*)[_a-z][_0-9a-z]*)*)*)/i", $ext, $btab);
                            $gobj=$totreat;
                            for($i=0; $i < $c; $i++){
                                $n=$btab["name"][$i];
                                $gdt=$btab["data"][$i];
                                $data=explode(",", str_replace(" ", "", $gdt));
                                sort($data);
                                $gobj->{$n}=$data;
                                $k=implode(", ", $data);
                                $gobj->{"@".$n}=$k;
                                if(!empty($imp)){
                                    $imp .= " ";
                                }
                                $imp .= $n." ".$k." ";
                                $def=str_replace($gdt, $k.(" "), $def);
                            }
                        }
                else{
                            $def=rtrim($def);
                        }
            }
            else{
                        if($type == "function"){
                            if(empty($name)){
                                $def=trim($def);
                                $totreat->isanonymous=1;
                                return;
                            }
                        }
                igk_wln(igk_show_trace());
                igk_wln_e("definition not found : ".$type. " ?? ".$name. " for ".$def);
            }
        };
        $totreat=igk_createobj();
        $totreat->type=$type;
        $totreat->startLine=$m->options->lineNumber;
        $totreat->name="";
        $totreat->options=array();
        $totreat->definitions=array();
        $totreat->src="";
        $totreat->mode=0;
        $totreat->readPMode=0;
        $totreat->indentLevel=$m->options->offsetDepth + $m->options->bracketDepth;
        $totreat->parent=$m->options->toread;
        $totreat->documentation=$m->options->documentation ? implode("\n", $m->options->documentation->data): null;
        $totreat->attributes=$m->options->decorator ? implode("\n", $m->options->decorator->data): null;
        if(isset($m->options->chainRender)){
                    foreach($m->options->chainRender as $chain){
                        call_user_func_array($chain->bind, array($m->options, $totreat));
                    }
        }
        $m->options->documentation=null;
        $m->options->decorator=null;
        if(isset($m->options->chainRender)){
                    foreach($m->options->chainRender as $chain){
                        call_user_func_array($chain->unset, array($m->options, $totreat));
                    }
        }
        $totreat->handleChar=function(& $t, $start, & $offset, $ch, $m){
                    $totreat=$m->options->toread;
                    $cmode=& $totreat->mode;
                    if($cmode < 4){
                        switch($totreat->type){
                            case "function":
                            if($cmode == 1){
                                $cancel=0;
                                igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);
                                if($cancel)
                                    return;
                            }
                            $step=["("=>[0=>1], ")"=>[1=>2], ":"=>[2=>3], "{"=>[2=>4, 3=>4]];
                            if(isset($step[$ch])){
                                $b=$step[$ch];
                                if(isset($b[$cmode])){
                                    if(($cmode == 1) && ($ch == ")") && ($m->options->conditionDepth > 1)){
                                        igk_wln_e("failed to return: ) line:".$m->options->lineNumber." conditionDepth: ".$m->options->conditionDepth);
                                        return;
                                    }
                                    if($cmode == 3){
                                        $def=igk_treat_get($m->options).substr($t, 0, $start);
                                        $totreat->ReturnType=trim(substr($def, strrpos($def, ":", -1) + 1));
                                    }
                                    $cmode=$b[$cmode];
                                    $totreat->readingMode=$cmode;
                                    if($ch == "("){
                                        $totreat->readP=array();
                                        $op_txt=igk_treat_get($m->options);
                                        $op_lx=substr($t, 0, $start + 1);
                                        $def=ltrim(preg_replace("/\\s+/", " ", $op_txt.$op_lx));
                                        $sdef=$def;
                                        $fc=$totreat->startTreat;
                                        $fc($def, $m->options);
                                        $totreat->def=$def;
                                        igk_treat_set_context($m->options, "parameterReading", $m->options->mode, null, "function:? ".empty($def). " anonymous:".igk_getv($totreat, "isanonymous"));
                                    }
                                    else if(($ch == ")") && ($m->options->conditionDepth<=0)){
                                        igk_treat_restore_context($m->options);
                                        $txt=igk_treat_get($m->options);
                                        if(!igk_getv($totreat, "isanonymous") && !strstr($totreat->def, $bs=ltrim($txt))){
                                            $toffset=strlen($txt) - strlen($bs);
                                            $gdef=substr($txt, 0, $toffset).$totreat->def.substr($txt, strpos($txt, "(") + 1);
                                            $totreat->def=ltrim($gdef);
                                            igk_treat_set($m->options, $gdef);
                                        }
                                    }
                                }
                            }
                            break;default: 
                            if($ch == "{"){
                                $totreat->readP=array();
                                $def=igk_treat_get($m->options).substr($t, 0, $start);
                                $fc=$totreat->startTreat;
                                $fc($def, $m->options);
                                $totreat->def=$def;
                                $cmode=4;
                            }
                            break;
                        }
                if($cmode>=4){
                            $totreat->handleChar=null;
                        }
            }
        };
        $totreat->startTreat=$startTreat_Callback;
        $totreat->endTreat=function($src, $options, $totreat){igk_treat_restore_context($options);
                    $bg=$options->toread;
                    $options->endMarkerFlag=0;
                    $totreat->handleChar=null;
                    $totreat->mode=4;
                    $skip=0;
                    switch($totreat->type){
                        case 'function':
                        if(isset($totreat->isanonymous) && ($totreat->isanonymous == 1)){
                            $src=preg_replace("/function\\s+\(/", "function(", $src);
                            igk_treat_append($options, trim($src), 0);
                            if($options->conditionDepth<=0){
                                $options->definitions->lastTreat=$totreat;
                                $options->endMarkerFlag=1;
                            }
                            $options->DataLFFlag=1;
                            return;
                        }
                ///TASK: Name formatted to lower case
                if($totreat->parent){
                            $skip=($options->bracketDepth-1) > 0;
                        }
                else{
                            $skip=$options->bracketDepth > 0;
                        }
                if($options->arrayDepth > 0){
                            igk_wln("failed :::: ", "depth:".$options->arrayDepth, $options->lineNumber, $src);
                        }
                break;
                case "namespace":
                $options->toread=$totreat;
                if(strpos(rtrim($src), ";", -1) !== false){
                            $fc=$totreat->startTreat;
                            $fc($src, $options);
                        }
                break;
                case "use":
                $options->toread=$totreat;
                if(strpos(rtrim($src), ";", -1) !== false){
                            $fc=$totreat->startTreat;
                            $fc($src, $options);
                        }
                else{
                            $options->endMarkerFlag=1;
                        }
                $options->toread=$bg;
                break;
                case "class":
                break;
            }
            if($totreat->parent){
                        $skip=($options->bracketDepth-1) > 0;
                    }
            else{
                        $skip=$options->bracketDepth > 0;
                    }
            if(($totreat->type != "use") && igk_count($totreat->definitions) > 0){
                        $out=igk_treat_outdef($totreat->definitions, $options, 1);
                        if($totreat->type == "function"){
                            if(($pos=strpos(rtrim($src), "{", strlen($totreat->def))) === false){
                                igk_wln_e("not start bracket found ".$src);
                            }
                            $gs=substr($src, 0, $pos + 1);
                            $out=rtrim($out);
                            if(!empty($out))
                                $src=$gs.$options->LF.$out. substr($src, $pos + 1);
                        }
                else{
                            if(($pos=strpos(rtrim($src), "}", -1)) === false){
                                igk_wln_e("not end bracket found ".$src);
                            }
                            $gs=rtrim(substr($src, 0, $pos)).$options->LF;
                            $indent=str_repeat($options->IndentChar, $totreat->indentLevel);
                            $rf="";
                            if(($totreat->type == "namespace") && (isset($totreat->def))){
                                $rf=substr($gs, ($_tpos=strpos($gs, "{")) + 1);
                                $out=rtrim($out);
                                $gs=$indent.ltrim(substr($gs, 0, $_tpos + 1).$options->LF);
                                $totreat->globalSrc=$rf;
                            }
                            $src=$gs.$out.$rf.$indent."}".$options->LF;
                        }
            }
            if($skip){
                        igk_ewln("\e[0;41mwarning:\e[0m ".$totreat->type." [".$totreat->name."] is embeded in bracket out. Line: ".$totreat->startLine. " - ".$options->lineNumber);
                        $options->DataLFFlag=1;
                        igk_treat_append($options, ltrim($src), 0);
                        $options->DataLFFlag=1;
                        return;
                    }
            if(is_object($options->toread) && ($totreat !== $options->toread)){
                        $options->toread->definitions[$totreat->type][]=$totreat;
                    }
            else{
                        $options->definitions->{$totreat->
                        type}[]=$totreat;
            }
            $totreat->src=rtrim($src).$options->LF;
            $options->definitions->lastTreat=$totreat;
        };
        $initContext_callback($m);
        $level=$m->options->offsetDepth + $m->options->bracketDepth;
        igk_treat_append($m->options, $def." ", 1);
        $totreat->indentLevel=$level;
        $m->options->toread=$totreat;
        $t=ltrim(substr($t, $type_offset));
        $offset=0;
        return $t;
    }
        ));
    array_unshift($tab, (object)array(
            'name'=>'comment',
            'pattern'=>'#//(.)*$#i',
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                if($m->options->context == "globalConstant"){
                    $offset=$start + strlen($m->options->LF) + 2;
                    $t=$m->options->LF.$t;
                }
                else{
                    $before=trim(substr($t, 0, $start));
                    if($m->options->RemoveComment){
                        if(($pos=strpos(substr($t, $start), "?>")) !== false){
                            $t=$before.substr($t, $start + $pos);
                            $offset=strlen($before);
                            return $t;
                        }
                        $t=$before;
                        $offset=strlen($t) + 1;
                    }
                    else{
                        if(($pos=strpos(substr($t, $start), "?>")) !== false){
                            $g=substr($t, 0, $pos);
                            igk_treat_append($m->options, $g, 0);
                            $m->options->DataLFFlag=1;
                            $t=substr($t, $pos);
                            $offset=0;
                            return $t;
                        }
                        $comment=substr($t, $start);
                        $m->options->commentInfo[]=$comment;
                        $offset=strlen($t) + 1;
                        if($m->options->content != "global"){
                            igk_treat_append($m->options, $t, 0);
                            $m->options->DataLFFlag=1;
                        }
                        $offset=1;
                        $t="";
                        $offset=strlen($t) + 1;
                    }
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'globalConstant',
            'pattern'=>"#(^|\\s*)(?P<comment>//\\s*)?(?P<operator>define)\\s*\(#i",
            'mode'=>'*',
            'callback'=>function(& $t, $start, & $offset, $m){
                if($m->options->operatorFlag || (isset($m->options->command) && igk_getv($m->options->command, "noDefineHandle"))){
                    $offset=strlen($m->data["operator"][0]) + $m->data["operator"][1];
                    return $t;
                }
                $d=$m->data;
                $comment=isset($d["comment"]) ? !empty($d["comment"][0]): false;
                if((($m->options->context == 'global') && ($m->options->output != '<?php')) || $m->options->depthFlag || ($m->options->bracketDepth > 0)){
                    if($comment){
                        $t="";
                        $offset == 0;
                        return $t;
                    }
                    $m->options->DataLFFlag=1;
                    $offset=$d["operator"][1] + strlen($d["operator"][0]);
                    $g=ltrim(substr($t, 0, $offset));
                    $t=ltrim(substr($t, $offset));
                    $offset=0;
                    igk_treat_append($m->options, $g, 1);
                    return $t;
                }
                $objread=igk_createobj();
                $objread->type="define";
                $objread->startLine=$m->options->lineNumber-1;
                $objread->mode=0;
                $objread->comment=$comment;
                $objread->handleChar=function(& $t, $start, & $offset, $ch, $m){
                    $u=$m->options->toread;
                    switch($ch){
                        case ")":
                        if($m->options->conditionDepth<=0){
                            $u=$m->options->toread;
                            $u->mode=4;
                            $txt=igk_treat_get($m->options).substr($t, 0, $start + 1);
                            $u->src=$txt;
                            $offset=$start + 1;
                            $m->options->DataLFFlag=0;
                            $m->options->endMarkerFlag=1;
                            $m->options->lastTreat=$u;
                            $u->parentTreat=$m->options->lastTreat;
                            $u->handleChar=null;
                        }
                        break;
                        case ";":
                        break;
                        case ",":
                        if(!isset($u->argumentOffset)){
                            $u->argumentOffset=strlen(igk_treat_get($m->options)) + $start + 1;
                        }
                        break;
                    }
                };
                $objread->endTreat=function($s, $options, $toread){igk_wln_e("DEFINE -- END TREAT NOT IMPLEMENT", __LINE__.':'.__FILE__, $s);
                };
                if($comment){
                    $objread->newLineTreat=function(& $t, & $nextline, $options){
                        $u=$options->toread;
                        $s=ltrim($t);
                        $gx="#^//\s*#";
                        if(preg_match($gx, $s)){
                            $s=preg_replace($gx, "", $s);
                            if($u->mode == 4){
                                if(strpos($s, ";") === 0){
                                    $t=$s;
                                    return;
                                }
                                $t=";".$s;
                                return;
                            }
                            igk_wln("new line treat:", $t, "mode: ", $u->mode, $s);
                            $t=$s;
                        }
                        else{
                            if($u->mode == 4){
                                $t=";";
                                $nextline--;
                                $u->handleChar=null;
                            }
                            else{
                                $u->src="";
                                $u->handleChar=null;
                                igk_treat_restore_context($options, 1);
                            }
                        }
                    };
                }
                $offset=$start + strlen($m->data [0][0]);
                igk_treat_set_context($m->options, $m->matcher->name, 2, array(
                "DataLFFlag",
                "toread",
                "data",
                "conditionDepth",
                "lastTreat"
            ));
                $m->options->DataLFFlag=0;
                $m->options->toread=$objread;
                $m->options->data="";
                igk_treat_append($m->options, ltrim($m->data[0][0]), 1);
                $t=substr($t, $offset);
                $offset=0;
                $m->options->conditionDepth=1;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'documentation',
            'pattern'=>"#///(?P<data>[^/](.)*)$#i",
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                $options=$m->options;
                $ln=strlen($options->output);
                $t=substr($t, 0, $start);
                $offset=strlen($t);
                if(!isset($options->documentation) || ($options->documentation->start != $ln)){
                    $options->documentation=(object)array("start"=>$ln, "data"=>array());
                }
                $options->documentation->data[]=$m->data['data'][0];
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'decorator',
            'pattern'=>"#//(?P<data>@[^/](.)*)$#i",
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                $options=$m->options;
                $ln=strlen($options->output);
                $t=substr($t, 0, $start);
                $offset=strlen($t);
                if(!isset($options->decorator) || ($options->decorator->start != $ln)){
                    $options->decorator=(object)array("start"=>$ln, "data"=>array());
                }
                $before=substr($t, $start);
                if(($pos=strpos($t, "?>")) === false){
                    $txt=$m->data['data'][0];
                    $options->decorator->data[]=$txt;
                }
                else{
                    $t=$before.substr($t, $pos);
                    $offset=strlen($before);
                }
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'specialDocument',
            'pattern'=>"#///(?P<info>(TASK|TODO|REMARK|NOTE|DEBUG))\\s*:\\s*(?P<data>(.)+)$#i",
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                $m->options->DataLFFlag=1;
                igk_treat_append($m->options, trim($m->data[0][0]), 0);
                $m->options->DataLFFlag=1;
                $t="";
                $offset=0;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'startmultilineString',
            'pattern'=>"#\\s*\<\<\<(')?(?P<name>[a-zA-Z]+)(\\1)?\s*$#",
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, & $m){
                $startup=0;
                igk_treat_set_context($m->options, $m->matcher->name, 0);
                $n=$m->data["name"][0];
                $l=& $m->options->lineNumber;
                $s=substr($t, $start).$m->options->LF;
                $before=substr($t, 0, $start);
                $tL=$m->options->totalLines;
                $sL=$l;
                $c=0;
                $rgx="/^".$n."(;)?$/";
                while($l < $tL){
                    $t=rtrim($m->options->source[$l]);
                    $s .= $t;
                    if(preg_match($rgx, $t)){
                        $c=1;
                        $l++;
                        break;
                    }
                    $s .= $m->options->LF;
                    $l++;
                }
                if($c){
                    $sf=rtrim($before.$s);
                    if($sf[strlen($sf)-1] != ";"){
                        $m->options->multiLineFlag=1;
                        $m->options->mustPasLineFlag=1;
                    }
                    $m->options->DataLFFlag=0;
                    igk_treat_append($m->options, $sf, 0);
                    $t="";
                    $offset=0;
                }
                else{
                    igk_wln_e("no closed multistring found:".$sL);
                }
                igk_treat_restore_context($m->options);
                $m->options->DataLFFlag=1;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'startmultilineComment',
            'pattern'=>"#/\*#i",
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                if($m->options->context != $m->matcher->name){
                    igk_treat_set_context($m->options, $m->matcher->name);
                }
                $before=trim(substr($t, 0, $start));
                $move=0;
                if($m->options->RemoveComment){
                    $t=substr($t, $start + 2);
                    $l=& $m->options->lineNumber;
                    $pos=null;
                    while(($pos=strpos($t, "*/")) === false){
                        $move=1;
                        if($l < $m->options->totalLines){
                            $t=$m->options->source[$l];
                            $l++;
                            continue;
                        }
                        break;
                    }
                    igk_treat_restore_context($m->options);
                    if($pos !== false){
                        $g=substr($t, $pos + 2);
                        $t=$before. $g;
                        $offset=strlen($before);
                        return $t;
                    }
                    else{
                        $t="";
                        $offset=1;
                        return $t;
                    }
                }
                else{
                    $sm="";
                    while(($pos=strpos($t, "*/")) === false){
                        $move=1;
                        $l++;
                        if($l < $m->options->totalLines){
                            $sm .= rtrim($m->options->source[$l]).$m->options->LF;
                            continue;
                        }
                        break;
                    }
                    if($pos !== false){
                        $g=substr($t, $pos + 2);
                        $t=$before. $g;
                        $offset=strlen($before);
                        if($move && empty(trim($t))){
                            $l++;
                        }
                        return $t;
                    }
                    else{
                        $t="";
                        $offset=1;
                        return $t;
                    }
                    $offset=$start + 2;
                }
                igk_wln_e("comment: failed attention");
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'specialkey',
            'pattern'=>'/(^|\\s+)(?P<operand>break|continue|return|goto)(\\s*)(\\s+|;|$)/',
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, $m){
                unset($m->options->lastOperand);
                $op=$m->data["operand"];
                $tc=$m->data[0][0];
                if($op[0] == "return"){
                    if(strpos($tc, ";") === false){
                        $m->options->DataLFFlag=1;
                        igk_treat_append($m->options, "return ", 0);
                        $m->options->DataLFFlag=0;
                    }
                    else{
                        igk_treat_append($m->options, "return;", 0);
                        $m->options->DataLFFlag=1;
                    }
                    $t=ltrim(substr($t, $start + strlen($tc)));
                    $offset=0;
                    return $t;
                }
                $c=preg_replace("/\\s+/", "", $tc);
                $m->options->DataLFFlag=1;
                igk_treat_append($m->options, $c, 0);
                if(strpos($tc, ";") === false){
                    $m->options->lastOperand=$c;
                    $m->options->DataLFFlag=0;
                }
                else{
                    $m->options->DataLFFlag=1;
                    $m->options->bracketVarFlag=0;
                }
                $t=substr($t, $start + strlen($tc));
                $offset=0;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"phpHtmlPreproc",
            "mode"=>-1,
            "pattern"=>"/^(.)+$/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $m->data=array(0=>array("", 0));
                $t=igk_treat_handle_html($t, $start, $offset, $m, $c);
                $def=igk_treat_get($m->options);
                $m->options->data="";
                if($c){
                    igk_treat_set_context($m->options, "global", 0);
                }
                if(preg_match("/^#!/", $def)){
                    $m->options->startCGIOffSet=strlen($def);
                }
                $m->options->output=$def;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            "name"=>"phpPreproc",
            "mode"=>-1,
            "pattern"=>"/\\s*\<\?(php|=)?/",
            "callback"=>function(& $t, $start, & $offset, $m){
                $offset=$start + strlen($m->data[0][0]);
                if(isset($m->options->startFileFlag)){
                    $m->options->startFileFlag=1;
                }
                igk_treat_set_context($m->options, "global", 0);
                $m->options->DataLFFlag=0;
                $lf="";
                if(!empty($m->options->output))
                    $lf=$m->options->LF;
                igk_treat_append($m->options, $lf."<?php", 0);
                $m->options->DataLFFlag=1;
                $t=substr($t, $offset);
                $offset=0;
                return $t;
            }
        ));
    array_unshift($tab, (object)array(
            'name'=>'htmlContextHandle',
            'pattern'=>'/(\\s*|^)\?\>/',
            'mode'=>'*',
            'callback'=>function($t, $start, & $offset, & $m){
                igk_treat_set_context($m->options, "html", 0);
                $tc=$m->data[0][0];
                $offset=0;
                $end_rgx="/\<\?(=|php)/";
                $o="";
                $before=substr($t, 0, $start);
                $offset=$start + strlen($tc);
                $t=substr($t, $offset);
                $offset=0;
                $ln=& $m->options->lineNumber;
                $tn=$m->options->totalLines;
                $c=0;
                $s="";
                $lf="";
                $lflag=0;
                while(($ln-1) < $tn){
                    if(preg_match($end_rgx, $t, $tab, PREG_OFFSET_CAPTURE, $offset)){
                        $c=1;
                        $s .= substr($t, 0, $tab[0][1]);
                        $t=substr($t, $tab[0][1] + strlen($tab[0][0]));
                    }
                    if($c || ($ln>=$tn))
                        break;
                    $s .= $t;
                    $t=rtrim($m->options->source[$ln]);
                    $s .= $m->options->LF;
                    $ln++;
                    $offset=0;
                }
                if($c){
                    $v_n=$tab[0][0];
                    $lflag=($v_n == "<?php");
                    if($lflag)
                        $lf=$m->options->LF;
                    $o=rtrim($s).$lf."{$v_n}";
                    $offset=0;
                }
                else{
                    $o=trim($s);
                    $offset=strlen($t);
                }
                $empty_o=!empty($o);
                igk_treat_restore_context($m->options);
                $m->options->DataLFFlag=0;
                if(!empty($before)){
                    igk_treat_append($m->options, $before, 0);
                }
                $m->options->DataLFFlag=0;
                igk_treat_append($m->options, " ?>".$o, 0);
                if(!$c){
                    igk_treat_append($m->options, $t, 0);
                    $t="";
                    $offset=0;
                }
                $m->options->DataLFFlag=$lflag ? 1: 0;
                return $t;
            }
        ));
    if(file_exists($cf='armonic_php_defined_instruct.pinc'))
        include($cf);
    unset($cf);
    array_unshift($tab, (object)array(
            'name'=>'fileDescription',
            'pattern'=>'/\/\/\\s*(?P<name>([a-zA-Z ]+)):\\s*(?P<value>(.)+)$/',
            'mode'=>function($option){
                return ($option->mode == 0) && ($option->bracketDepth<=0);
            },
            'callback'=>function($t, $start, & $offset, & $m){
                $n=$m->data["name"][0];
                $v=$m->data["value"][0];
                $m->options->definitions->{"filedesc"}[]=$m->data[0][0].IGK_LF;
        $before=rtrim(substr($t, 0, $start));
        $t="";
        $offset=0;
        if(!empty($before)){
                    $t=$before;
                    $offset=strlen($t);
                }
        return $t;
    }
        ));
    if((isset($options) && $c=$options->command)){
        if(igk_getv($c, "allowDocBlocking")){
            $options->chainRender[]=(object)array(
                    "bind"=>function($options, $totreat){$totreat->docBlocking=igk_getv($options, "docBlocking");
                    },
                    "unset"=>function($options){
                        $options->docBlocking=null;
                    }
                );
            $options->documentationListener[]=function($options, $totreat, $indent=0){
                $v1=$totreat;
                $s=$v1->docBlocking;
                if(empty($s)){
                    $s="/**".$options->LF;
                    if($v1->documentation){
                        $s .= igk_treat_converttodockblocking($v1->documentation, $options);
                    }
                    else{
                        $s .= "* ".__("represent")." {$v1->name} {$v1->type}".$options->LF;
                        if(!igk_getv($options, "noAutoParameter") && isset($v1->readP)){
                            foreach($v1->readP as $kv=>$vv){
                                $s .= "* @param ";
                                if(isset($vv->type)){
                                    $s .= $vv->type." ";
                                }
                                if(isset($vv->ref) && $vv->ref){
                                    $s .= "* ";
                                }
                                $s .= "$".$vv->name;
                                if(isset($vv->default)){
                                    $s .= " the default value is ".preg_replace("#\*/#", "\*\/", $vv->default);
                                }
                                $s .= $options->LF;
                            }
                        }
                        if(($cond1=isset($v1->ReturnType)) | ($cond2=(isset($v1->options) && igk_getv($v1->options, "ref")))){
                            $s .= "* @return ";
                            if($cond1)
                                $s .= $v1->ReturnType;
                            if($cond2){
                                $s .= "*";
                            }
                            $s .= $options->LF;
                        }
                    }
                    $s .= "*/";
                }
                return igk_str_format_bind($indent."{0}".$options->LF, explode("\n", $s));
            };
            array_unshift($tab, (object)array(
                    'name'=>'docBlocking',
                    'pattern'=>'/\/\*\*/',
                    'mode'=>"*",
                    'callback'=>function($t, $start, & $offset, & $m){
                        $ln=& $m->options->lineNumber;
                        $before=substr($t, 0, $start);
                        $block_ln=substr($t, $start);
                        $boffset=0;
                        $s="";
                        $c=0;
                        while($ln<=$m->options->totalLines){
                            if(($pos=strpos($block_ln, "*/", $boffset)) !== false){
                                $s .= ltrim(substr($block_ln, 0, $pos + 2));
                                $c=1;
                                $t=$before.substr($block_ln, $pos + 2);
                                $offset=strlen($before);
                                break;
                            }
                            $s .= ltrim($block_ln).IGK_LF;
                            $block_ln=$m->options->source[$ln];
                            $ln++;
                            $t=$block_ln;
                            $boffset=0;
                        }
                        $m->options->docBlocking=$s;
                        return $t;
                    }
                ));
        }
    }
    return $tab;
}
///<summary>Represente igk_treat_start_array function</summary>
///<param name="m"></param>
///<param name="t"></param>
///<param name="start"></param>
///<param name="bracket" default="1"></param>
function igk_treat_start_array($m, $t, $start, $bracket=1){
    $m->options->arrayDepth++;
    $tbefore=substr($t, 0, $start);
    $v_o=igk_treat_get($m->options);
    $v_depth=0;
    if(($c=count($m->options->arrayEntity)) > 0){
        $v_depth=$m->options->arrayEntity[$c-1]->depth + 1;
    }
    $s_offset=strlen($v_o) + $start;
    $m->options->arrayEntity[]=(object)["start"=>$s_offset, "markOffset"=>$s_offset, "startLine"=>$m->options->lineNumber, "src"=>$bracket ? "[": "(", "before"=>$v_o, "detectLine"=>$t, "detectStart"=>$start, "beforeLine"=>substr($t, 0, $start), "items"=>array(), "isassoc"=>0, "litteral"=>!$bracket, "parent_read"=>$m->options->toread, "depth"=>$m->options->bracketDepth + $m->options->arrayDepth + ($m->options->depthFlag ? 1: 0)];
}
///<summary>Represente igk_treat_update_array_item function</summary>
///<param name="q" ref="true"></param>
///<param name="t"></param>
///<param name="start"></param>
///<param name="m"></param>
function igk_treat_update_array_item(& $q, $t, $start, $m){
    $o=igk_treat_get($m->options);
    $q_txt=$o;
    $ln=strlen($q_txt);
    $si="";
    if($t === null){
        $si=substr($q_txt, $q->markOffset, $ln - $q->markOffset + $start);
    }
    else{
        $o=$o.substr($t, 0, $start);
        $si=substr($o, $q->markOffset);
    }
    $q->markOffset=$ln;
    $q->items[]=trim($si);
}
///<summary>Represente igk_treat_update_def function</summary>
///<param name="m"></param>
///<param name="type"></param>
///<param name="r"></param>
function igk_treat_update_def($m, $type, $r){
    if(isset($m->reports)){
        if(!isset($m->reports[$type])){
            $m->reports[$type]=$r;
        }
        else{
            $m->reports[$type] += $r;
        }
    }
}
if(!defined('IGK_FRAMEWORK')){
    if(!(file_exists($libfile=dirname(__FILE__)."/../igk/igk_framework.php") || file_exists($libfile="d://wamp/www/igkdev/Lib/igk/igk_framework.php")))
        igk_die("framework not exists");
    require_once($libfile);
    igk_display_error(1);
}
define("ARMONIC_INDENT_CHAR", "    ");
define("ARMONIC_TEST", 1);
if(isset($_SERVER["ARMONIC_DATA_FILE"])){
    define("ARMONIC_DATA_FILE", $_SERVER["ARMONIC_DATA_FILE"]);
}
if(isset($_SERVER["ARMONIC_DATA_OUTPUT_FILE"])){
    define("ARMONIC_DATA_OUTPUT_FILE", $_SERVER["ARMONIC_DATA_OUTPUT_FILE"]);
}
define("IGK_APP_DIR", dirname(__FILE__));
define("IGK_BASE_DIR", dirname(__FILE__));
define("IGK_TREAT_IDENTIFIER", "[_a-zA-Z][_a-zA-Z0-9]*");
define("IGK_TREAT_NS_NAME", "((\\\\\\s*)?".IGK_TREAT_IDENTIFIER.")((\\s*\\\\\\s*)".IGK_TREAT_IDENTIFIER.")*");;;;
igk_treat_reg_command("-local", function($v, $command, $c){
    $command->{"exec"}=function($command){
        $command->inputFile=__FILE__;
        $command->outFile=igk_io_dir(isset($command->outFile) ? $command->outFile: (isset($command->outDir) ? $command->outDir."/".basename(__FILE__): dirname(__FILE__)."/".basename(__FILE__).".out.php"));
        igk_treat_filecommand($command);
    };
}
, "treat current file. and output formatted");
igk_treat_reg_command("-f,--file", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=igk_io_expand_path($v);
        if(!file_exists($v)){
            igk_wln_e("\e[0;31mdanger\e[0m file not exists");
        }
        $command->inputFile=$v;
        igk_debug_wln("input file : ".$v);
        if($command->{"exec"} == null){
            $command->{"exec"}=function($command){
                $command->outFile=igk_io_dir(isset($command->outFile) ? $command->outFile: (isset($command->outDir) ? $command->outDir."/".basename($command->inputFile): dirname(__FILE__)."/".basename($command->inputFile).".out.php"));
                igk_treat_filecommand($command);
            };
        }
    }
    $command->waitForNextEntryFlag=1;
}
, "set input file. usage -f|--file [filepath]");
igk_treat_reg_command("-o,--outDir", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $command->outDir=igk_io_expand_path($v);
        igk_debug_wln("output dir: ".$v);
    }
    $command->waitForNextEntryFlag=1;
}
, "set output directory file. usage -o|--outDir [dirpath]");
igk_treat_reg_command("-of,--outFile", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $command->outFile=igk_io_expand_path($v);
        igk_debug_wln("output file: ".$v);
    }
    $command->waitForNextEntryFlag=1;
}
, "set output file. use with -local");
if(defined("ARMONIC_TEST") && file_exists(ARMONIC_DATA_FILE)){
    igk_treat_reg_command("-data", function($v, $command, $c){
        $command->{"exec"}=function($command){
            igk_treat_bind_data($command);
        };
    }
    , "Test data.php file library");
}
if(file_exists('d:\wamp\www\igkdev\Lib\igk\igk_framework.php')){
    igk_treat_reg_command("-igklib", function($v, $command, $c){
        $command->{"exec"}=function($command){
            if(!isset($command->outDir)){
                $command->outDir="d:/temp/dist";
            };
            ini_set("max_execution_time", 0);
            $dir=igk_io_dir(IGK_LIB_DIR);
            $sdir=igk_io_dir($command->outDir);
            $ln=strlen(dirname(dirname($dir)));
            $ignore_dir=igk_treat_get_ignore_regex($command);
            foreach(igk_io_getfiles($dir, IGK_ALL_REGEX, true, $ignore_dir) as $file){
                $outfile=igk_io_dir($sdir.substr($file, $ln));
                igk_ewln("\e[0;31mfile:\e[0m".$file);
                if(preg_match("/\.(pinc|ph(p|tml))$/", $file)){
                    $command->inputFile=$file;
                    $command->outFile=$outfile;
                    igk_treat_filecommand($command);
                }
                else if(preg_match("/\.gkds$/", $file)){
                    continue;
                }
                else{
                    igk_io_w2file($outfile, file_get_contents($file));
                }
            }
        };
    }
    , "Treat all igk_framework library and generate source files to output folder");
}
igk_treat_reg_command("-d, --inputDir", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=igk_io_expand_path($v);
        $command->inDir=$v;
        if(!is_dir($v)){
            igk_wln_e("error", "Input directory ".$v." does not exists");
        }
        $command->{"exec"}=function($command){
            if(!isset($command->outDir)){
                igk_die("outDir not set");
            }
            ini_set("max_execution_time", 0);
            $dir=$command->inDir;
            $sdir=igk_io_dir($command->outDir);
            $ln=strlen($dir);
            $ifolder=null;
            $ignore_dir=igk_treat_get_ignore_regex($command);
            $v_treatfc=function($file){
                return preg_match("/\.(pinc|ph(p|tml))$/", $file);
            };
            if($command->ignoreDirs){
                $v_treatfc=function($file) use ($command){
                    $c=$command->ignoreDirs;
                    if(preg_match("/\.(pinc|ph(p|tml))$/", $file)){
                        foreach($c as $v){
                            if(strstr($file, $v)){
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                };
            }
            foreach(igk_io_getfiles($dir, "/(.)+/", true, $ignore_dir) as $file){
                $outfile=igk_io_dir($sdir.substr($file, $ln));
                igk_ewln("\e[0;31mfile:\e[0m->".$file);
                if($v_treatfc($file)){
                    $command->inputFile=$file;
                    $command->outFile=$outfile;
                    igk_treat_filecommand($command);
                }
                else if(preg_match("/\.gkds$/", $file)){
                    continue;
                }
                else{
                    igk_io_w2file($outfile, file_get_contents($file));
                }
            }
        };
    }
    $command->waitForNextEntryFlag=1;
}
, "set input directory");
igk_treat_reg_command("--gen-xmldoc", function($v, $t, $c){
    $t->genxmldoc=1;
}
, "active xml documentation generation");
igk_treat_reg_command("--gen-xmldoc-od", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=igk_io_expand_path($v);
        $command->xmlOutDir=$v;
    }
    $command->waitForNextEntryFlag=1;
}
, "set xml documentation output directory");
igk_treat_reg_command("--list", function($v, $command, $c){
    if(!isset($command->storage["list"])){
        $command->storage["list"]=1;
        $command->defListener[]=function($def, $command){ob_start();
            igk_treat_generator($def, array(
            "function"=>function($tab, & $tref, $m){
                        igk_treat_render_data($tab, "Functions:", $r);
                        igk_treat_update_def($m, 'function', $r);
                    },
            "class"=>function($tab, & $tref, $m){
                        igk_treat_render_data($tab, "Classes:", $r);
                        igk_treat_update_def($m, 'class', $r);
                    },
            "interface"=>function($tab, & $tref, $m){
                        igk_treat_render_data($tab, "Interfaces:", $r);
                    },
            "trait"=>function($tab, & $tref, $m){
                        igk_treat_render_data($tab, "Traits:", $r);
                    },
            "global"=>function($tab, & $tref, $m){
                        $r=0;
                        $o="";
                        $rtab=array();
                        foreach($tab as $k=>$v){
                                $rtab[$v->type][]=$v->src;
                            $o .= $v->src;
                            $r++;
                        }
                        if($r > 0){
                            if(count($rtab["define"])){
                                igk_wln("Constants:");
                                igk_wln(implode("", $rtab["define"]));
                            }
                        }
                    }
        ), $command);
            $s=ob_get_contents();
            ob_end_clean();
            if(!empty($s)){
                igk_wln("[".$command->inputFile."]");
                igk_wln($s);
            }
        };
    }
}
, "list all detected items types");
igk_treat_reg_command("--verbose", function($v, $t, $c){
    $t->verbose=1;
}
, "activate verbosity");
igk_treat_reg_command("--debug", function($v, $t, $c){
    $t->debug=1;
}
, "activate or not debug ");
igk_treat_reg_command("--no-output", function($v, $t, $c){
    $t->noRenderOutput=1;
}
, "disable rendering output");
igk_treat_reg_command("--no-definehandle", function($v, $t, $c){
    $t->noDefineHandle=1;
}
, "disable define - global handle ");
igk_treat_reg_command("--no-treat", function($v, $t, $c){
    $t->noTreat=1;
}
, "disable source code treatment");
igk_treat_reg_command("--no-check", function($v, $t, $c){
    $t->noCheck=1;
}
, "disable source code syntax checking");
igk_treat_reg_command("--no-git", function($v, $t, $c){
    $t->noGit=1;
}
, "ignore git folder for treatment");
igk_treat_reg_command("--no-vscode", function($v, $t, $c){
    $t->noVSCode=1;
}
, "ignore vscode folder for treatment");
igk_treat_reg_command("--single-file-per-class", function($v, $t, $c){
    $t->singleFilePerClass=1;
}
, "for armonic to generate single file per class or interface");
igk_treat_reg_command("--single-file-output", function($v, $t, $c){
    if($t->waitForNextEntryFlag){
        $t->singleFileOutput=$v;
    }
    $t->waitForNextEntryFlag=1;
}
, "directory for single file output");
igk_treat_reg_command("--ignore-pattern", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $command->ignorePattern=$v;
    }
    $command->waitForNextEntryFlag=1;
}
, "ignore vscode folder for treatment");
igk_treat_reg_command("--no-hiddenfolder", function($v, $t, $c){
    $t->noHiddenFolder=1;
}
, "ignore vscode folder for treatment");
igk_treat_reg_command("--ignore-dir", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=explode(";", igk_html_uri(igk_io_expand_path($v)));
        $command->ignoreDirs=$v;
    }
    $command->waitForNextEntryFlag=1;
}
, "list of semi-column separated directory path that must be ignored for treatment");
igk_treat_reg_command("--max-arraylength", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=intval($v);
        if($v > 0)
            $command->maxArrayLength=$v;
    }
    $command->waitForNextEntryFlag=1;
}
, "Maximum array definition for an array. [number]");
igk_treat_reg_command("--def", function($v, $command, $c){
    if($command->waitForNextEntryFlag){
        $v=igk_io_expand_path($v);
        if(file_exists($v))
            $command->descriptionHeaderFile=$v;
    }
    $command->waitForNextEntryFlag=1;
}
, "set definition header file");
igk_treat_reg_command("--forceFileHeader", function($v, $command, $c){
    $command->forceFileHeader=1;
}
, "always use file for file description. note the file: tag will be replaced with current file");
igk_treat_reg_command("--allowDocBlocking", function($v, $command, $c){
    $command->allowDocBlocking=1;
}
, "allow document php Blocking");
igk_treat_reg_command("--spaceAffectation", function($v, $command, $c){
    $command->allowSpaceAffectation=1;
}
, "allow space for '=' operator");
igk_treat_reg_command("--no-vargroup", function($v, $command, $c){
    $command->noVarGroup=1;
}
, "disable variable grouping");
igk_treat_reg_command("--multi-linevar", function($v, $command, $c){
    $command->multilineVars=1;
}
, "on variable grouping - force to write one variable per line. variable grouping is the defaultmode.");
igk_treat_reg_command("--text-lineConcatenation", function($v, $command, $c){
    $command->textMultilineConcatenation=1;
}
, "concatenate line segment in multiline.");
igk_treat_reg_command("--no-xmlDoc", function($v, $command, $c){
    $command->noXmlDoc=1;
}
, "disable auto xml documentation.");
igk_treat_reg_command("--leave-comment", function($v, $command, $c){
    $command->leaveComment=1;
}
, "do not remove comment");
igk_treat_reg_command("-utest", function($v, $command, $c){
    igk_treat_check_command_handle($command);
    $command->unitTest=1;
    $command->{"exec"}=function($command){
        igk_wln("start unit testing");
    };
    $command->commandHandle=1;
}
, "start unit testing on local data");
$tab=array_slice($_SERVER['argv'], 1);
if(count($tab) == 0){
    igk_treat_show_usage();
}
else{
    $command=igk_createobj();
    $command->command=$tab;
    $command->{"exec"}=null;
    $command->storage=array();
    $command->waitForNextEntryFlag=false;
    $gcommand=igk_treat_command();
    $action=null;
    foreach($tab as $k=>$v){
        if($command->waitForNextEntryFlag){
            $action($v, $command, []);
            $command->waitForNextEntryFlag=false;
        }
        $c=explode(":", $v);
        if(isset($gcommand[$c[0]])){
            $action=$gcommand[$c[0]];
            $action($v, $command, implode(":", array_slice($c, 1)));
        }
    }
    igk_treat_execute_command($command);
}