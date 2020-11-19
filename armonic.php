<?php
// desc: balafon-module : php formatter armonic  
// author: C.A.D BONDJE DOUE
// email: bondje.doue@igkdev.com
// version: 2.0
// release: 10/01/2019
// copyright: igkdev @ 2020
// explode class interface to single file per class or interface

if (!defined('IGK_FRAMEWORK')){
	$libfile = ""; 
	if (isset($_SERVER["IGK_LIB_DIR"])){
		$libfile = realpath($_SERVER["IGK_LIB_DIR"]."/igk_framework.php"); 
	} 
	if (!(!empty($libfile) && file_exists($libfile)) && !( 
		file_exists($libfile = dirname(__FILE__)."/../igk/igk_framework.php") 
		//||
		//file_exists($libfile = "d://wamp/www/igkdev/Lib/igk/igk_framework.php")
	)){
		die("igk_framework.php is Require doesn't exists.");
		exit(-1);
		// echo ($libfile);
		// exit;
	}
	require_once($libfile);
	igk_display_error(1);
}

define("ARMONIC_INDENT_CHAR", "    ");
define("ARMONIC_TEST", 1); 

igk_environment()->cmdColors = [
	"No"=>"\e[0m",
	"Green"=>"\e[0;32m",
	"Red"=>"\e[0;31m"
];

if (isset($_SERVER["ARMONIC_DATA_FILE"])){
	define("ARMONIC_DATA_FILE", $_SERVER["ARMONIC_DATA_FILE"]);
}
if (isset($_SERVER["ARMONIC_DATA_OUTPUT_FILE"])){	
	define("ARMONIC_DATA_OUTPUT_FILE", $_SERVER["ARMONIC_DATA_OUTPUT_FILE"]);
}


/// TASK: append decorator 
/// TASK: append php doc blocker setting 
/// TASK: array dependencie

define("IGK_APP_DIR", dirname(__FILE__));
define("IGK_BASE_DIR", dirname(__FILE__));

define("IGK_TREAT_IDENTIFIER","[_a-zA-Z][_a-zA-Z0-9]*");
define("IGK_TREAT_NS_NAME" ,"((\\\\\\s*)?".IGK_TREAT_IDENTIFIER.")((\\s*\\\\\\s*)".IGK_TREAT_IDENTIFIER.")*");

use function igk_treat_lang_res as __;

// function igk_wln(){}
// function igk_wln_e(){
// 	igk_wln(__FILE__.":".__LINE__);
// 	exit;
// };
// function igk_debug_wln(){

// }
// function igk_getv($tab, $index, $default=null){
// 	if (is_object($tab))
// 		return igk_treat_ogetv($tab, $index, $default);
// 	if (isset($tab[$index]))
// 		return $tab[$index];
// 	return $default;
// }
function igk_treat_ogetv($tab, $index, $default=null){
	if (isset($tab->$index))
		return $tab->$index;
	return $default;
}
function igk_treat_lang_res($n){
	static $res  = null;

	if ($res==null){
		$res["represent"] = "Represente";
		
	}	
	return igk_getv($res, $n, $n);
}
function igk_treat_init_array_reading($m, $start, & $t, & $cancel=0){
		$cancel = 0;
		if ($m->options->context == "parameterReading"){
		if ($m->options->toread->readPMode==0){
			//parameter type declaration 
			$cancel=1;
			$m->options->toread->paramdef="array ";
		}
		// igk_wln("param reading: ".$m->options->toread->readPMode);
		}
		if (is_object($m->options->toread) && 
		($m->options->toread->type =="function") && 
		($m->options->toread->readingMode == "3") && 
		( $m->options->context == "definitionDeclaration")
		){
			$cancel = 1;
			// igk_wln("t:".$t);
			// //	$t = substr($t, $start+ strlen($m->data["operator"][0]));
			// igk_wln("t:".$t);
			// igk_exit();
		}
		if ($cancel){
			igk_treat_append($m->options, "array ", 0);
			$t = substr($t, $start+ strlen($m->data["operator"][0]));
			$offset = 0;
			return $t;
		}  
		igk_treat_start_array($m, $t, $start, 0);// wait for "("
		// igk_wln_e("after .... ", $m->options->toread);
}

function igk_treat_bind_array($m, $offset, $start, & $t, & $cancel){
	$g = igk_treat_init_array_reading($m, $start, $t , $cancel);	
	if ($cancel){
		$t = $g;
		return $t;
	}	
	igk_treat_set_context($m->options, $m->matcher->name, 0, array("toread"));

	$m->options->depthFlag = 0;
	$indent = 0;
	$sp = "";
	$f =  substr($t, 0, $start).$sp;
	$t = substr($t, $offset);

	// igk_wln_e("binding :", $t, $f,  $offset);
	if (!empty($f))
		igk_treat_append($m->options, $f, $indent);
	$offset = 0; 
	//+ change the toread to maching context
	$m->options->toread = "array";
	return $t;
}

function igk_treat_handle_modifier($options){
 
	// $mod = $m->options->modifier;
	$mod_args = $options->modifierArgs;
	if (count($mod_args)>0){
		
		if (!is_object($options->toread)){
			igk_wln_e("not and object type:".$options->toread." line:".$options->lineNumber);
			return 0;
		}
		
		$options->toread->definitions["vars"] = array("depth"=>
		$options->bracketDepth+ $options->offsetDepth, "tab"=>$mod_args);
		return 1;
	}
	return 0;		
}
function igk_treat_handle_funcparam($ch, $t, $start, $m, & $cancel){
	$totreat = $m->options->toread;
	$cancel=1;
	$def="";
	$read_paramname = function($ch, $t, $start, $m){//read parameter name
		$totreat = $m->options->toread;		
		$tc = substr($t, 0, $start);
		if (empty($tc)){
			return;
		}
		if (!isset($totreat->paramdef)){
			$totreat->paramdef = "(";
		} 
		$totreat->paramdef .= $tc;
		
		// igk_wln("handle param name : ".$totreat->paramdef);
		
		// $def = ltrim(igk_treat_get($m->options).substr($t,0, $start));											
		$pf = $totreat->paramdef;//substr($def, strlen($totreat->def));

		if (empty($pf)){
			return;
		}
		$rgx = "/((?P<type>(".IGK_TREAT_NS_NAME."))\\s+)?(?P<refout>\\s*\&\\s*)?\\$(?P<name>(".IGK_TREAT_IDENTIFIER."))\\s*$/";
		$gtab = null;
		if (!preg_match($rgx, $pf, $gtab)){
			// igk_wln($totreat->def);
			// igk_wln($rgx);
			// igk_wln(igk_show_trace());
			// igk_wln($def);
			igk_wln_e("parameter not valid:  ".$pf. " DefToRead:".$totreat->def."| Line: ".$m->options->lineNumber);
		}
		// igk_wln("bass:".$pf. " type:".igk_getv($gtab, "type", null));
		//var_dump($gtab); 
		$totreat->readP[] = (object)array(
			"name"=>$gtab["name"],
			"type"=>igk_getv($gtab, "type", null),
			"ref" =>trim(igk_getv($gtab, "refout", ""))=="&",
			"default"=>null
		);
		
		// var_dump($totreat->readP);
		//$totreat->def = $def; 
	}; // end read name -- callback
	
	// igk_wln("::::::::::::::::::::::::::".$totreat->readPMode);
	
	// igk_wln("functionparam:".$ch.":".$totreat->readPMode.": cdepth:".$m->options->conditionDepth. " t:". substr($t,0, $start+1));
	// igk_wln("char: ".$ch. "; ".$totreat->readPMode);
	if ($totreat->readPMode==1){ // ignore [ start ] - because of not splitting $text
		if(!($ch=="]"))
		$totreat->paramdef .= substr($t,0, $start);
	}
	switch($ch){
		case ",":
			if ($m->options->conditionDepth<=1){
				// $def = igk_treat_get($m->options).substr($t,0, $start);											
				// $pf = substr($def, strlen($totreat->def)+1);
				if ($totreat->readPMode===1){ // read default value					
					$totreat->readP[count($totreat->readP)-1]->default = trim($totreat->paramdef);					
				}
				else{
					$read_paramname($ch, $t, $start, $m);					
				}
				$totreat->readPMode = 0;
				$totreat->paramdef="";
				return;				
			} 
			// update def
			break;
		case "=";
			//update definition and pass to mode 1	
			if ($m->options->conditionDepth<=1){
				$read_paramname($ch, $t, $start, $m);
				$totreat->readPMode = 1;				 
				$totreat->paramdef="";
				return;
			}
			break;
		case ")":
			if ($m->options->conditionDepth<1){
				$def = substr($t,0, $start);
				if ($totreat->readPMode===1){ // read default value												
					$pf = $totreat->paramdef ;
					$totreat->readP[count($totreat->readP)-1]->default =  trim($pf);					
					
				}else{
					$read_paramname($ch, $t, $start, $m);
				} 
				$cancel=0;
			}
			break ;
		case "]": 
			return;
		case "&":
			//reference input 
			if ($totreat->readPMode== 0){
				$v_t = trim(substr($t, 0, $start));
				if(!empty($v_t))
					$v_t.=" ";
				if (isset($totreat->paramdef))
					$totreat->paramdef .= $v_t."& ";
				else
					$totreat->paramdef = $v_t."& ";
				// igk_wln("refinput:".substr($t, 0, $start));
				// igk_wln_e("refinput:".$totreat->paramdef);
				$cancel=0;
			}
			break;
		}
	
	if (($totreat->readPMode==1)){
		if($ch==','){
			$totreat->paramdef.=", ";
		}else
			$totreat->paramdef.=substr($t,$start,1);
	}
}
function igk_treat_handle_char(& $t, $start, & $offset, $d, $m){
	if (is_object($m->options->toread) && ($fc_hchar =	$m->options->toread->handleChar)){
		return $fc_hchar($t, $start, $offset, $d, $m);
	}
	return false;
}

function igk_treat_converttodockblocking($doc, $options){
	$bs = "";
	$d = igk_createxmlnode("dummy"); //->load($doc);
	$d->load($doc);
	foreach($d->getElementsByTagName("summary") as $n){
		$bs .= "* ".implode($options->LF."*", explode("\n", $n->getContent())).$options->LF;
	}
	foreach($d->getElementsByTagName("param") as $n){
		$bs .= "* @param 88 ";
		$t = igk_getv($n, 'type', "mixed");		
		$bs.=$t." ";
		$bs .= $n["name"]." ";
		$bs .= implode($options->LF."*", explode("\n", $n->getContent())).$options->LF;
	}
	
	return $bs;
	
}

function igk_treat_modifier_getname($base_def, $t, $start, $m){
	$gt = $base_def.substr($t, 0, $start);
	//read propertie name
	$rgx = "/(?P<name>(\\$)?(".IGK_TREAT_IDENTIFIER."))\\s*$/";
	if(preg_match($rgx, $gt, $tab)){
		$name = $tab["name"];
		$m->options->modifierArgs[]=(object)array(
			"modifier"=> igk_treat_modifier($m->options->modifier), //$m->options->modifier,
			"name"=>$name,
			"value"=>null,
			"offset"=>strlen($gt) // offset where name . start can be 'for ',' or '='
		);
		// igk_wln("name: ".$name, "basedef:".$base_def, strlen($gt), $gt, strlen($base_def)+$start );
		// igk_wln("init offset:".(strlen($base_def)+$start));
	}else{
		igk_wln_e(
		__FILE__.":".__LINE__.":".__FUNCTION__, 
		"target:".$t,
		"gt:".$gt,
		"base_def:".$base_def, 
		"not name : ".$gt . " Line:".$m->options->lineNumber);
	}
}
///<summary>internal use. read modifier value on mode 2</summary>
function igk_treat_modifier_getvalue($base_def, $t, $start, $m){
	//start in t	
	$md = igk_last($m->options->modifierArgs);
 
	if($md){
		// + start here is where to stop reading
		$def = substr($t, 0,$start);		 
		// get kvalues
		$sv = (ltrim(substr($base_def, $md->offset))).$def;
		//real value
		$rv = trim(substr($sv, strpos($sv,"=")+1));

		$md->value = $rv; // trim(substr($rdef = $base_def.$def, strrpos($rdef, "=", 0)+1)); // trim($def);
// 		igk_wln_e("get value: ",$md->value, "basedef:".$base_def,
// 		"def:".$def , $md,
// 	"#####".($k = ltrim(substr($base_def, $md->offset))),
// "strlen: ".strlen($k),
// "sv=".$sv,
// "rv=".$rv);
		// , "basedef", $base_def, "redef : ".$rdef,
		// strrpos($rdef, "=", 0)
	// );

		// "t:".$t."-----------------------------",  
		// "start:".$start,
		// "mdoffset:".$md->offset,
		// "basedef : ".$base_def, 
		// "sub : ". trim(substr($base_def. substr($t, 0, $start), $md->offset)),

		// "last line offset: ". strlen(array_pop(explode("\n",$base_def))), // $md->offset
		// "outvalue : ".$md->value, " ", " " );//, $base_def, $start, $t);
	} 
	$m->options->modFlag = 1;
}
		
function igk_treat_handle_modargs(& $t, $start, & $offset, $g, $m){
	// for variable args
	//igk_wln("context: ".$m->options->context . " toread:".$m->options->toread);
	
    // igk_wln("handlemodeargs:".$g."|".$m->options->modFlag . " cdepth:".$m->options->conditionDepth. " modifier:".
	// $m->options->modifier);
	
	// igk_wln("data ::::::::::::::::::::: ".$g, $m->options->toread, 
	// $m->options->conditionDepth,
	// $m->options->modifier);
	if( ($m->options->toread != "array") && $m->options->modFlag && ($m->options->conditionDepth<=0) && !empty($m->options->modifier)){
		//+ not in array context ready definition
		switch($g){
				// case ",": // mod separator
				// handle param
				// igk_wln("mod separator:".$t);
				// break;
			case ",":
			case "=":
				// valid separator
				// igk_wln("mod: ". $g. " ".$m->options->modFlag);
				$modifier_def = igk_treat_get($m->options);
				
				if ($m->options->modFlag==2){ // for multi variable declaration
					// end read value
					// igk_wln("**************:end get value:".$t);
					igk_treat_modifier_getvalue($modifier_def, $t, $start, $m);
					return;
				}
				igk_treat_modifier_getname($modifier_def, $t, $start, $m);
				 
				if ($g == "="){							
					$m->options->modFlag = 2; // 
				} 			
				break;		
		}
	}
}


function igk_treat_render_documentation($options, $v, $indent){
	if (isset($options->documentationListener)){
		$o = "";
		foreach($options->documentationListener as $k){
			$o .= call_user_func_array($k, array($options, $v, $indent));
			
		}
		return $o;
	}
	return null;
}

///<summary>treat output: </summary>
function igk_treat_outdef($def, $options, $nofiledesc=0){
	
	$out = "";
	$tdef = array($def);
	$indent_l = -1;
	$indent = "";
	$indent_c = function($v)use(& $indent, & $indent_l, $options){
		if (isset($v->indentLevel)){			
			if ($indent_l != $v->indentLevel){			
				$idx = max(0, $v->indentLevel);
				$indent = str_repeat($options->IndentChar,  $idx);
				$indent_l = $idx;
			}
		}
	};
	$def_file = 0;
	static $tlist = null;
	if($tlist==null){
		$name_sort = function(& $tab){
			usort($tab, function($a, $b){
			return strcmp(strtolower($a->name), strtolower($b->name));
		});
		};


		$tlist = array(
			"use"=>(object)array(
				"sort"=>$name_sort, 
				"doc"=>0,
				"noXmlDoc"=>1,
				"render"=>function($v){
					 
					return $v->src;
			}),
			"global"=>(object)array(
				"doc"=>0,
				"noXmlDoc"=>1,
				"render"=>function($v){
					 return $v->src;
				}
			)		
			,"vars"=>(object)array(
				"doc"=>0,
				"noXmlDoc"=>1,
				"sort"=>function(& $tab){
					$q = $tab["tab"]; 
					usort($q, function($a, $b){
					$r = strcmp($a->modifier, $b->modifier);
					if ($r==0){
						$r = strcmp($a->name, $b->name);
					}
					return $r;
					});
					
					$tab["tab"] = $q;				
					$tab = array((object)array("tab"=>$tab, "indentLevel"=>-1));
				
				},
				"render"=>function($tab, $indent, & $tdef=null, $options=null){
			
						$indent = str_repeat($options->IndentChar, $tab->tab["depth"]);
						//$out.= IGK_LF;
						$inline_var = igk_gettsv($options, "command/noVarGroup")!=1;
						$gp = igk_gettsv($options, "command/multilineVars");
						$modifier = -1;
						$sp = "";
						$mp = '';
						$lf = "";
						if ($gp)
							$lf = $options->LF.$indent;
						$out = "";
						$tab = $tab->tab["tab"];
						
						foreach($tab as $k=>$v){ 
							// igk_wln("vargrup ", $v);
								if ($inline_var && ($v->modifier!='const' )){
									if (($modifier==-1) || ($modifier!= $v->modifier)){
										if (($modifier!==-1)&&($modifier!="const")){
											$out.=";".IGK_LF;
										}
										$modifier = $v->modifier;
										$out.= $indent.$modifier;
										if ($gp)
											$mp = str_repeat(" ", strlen($modifier));
										else 
											$mp = "";
									}else{
										$out.=",".$lf.$mp;
									}
									$out .= " ".$v->name;
									if (($v->value!==null)&&(!empty($v->value)))
										$out.= $sp."=".$sp.$v->value;
								
								}
								else{
									$out .= $indent.$v->modifier." ".$v->name;
									if ($v->value!==null)
										$out.="=".$v->value;
									$out.=";".IGK_LF;
									$modifier = $v->modifier;

							}				
					}
					// igk_wln(__FILE__.":".__LINE__, $modifier, $out);
					// var_dump($tab);
					// exit;
					if ($inline_var && ($modifier!="const")){
						$out.=";".IGK_LF;
					} 
					return $out;
				}
			
			
				// $indent = str_repeat($options->IndentChar, $q["depth"]);
				// //$out.= IGK_LF;
				// $inline_var = igk_gettsv($options, "command/noVarGroup")!=1;
				// $modifier = -1;
				// $sp = "";
				// $mp = '';
				// $gp = igk_gettsv($options, "command/multilineVars");
				// $lf = "";
				// if ($gp)
				// $lf = $options->LF.$indent;
				// foreach($tab as $k=>$v){
				// // $indent_c($v);
				// if ($inline_var){
					// if (($modifier==-1) || ($modifier!= $v->modifier)){
						// if ($modifier!==-1){
							// $out.=";".IGK_LF;
						// }
						// $modifier = $v->modifier;
						// $out.= $indent.$modifier;
						// if ($gp)
							// $mp = str_repeat(" ", strlen($modifier));
						// else 
							// $mp = "";
					// }else{
						// $out.=",".$lf.$mp;
					// }
					// $out .= " ".$v->name;
					// if ($v->value!==null)
						// $out.= $sp."=".$sp.$v->value;
				 
				// }
				// else{
					// $out .= $indent.$v->modifier." ".$v->name;
					// if ($v->value!==null)
						// $out.=" =".$v->value;
					// $out.=";".IGK_LF;

				// }
				// }
				// if ($inline_var){
				// $out.=";".IGK_LF;
				// } 
			),
			//render function type
			"function"=>(object)array("sort"=>$name_sort, 
			"autodoc"=>function($v, $indent, $options){
				static $_listname = null;
				if ($_listname == null){
					$_listname = ["__construct"=>".ctr"];
				}
				$t_name = isset($_listname[$v->name])? $v->name: $v->name;
				$t_out = $indent."///<summary>".__("represent")." ".$t_name." ".$v->type."</summary>".IGK_LF;
				// TODO DEBUGING
				// if ($v->name == "igk_agent_androidversion"){
					// igk_wln("end","igk_agent_androidversion", __LINE__, "intent length: ".strlen($indent), $options->offsetDepth);
				 
				// }
				if (!isset($v->readP)){
					igk_wln($v->src);
					igk_wln("startline: ".$v->startLine);
					igk_wln("function parameter not exists : ".$v->name);
				}
				if (!igk_getv($options, "noAutoParameter") && isset($v->readP)){
					foreach($v->readP as $kv=>$vv){
						$gs ="";
						$g = igk_createxmlnode("param");
						$g["name"]=$vv->name;
						if (isset($vv->default)){
							$g["default"] = $vv->default;
						}
						if (isset($vv->type)){
							$g["type"] = $vv->type;
						}
						if (isset($vv->ref) && $vv->ref){
							$g["ref"] = "true";
						}														
						$t_out.= $indent ."///".$g->render().IGK_LF;					 
					}				
				}
				if (($cond1=isset($v->ReturnType)) | ($cond2 = (isset($v->options) && igk_getv($v->options, "ref")))){
					$g = igk_createxmlnode("return");
					if ($cond1)
						$g["type"] = $v->ReturnType;
					if ($cond2){
						$g["refout"] = "true";
					}
					$t_out.= $indent ."///".$g->render().IGK_LF;
				}
				if (isset($v->attributes)){
						// $v->attributes
						$t_out .= igk_str_format_bind($indent."//{0}".IGK_LF, explode(IGK_LF, $v->attributes));//.IGK_LF;
				}
				return $t_out;
			},
			"doc"=>1
			),
			
			"interface"=>(object)array(
					"sort"=>function(&$tab){
						usort($tab, function($a, $b){
							$da = $a->{'@extends'} ?? "";
							$db = $b->{'@extends'} ?? "";
							if (($r = ($da <=>$db))==0)
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
				"sort"=>function (& $tab){
					$klist = array();
					$nlist = array();
					usort($tab, function($a,$b) use(& $nlist){
						if (!isset($nlist[$a->name])){
							$nlist[$a->name] = $a;
						}
						if (!isset($nlist[$b->name])){
							$nlist[$b->name] = $b;
						}
						return strcmp($a->name, $b->name);
						
					});
					$v_sroot = "/";
					foreach($tab as $k=>$v){ 
						$n =$v_sroot;
						if (isset($v->{'@extends'})){
							$p = $v->{'@extends'};
							$n = $v_sroot.trim($v->{'@extends'});
							$key =$v->name;				
							
							while($p && isset($nlist[$p])){
								$key = $p."/".$key;
								$p = igk_getv($nlist[$p],'@extends');
							}
							//igk_wln("key ::: ".$key);
							$klist[$key] = $v; 
						}else {
							$klist[$v->name] = $v;
						} 
					} 
					// igk_wln_e("");
					$cl = array_keys($klist);
					sort($cl);
					$outlist = array();
					foreach($cl as $k){
						$v = $klist[$k];
						$outlist[] = $v;											
					}	
					$tab = $outlist;
				},
				"doc"=>1,
				"render"=>function($v, $indent, & $tdef=null){					 
					return $v->src;							
				}
			),
			
			"namespace"=>(object)array(
				"sort"=>$name_sort,
				"doc"=>1,
				"render"=>function($v, $indent, & $tdef = null){							 
					if ($v->definitions && (strpos($v->src, "{")===false)){ 						 
						 array_push($tdef , $v->definitions);
					} 		
					return $v->src.IGK_LF;
				}
			)
			
		);
	}
	$gen_noxmldoc = igk_gettsv($options, "command/noXmlDoc");
	

	while($def = array_pop($tdef)){
		
			if (!$def_file && !$nofiledesc && igk_getv($options, "noFileDesc") !=1){
				// render file description
				// igk_wln_e("render file desc");
				$tab = igk_getv($def,"filedesc");
				$rpheader = $options->command && igk_getv($options->command, 'forceFileHeader', 0);
				if (!$rpheader && $tab && (count($tab)>0)){			 
					foreach($tab as $k=>$v){ 			
						$out.=  $v;
					}
					$out .= IGK_LF; 
				}else{
					 
					if($options->command){						 
						$out.= igk_treat_getfileheader($options, basename($options->command->inputFile)); 
					}
				} 
			}

			if (!$def_file){
				$tab = igk_getv($def,"FileInstruct");
				if ($tab){			 
						// 
						foreach($tab as $line){
							$out.= $line.IGK_LF;
						}
						$out.= IGK_LF;
				}
			}

			$def_file = 1;
		
		
		
		
			foreach($tlist as $k=>$v){ 
				$tab = igk_getv($def, $k);
				if (!$tab){
					continue;
				}
				//igk_wln("type: ".$k);
				if(isset($v->sort)){
					$sort = $v->sort;
					$sort($tab);
				}
				$doc = isset($v->doc) ? $v->doc : 0;
				$fc_autodoc = null;
				if (isset($v->autodoc)){
					$fc_autodoc = $v->autodoc;
				}else{
					$fc_autodoc = function($cv, $indent, $options){
						if (!isset($cv->name)){
							igk_wln($cv);
							igk_die("name of the item not setup");
						}				
					 
						return $indent."///<summary>".__("represent")." ".$cv->type. ": ".$cv->name."</summary>".IGK_LF;
					};
				}
				$fc_render =  isset($v->render)? $v->render : function($v){
					 return $v->src;
				};
				$noxml = igk_getv($v, "noXmlDoc") || $gen_noxmldoc;
				
				foreach($tab as $ck=>$cv){
					
					$indent_c($cv);
					if (!$noxml){
						if ($cv->documentation)
							$out.= igk_str_format_bind($indent."///{0|trim}".IGK_LF, explode(IGK_LF, $cv->documentation));//.IGK_LF;
						else{
							$out.= $fc_autodoc($cv, $indent, $options); 
						}
					}
					if ($doc){					
						$out.= igk_treat_render_documentation($options, $cv, $indent);
					}

					$out .= $fc_render($cv, $indent, $tdef, $options);
				} 
			}
			 
	}	
	// igk_trace();
	// igk_wln_e("the out : ", $out);
	return $out;
}
function igk_treat_reset_modifier($options){

	if ($options->context=="modifierReading"){		
		igk_treat_restore_context($options, 1);
	}
	$options->modifier = "";
	$options->modifierArgs = null;
	$options->modFlag = 0;
	$options->modOffset=-1;
}

function igk_treat_reset_flags($options){
	$options->multiLineFlag=0;
}

///<summary>set or replace current output</summary>
///<param name="options">definition options</param>
///<param name="text">new text</param>
function igk_treat_set($options, $t){
	// igk_debug_wln("settext :".$t);
	if(empty($options->data) && ($options->mode==0)){
		$g = & $options->output;
	}
	else{
		$g = & $options->data; 
	} 
	$g = $t;
}
function igk_treat_append($options, $t, $indent=1){
	//+ append text 
		igk_debug_wln("append::: CTX:".$options->context
		." : LF:".$options->DataLFFlag
		." IND:".$indent." BDepth:".$options->bracketDepth 
		." cDEPTH:".$options->conditionDepth
		." t:".$t);
 
		$tg = $options->DataLFFlag;  
		 

		$options->DataLFFlag = $options->DataLFFlag || igk_getv($options, "depthFlag");
		$indent = $indent || $options->DataLFFlag;
		 
		
	$tab ="";
	$g = 0;
	$idx = 0;
	if (empty($options->data) && ($options->mode==0)){
		$g = & $options->output;
	}
	else{
		$g = & $options->data; 
	} 
	//reset flag
	if (!empty($options->modifier) && !$options->modFlag){
		igk_treat_reset_modifier($options);
	}
	
	if(igk_getv($options, "multiLineFlag")==1){ 
		$options->multiLineFlag=0;
	}

	// if ($tg && ($tg != $options->DataLFFlag)){ 
		// igk_wln_e("changin ..... ");
	// } 

	if ($options->DataLFFlag){ 
		$g.= $options->LF;
		$options->DataLFFlag=0;
		$indent=1;
	}
	if($indent){
		$idx = $options->bracketDepth + $options->offsetDepth;
		if ( $options->depthFlag){ // for the next adding 
			$idx++;
			$srt = $options->depthFlag;
			$options->depthFlag=0;
		}
		//update for array depth level .activate by fonction start block \{
		if ($options->arrayBlockDepthFlag && ($options->arrayDepth>0)){
			$idx+= ($options->arrayDepth+1);
		}
		
		if ($idx>0){
			$tab= str_repeat($options->IndentChar, $idx);
			$t = $tab.$t;			
			
		}
	}	  
	$g .= $t;
}

function igk_treat_get($options){
	if (($options->mode!=0) || !empty($options->data)){
		return $options->data;
	}
	return $options->output;
}

function igk_treat_create_options($options=null){
	$obj = (object)array(
	// main handle properties
		"context"=>"html",
		"mode"=>-1,
		"tag"=>null,	
		"depthIndent"=>0,
		"output"=>"",
		"data"=>"",
		"mark"=>"@", //comment marker
		
		"offset"=>0,
		"DataLF"=>1,
		"DataLFFlag"=>0, // flag use for append // flow
		"depthFlag"=>0, // flag use for append // flow
		"LF"=>IGK_LF,
		//array management . in php array can be declared as [] or array(); and association array is like definition. armonic must be capable to replace
		// single line definition content with 
		"arrayEntity"=>array(), // list of array definition. (startOffset, endOffset)
		//"arrayFlag"=>0,			//use to idicate that we are on array definition
		"arrayDepth"=>0,		//encaspulate array field
		"arrayMaxLength"=>60,	// max caracter length of an array
		"arrayBlockDepthFlag"=>0, // start array block depth flag		
		
		"FormatText"=>1,
		"IndentChar"=>defined("ARMONIC_INDENT_CHAR") ? ARMONIC_INDENT_CHAR : "\t", // ident char
		"IgnoreEmptyLine"=>1,
		"RemoveComment"=>1,
		"toread"=>0,
		"bracketDepth"=>0,
		"bracketVarFlag"=>0,
		"operatorFlag"=>0,
		"mustPasLineFlag"=>0,
		"offsetDepth"=>0,
		"conditionDepth"=>0, // for ()
		"openHook"=>0, //for []
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
			if (isset($options->$k)){
				$obj->$k = $options->$k;
			}
		}
	}
	return $obj;
	
}
function igk_treat_defaultheader($options){
	static $defaultHeader = null;
	$mark = $options->mark;
	if ($defaultHeader === null){	
		$defaultHeader = "";					
		$hfile = igk_getv($options->command, "descriptionHeaderFile");	
		if (!$hfile){
			$hfile = dirname(__FILE__)."/definition.php";
		}
		if(file_exists($hfile)){
			// igk_wln_e("mark:------------------- ".$mark);
			$defaultHeader = igk_str_format_bind("// ".$mark."{0|trim}".IGK_LF , explode(IGK_LF, igk_io_read_allfile($hfile))).
			IGK_LF;
		} else {								
			$defaultHeader.="// ".$mark."author: C.A.D. BONDJE DOUE".IGK_LF;
			$defaultHeader.="// ".$mark."description: ".IGK_LF;
			$defaultHeader.="// ".$mark."copyright: igkdev © ".date('Y').IGK_LF;
			$defaultHeader.="// ".$mark."license: Microsoft MIT License. For more information read license.txt".IGK_LF;
			$defaultHeader.="// ".$mark."company: IGKDEV".IGK_LF;
			$defaultHeader.="// ".$mark."mail: bondje.doue@igkdev.com".IGK_LF;
			$defaultHeader.="// ".$mark."url: https://www.igkdev.com".IGK_LF;
		}
	}
	return $defaultHeader;
}

function igk_treat_getfileheader($options, $file){
	$defaultHeader = igk_treat_defaultheader($options);
	$s="// ".$options->mark."file: ".basename($file).IGK_LF;
	$s.= $defaultHeader.IGK_LF;
	return $s;
}

function igk_treat_skip(& $t, $start, & $offset, $m){
	$offset = $start + strlen($m->data[0][0]);
	// igk_wln_e("skipping....:");
	return $t;
}
function igk_treat_source($source,  $callback=null, $tab=null,& $options=null){
	if (is_string($source)){
		$source = explode("\n", $source);
	}
	// igk_wln($options);
	// igk_exit();
	$options = $options ?? igk_treat_create_options();
	$tab = $tab ?? igk_treat_source_expression($options);
	$out = & $options->output;
	$offset = & $options->offset;
	$sline = & $options->lineNumber;

	if ($callback==null){
		$callback = function($out, $option){
			/// MODEL : PHP Model scripting 
			$d = trim($option->data);
			if (!empty($d)){
				// there is some error(s)
				igk_wln(
				 "cDepth: ".$option->conditionDepth
				,"bDepth: ".$option->bracketDepth
				,"oDepth: ".$option->openHook 
				,"context:".$option->context);

				if (is_object($option->toread))
					igk_wln($option->toread->type.":".$option->toread->name."<|||>".$option->tag);
				else if ($option->toread){
					igk_wln($option->toread);
				}
				// igk_wln($option->debugStart);
				// constext
				while($option->context && ($option->context !="global")){
					igk_treat_restore_context($option);
					if ($option->context=="html"){
						igk_wln_e("error html");
					}
					igk_wln("+context:".$option->context ." = ".$option->tag);
				}
				igk_wln_e("some: error: data is not empty:::", $option->data, "x: ".strlen($d),
				$d, 
				"y: ".strlen($option->data));
			}else{

				if (igk_getv($option->command, "singleFilePerClass")==1){
			 
					($outdir = igk_getv($option->command,"singleFileOutput")) || 
					($outdir = igk_getv($option->command,"outDir")) ||
					($outdir = dirname($option->command->outFile));
	
					
					// igk_wln_e("info ....= ", $outdir, $option->command->outDir);
					if (!empty($outdir)){
	
					$tdef = (object)array();
					$globaloutput = array();
					foreach($option->definitions as $k=>$v){
						if ($k=="lastTreat")
							continue;
						$NS_N = "";
						//
						$defp = array((object)array("ns"=>"", "d"=>$v) );
						$gsrc = "";
						while($q = array_pop($defp)){
					 
							foreach($q->d as $def){
								switch(strtolower($def->type))
								{
									case "function":
										if (empty($q->ns)){ 
											$tdef->function[]= $def;
										}else{
											$globaloutput[$q->ns]["func"][] = $def;										
										}
										continue 2;
									break;
									case "namespace": 
									{  
										//single line namespace declaration or {}
										 
										if (isset($def->globalSrc) && !empty($gnssrc = $def->globalSrc)){
											$nsdec = "";
											if (isset($def->def)){
												$nsdec.= $def->def.";".IGK_LF;//.$src."}";
			
											}else 	
												$nsdec .= $def->src;//.$src;
	
											$globaloutput[$def->name]["nsdec"] = $nsdec;
											$globaloutput[$def->name]["gsrc"][] = $gnssrc;
											unset($nsdec, $ngssrc);
										}
										foreach($def->definitions as $nt=>$mf){
											if($nt=="use"){
												foreach($mf as $rr){
													$gsrc.= $rr->src. IGK_LF;
												}
												continue;
											}								
											array_push($defp, (object)array("ns"=>$def->name, "d"=> $mf ,"p"=>$def, "src"=> & $gsrc));
										}
		
										continue 2;
									}
									break;
									case "use": 
										if (empty($q->ns)){ 
											$tdef->use[]= $def;
										} 
										continue 2;								 
									break;
									default:
									break;
								}
							
	
								$src = $gsrc.$def->src;
								$nsdef = "";
								if(!empty($ns = $q->ns)){
									$ns.="/";
									// igk_wln_e("qrc : ", $q);//, " : ", $gsrc);
									if (isset($q->p->def)){
										$nsdef.= $q->p->def."{".IGK_LF.$src."}";
	
									}else 	
										$nsdef .= $q->p->src.$src;
								}else{
									$nsdef = $src;
								}
	
								
								// $f = $outdir."/".$ns.$def->name.".".strtolower($def->type).".php";
								$f = $outdir."/".$ns.$def->name.".php";
								// igk_wln("outto: ".$f, $src, $nsdef);
								// if (IGKIO::CreateDir(dirname($f))){
								
								igk_io_w2file($f , "<?php\n".igk_treat_getfileheader($option, $f).$nsdef);
								// }
								
							 
	
							}
						}
	 
					}
					
					
					
			
					if (count($globaloutput)>0){
						$indent = str_repeat($option->IndentChar, 1);
						foreach($globaloutput as $kk=>$tt){
							$_tout = "";
	
							$_tout .= $tt["nsdec"].IGK_LF;
	
							if (isset($tt["func"]) && ($funcs = $tt["func"])){
								//sort func
								usort($funcs, function($a, $b){
									return strcmp( $a->name, $b->name);
								}); 
								foreach($funcs as $_gfc){
									$_tout .= $_gfc->src;
								}
							}
	
							foreach($tt["gsrc"] as $_t){
								$_tout .=  $_t;
							}
							$_tout = preg_replace("#^".$indent."#im", "", $_tout);
							$f = $outdir."/".$kk."/_global.ns.php"; 
							igk_io_w2file($f, "<?php\n".igk_treat_getfileheader($option, $f).$_tout);
						}
	
				
					}
					else{ 
						$def = igk_treat_outdef($tdef, $option);
					}
				}
					// igk_wln($globaloutput);
					// igk_wln_e("single file per class ... 1", $out);
				}
				else{
				// var_dump($option->definitions); 
				$regx = "/^\<\\?(php)?(\\s*|$)/";
				$s = "";
				$lf = (empty($option->LF)? $option->LF : IGK_LF);
				$gdef =preg_match($regx, $out);
				$nodesc = igk_getv($option, 'noFileDesc'); 
				if (!$gdef && !$nodesc )
					$option->noFileDesc = 1;

				$def = igk_treat_outdef($option->definitions, $option);
				$option->noFileDesc = $nodesc;
				if ($gdef){			
					$s = "<?php";
					$out = preg_replace($regx, "", $out);
					$s .= $lf.$def.$out;
				}else{ 
					if (!$option->noFileDesc && $option->command){
						// igk_wln_e($option->startCGIOffSet);
						$g = "";
						if ($option->startCGIOffSet>0){
							$g .= substr($out, 0, $option->startCGIOffSet)."\n";
							$out = substr($out, $option->startCGIOffSet);
						}else{
							$g .= "<?php\n";
						}
						$g .= igk_treat_getfileheader($option,basename($option->command->inputFile)). " \n";
						$out = $g.$out;
					}  
					$s .= $out.$lf.$def;
				}
				return $s;
				}
			}
			return $out;
		};

	}

	//-----------------------------------------------------------------------------
	// treat source algorithm
	// when tab search index position is lower that all available use it for search
	//-----------------------------------------------------------------------------
	$tline = igk_count($source);
	$options->totalLines = $tline;
	$options->source = $source;
	$options->{"@automatcher_flag"} = array();
	 
	$flag = 0;
	$autoreset_flag = & $options->{"@automatcher_flag"};
	while($sline < $tline){
		$t = $source[$sline];
		$sline++;
		// if(!($sline % 100)){
			///TASK: PROCESS LINE
			// echo ("info ".$sline. " / ".$tline."\r");
		// }
		if ($options->IgnoreEmptyLine && (strlen(trim($t))==0)){
			continue;
		}
		if ($flag){
			if ($options->DataLFFlag  && ($options->conditionDepth<=0)){
				$options->DataLFFlag = 0;
				igk_treat_append($options, $options->LF, 0);
			}else{
				// append space separator :: if data
				if (is_object($options->toread) && ($options->toread->mode == 0)){
					$options->DataLFFlag = 0;
					igk_treat_append($options, " ", 0);
				// igk_wln("append : ::: space");
				}
			}
			
		}
		if (($hread = $options->toread) && isset($hread->newLineTreat) &&  ($n_fc = $hread->newLineTreat)){
				$n_fc($t, $sline,  $options);// fnewLineTreat
		}
		unset($hread);

		//igk_wln("Line: ".$sline);
		$flag = 1;
		$matchFlag = 0;
		$tq = array(rtrim($t)); // remove last trailing space . because we don't know the model no need to remove space before
		$offset = 0;
		$auto_reset_list=["operatorFlag", "mustPasLineFlag"];
		while($t = array_pop($tq))
		{		
			// treat every line
			// get the matcher
			$matches = null;
			$mlist = null;
			
			
			foreach($tab as $k=>$v){
				
				if (((is_callable($gf = $v->mode) && $gf($options))
					|| ($v->mode==="*") ||($v->mode === $options->mode))
					&& 
					preg_match($v->pattern, $t, $matches, PREG_OFFSET_CAPTURE, $offset)
					)
					{
						$start =  $matches[0][1];
						
						if(!$mlist || ($mlist->start > $start)){
							if (!$mlist)
								$mlist = (object)array();
							$mlist->start=$start;
							$mlist->matcher=$v;
							$mlist->data = $matches;
							$mlist->options = $options;
						}
					}
				 
			}
			if ($mlist){
				// igk_debug_wln(
						// __FILE__.":".__LINE__, "match ::: ".$v->name. " startAt: ".$start . " t: ".$t);
				
				// if ($options->operatorFlag){
					// if(isset($autoreset_flag["operatorFlag"])){
						// $options->operatorFlag = 0;
						// unset($autoreset_flag["operatorFlag"]);
					// }else 
						// $autoreset_flag["operatorFlag"] = 1;
				// }
				// if ($options->mustPasLineFlag){
					// if(isset($autoreset_flag["mustPasLineFlag"])){
						// $options->mustPasLineFlag = 0;
						// unset($autoreset_flag["mustPasLineFlag"]);
					// }else 
						// $autoreset_flag["mustPasLineFlag"] = 1;
				// }
				foreach($auto_reset_list as $re){
					if ($options->$re){
						if(isset($autoreset_flag[$re])){
							$options->$re = 0;
							unset($autoreset_flag[$re]);
						}else 
							$autoreset_flag[$re] = 1;
					}
				}
				
				if ($options->endMarkerFlag && isset($options->definitions->lastTreat)){
					if(isset($autoreset_flag["endMarkerFlag"])){
						$options->endMarkerFlag = 0;
						unset($autoreset_flag["endMarkerFlag"]);
					}else 
						$autoreset_flag["endMarkerFlag"] = 1;
				} 
				// igk_debug_wln(__FILE__.":".__LINE__, "matcher: ".$mlist->matcher->name);
				$fc = $mlist->matcher->callback;
				$t = $fc($t, $mlist->start, $offset, $mlist);
				
				if (!empty($t)){
					array_push($tq, $t);
					continue;
				}
			}
			break;
		}
		$s = trim($t);
		if ((strlen($s)==0) && $options->IgnoreEmptyLine){
			$flag = 0;
		}else{
			igk_treat_append($options, ltrim($t), 0);			
		}
		 
	}	
	unset($options->{"@automatcher_flag"});
	
	if ($callback){
		return $callback($out, $options);
	}
	return $out;
	
}

function igk_treat_handle_operator_flag($m, $type, $t, $start, & $offset=null){
		
		if ($opFlag = $m->options->operatorFlag){
			// igk_wln("operator flag:".$opFlag);//.":: type:".$type."| match:".preg_match("/(=|=\>|,|\?\?)/", $opFlag));
			if ( ! (($type=="function") && preg_match("/(=|=\>|,|\?\?|:)/", $opFlag))
				|| (preg_match("/(::|-\>)/", $opFlag))
			)
			 // probably affectation of anonymous function
			{
				igk_wln("\e[0;41mwarning\e[0m ignore reserved operator use as instance member:\n type=".$type.
				" text:".$t.
				" Line:".$m->options->lineNumber.
				" opFlag:[{$opFlag}] ? ".preg_match("/(::|-\>)/", $opFlag));
				$offset = $start+ strlen($m->data[0][0]);
				return $t;
			}
		}
	}
	
		
//+ start array update array items
function igk_treat_update_array_item(& $q, $t, $start, $m){
	// igk_wln("********************************updateArray");
			$o = igk_treat_get($m->options);
			$q_txt = $o;
			$ln = strlen($q_txt);
			$si = "";
			if ($t === null){
				// igk_wln("lkd");
				$si = substr($q_txt, $q->markOffset, $ln-$q->markOffset +$start);
			}else{
				$o = $o.substr($t, 0, $start);
				$si= substr($o, $q->markOffset);//.substr($t, 0, $start);
			}
			// igk_wln("o: ".$o);
			// igk_wln("si:::".$si. " :::: ".count($q->items) . " offset:".$q->markOffset);
			$q->markOffset = $ln;// $next;
			$q->items[] = trim($si);
		};
		//+ start array reading
		function igk_treat_start_array($m, $t, $start, $bracket=1){
			// igk_wln("Start Read Array : ".$m->options->lineNumber. " : ".$m->options->arrayDepth);
			// if($m->options->lineNumber == 43643){
				// igk_wln("with bracket : ".$bracket);
				// igk_wln("Brack position :... ", substr($t,  $m->data[0][1], 10));
				// igk_wln("context : ", $m->options->context);
				// igk_trace();
				// igk_wln_e($t);
			// }
			$m->options->arrayDepth++; 
			
			$tbefore = substr($t,0, $start); 
			 
			
			$v_o = igk_treat_get($m->options);
			$v_depth = 0;
			if (($c = count($m->options->arrayEntity))>0){
				$v_depth = $m->options->arrayEntity[$c-1]->depth + 1;
			} 
			//start offset 
			$s_offset = strlen($v_o)+$start;
			$m->options->arrayEntity[] = (object)[
				"start" => $s_offset ,
				"markOffset"=>$s_offset,
				"startLine"=>$m->options->lineNumber,
				"src"=>$bracket ? "[" : "(",
				"before"=>$v_o, // .substr($t,0, $start),
				"detectLine"=>$t,
				"detectStart"=>$start,
				"beforeLine"=>substr($t, 0, $start),
				"items"=>array(),
				"isassoc"=>0,
				"litteral"=>!$bracket, 
				"parent_read"=>$m->options->toread,
				"depth"=>$m->options->bracketDepth + 
						$m->options->arrayDepth + ($m->options->depthFlag? 1 : 0) 
				];
		};
		//+ end reading array
		function igk_treat_end_array($m, & $t, & $start,& $offset){
			// igk_wln("********************************EndArray");
			static $maxArray = null;
			if ($maxArray==null)
				$maxArray = igk_gettsv($m->options, "command/maxArrayLength", $m->options->arrayMaxLength);
			else{
				$maxArray = $m->options->arrayMaxLength;
			}
			
			
			$m->options->arrayDepth--;
			 

			$q = array_pop($m->options->arrayEntity);
			if ($q){ 
			
			
			$v_o = igk_treat_get($m->options);
			$q_txt = $v_o.trim(substr($t, 0,$start));
			igk_treat_update_array_item($q, $t , $start, $m);		
			$lvg = strlen($q_txt)-strlen($q->before);		
			
			
			// igk_wln("context: ".$m->options->context, $q->items);
			
			// igk_wln("***********************************");
			// igk_wln("array_defLength:".$lvg);
			// igk_wln("array_definition:".substr($q_txt, -$lvg).$d);
			// igk_wln("array_items:".count($q->items));
			// igk_wln("***********************************".$maxArray);
			if ($lvg > $maxArray){
				//replace
				// igk_wln_e("replace");
				if (($v_cc = count($q->items))> 1)
				{
					
					$tq=array_merge($q->items);//, array("]"));
					$inchar = $m->options->IndentChar;
					$indents = str_repeat($inchar, $q->depth);					 
					$indentd = $indents.$inchar;
					if (!$q->litteral){
						$outtxt = "";
						if ($v_cc >1){	
							$outtxt .= ltrim($indentd.implode(",\n".$indentd, $tq)."\n".$indents);
						// var_dump($q);
						}else{
							// igk_wln("::::::/".$tq[0]);
							if(($pos = strpos($tq[0], "[")) !==false){
								$v_o= rtrim($v_o);
								$outtxt = substr($indents, strlen($inchar)-1).ltrim($q->beforeLine.$outtxt.substr($tq[0], $pos+1));
							}
							else
								$outtxt.=trim($tq[0]);
							$m->options->DataLFFlag=0; //"because of single data
						}
						 
						
					}else{
						$tq[0]= substr($tq[0], strpos($tq[0], "(")+1);	
						$outtxt = "array(";
						if (($v_cc >1) || (!empty(trim($tq[0])))){					
							$outtxt .= "\n".$indentd.implode(",\n".$indentd, $tq)."\n".$indents;
						}					
					}
					 $new_o = substr($v_o, 0, $q->start).$outtxt;
					// igk_wln_e("outtere:=== ".$t);
					if($start>0)				
					{
						$t=ltrim(substr($t, $start));
						$start = 0;
						$offset = 1;
						// igk_wln("bbb:".$offset);
						// igk_wln(__LINE__.":t: ".$t);
					}
					//$start = -strlen($m->data[0][0])+1; 
					igk_treat_set($m->options, $new_o);
				}
			}
			}
			else{
				igk_wln("no items");
			}
		};
///<summary>represent php treat expression</summary>
function igk_treat_source_expression($options=null){
	$tab = array(); 
	
	static $defExpression = null;
	if ($defExpression==null){
		$defExpression = 1;
	
	}
	
	


	array_unshift($tab, (object)array(
		"name"=>"switchCaseOperatorHandle",
		"mode"=>"*",
		"pattern"=>"/(^|\\s+)(?P<operator>(case|default))(\\s+|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			// igk_wln_e("not on switch case Declaration", $m->options->mode);
			$idx = $m->data["operator"];
			$op_n = $idx[0];
			$space = " ";
			// igk_wln("handle:", $idx);
			if (igk_treat_handle_operator_flag($m, $op_n, $t, $start, $offset)){
				// igk_wln_e("handle opera:".$op_n);
				return $t;
			}
			if ($op_n=="default"){
				$space="";
			}
			switch($op_n){
				case "case": //wait
				case "default": //waiting for ":"
					$m->options->switchcaseFlag = 1;
					break;
			}
			
			igk_treat_append($m->options, $op_n.$space, 1);
			$t = substr($t, $start+strlen($m->data[0][0]));
			$offset = 0;
			return $t;		
	}));		
	array_unshift($tab, (object)array(
		"name"=>"controlConditionalHandle",
		"mode"=>"*",
		"pattern"=>"/(^|[^a-zA-Z0-9_ \$@]|\\s+|)(?P<operator>(for|if|elseif|while|do|switch|foreach|try|finaly|catch|array))\\s*(\(|\{|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$idx = $m->data["operator"];
			// if ($idx[0]=="array"){
				// igk_wln(":::::::::::::::::::::::::".$t, $m->data[0]);
				// igk_wln($offset);
				// igk_wln($start);				
			// }
			// if (strpos($m->data[0][0], '$')===0){
				// $offset = $start + strlen($m->data[0][0]);
				// return $t;
			// }
			$h = trim(substr($t, $offset, $start-$offset));
			if(($start!=$offset) && !empty(trim(substr($t, $offset, $start-$offset)))){
				// igk_wln("continu..................");
				$offset = $idx[1] + strlen($idx[0]);
				return $t;
			}
			
			// igk_wln("########".$h."####################".$m->data[0][0]);
			$offset = $idx[1]+strlen($idx[0]);
			$indent = 1;
			$sp = " ";
			if (preg_match("/(for(each)?|while|catch|switch|if|elseif|array)/",$idx[0])){
				$sp="";
			}
			$m->bracketVarFlag = 1;
			switch($idx[0])
			{
				case "do":
					$m->bracketVarFlag = 0;
					$m->options->doMarkerFlags = 1;
					$m->options->DataLFFlag = 1; 
					igk_treat_append($m->options, "do", 1);
					$m->options->DataLFFlag = 0;
					$t = substr($t,$offset);
					$offset= 0;
					// igk_wln_e("do ");
					return $t;
				
				case "array":
					// igk_wln("restore:". $m->options->context." modflag:".$m->options->modFlag);
					$g = igk_treat_init_array_reading($m, $start, $t , $cancel);	
					if ($cancel){
						return $g;
					}			
					break;
			}
			
			igk_treat_set_context($m->options, $m->matcher->name, 0, array("toread"));
			
			switch($idx[0])
			{
				case "array":	 
						
					$m->options->depthFlag = 0;
					$indent = 0;
					$sp = "";
				break;
				default:
				// while in  do while
				if (($idx[0] =="while") && isset($m->options->doMarkerFlags)){
					// igk_wln_e("start while for do");
					$m->options->doMarkerFlags = 0;
					unset($m->options->doMarkerFlags);			
					$m->options->endDoWhileMarkerFlag = 1;
					$indent = 0;
					$m->options->DataLFFlag = 0;
					$idx[0] = " ".$idx[0];
					// igk_wln_e("baba:::>");
				}else{
					$m->options->DataLFFlag=1;
				}
				if ($m->options->depthFlag){
					$m->options->DataLFFlag=0;
					$indent = 0;
					$m->options->depthFlag = 0;
					$idx[0]=" ".$idx[0];
					// igk_wln_e("FLAGIN:::::::::::::::::::::::::::::::::::::::");
					// igk_wln_e("e::: ".substr($t, $offset));
				}
				break;
			}

			
			igk_treat_append($m->options, substr($t, 0, $start).$idx[0].$sp, $indent);
			$t = substr($t, $offset);
			$offset = 0;
			//+ change the toread to maching context
			$m->options->toread = $idx[0];
			return $t;
		}
	));
	
	
	
	
	array_unshift($tab, (object)array(
		"name"=>"depthFlagHandle",
		"mode"=>"*",
		"pattern"=>"/(^|\\s+)(?P<operator>(else))(\\s*|[^a-zA-Z0-9]|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			// igk_wln_e("handle ....");
			$d = $m->data[0][0];
			$s = preg_replace("/\\s*/", "", $d);
			$m->options->DataLFFlag = 1;
			igk_treat_append($m->options, $s, 1);
			$t = substr($t, $start+strlen($d));
			$offset = 0; 
			$m->options->depthFlag = 1;
			return $t;
		}
	));
	
		array_unshift($tab, (object)array(
		"name"=>"operatorHandle",
		"mode"=>"*",
		"pattern"=>"/\\s*(?P<operator>(((=|\!)==|::|\>\>|\<\<|\+\+|\-\-|&&|\<=\>|\<=|\>=|\<\>|(=|-)\>(\{)?|(\|\|)|\?\?)|\<|\>|([\-\+\/\*%\<\>\=\.\|\&\!\^])?=|[\.&,:\+\-\*%\|\?\!]))\\s*/",
		"callback"=>function(& $t, $start, & $offset, $m){
			//reset flag for static operator
			igk_treat_reset_operatorflag($m);
			$offset = $start+strlen($m->data[0][0]);
			$ln = strlen($m->data[0][0]);
			$g = preg_replace("/\\s+/", "", $m->data[0][0]);
			$ch = $g;
			$gx = empty(ltrim(substr($t, 0, $start)));
	
			
			if (strlen($ch)==1){
				igk_treat_handle_char($t, $start, $offset, $g, $m);
			}
			igk_treat_handle_modargs($t, $start, $offset, $g, $m);
			
			$v = trim(substr($t, 0, $start));
			//remove all duplication text
			$m->options->operatorFlag = $g;
			
			if ($m->options->FormatText){	
				$v_indent = 0;
				$h = 0;			
				switch($g){
				
					case "&":
						//igk_debug_wln("&:".$t . " start:".$start);
						if ((($m->options->context == "parameterReading")
						&& 	($m->options->toread) && 
						isset($m->options->toread->readP) && ($start >0)) || (
							 ($m->options->context=='global') && !$m->options->mode)
						){
							$g =" ".$g." ";
							break;
							
						}
						$g = "& ";
						// igk_wln_e("not collapse", $m->options->context, $m->options->mode, $m->options->toread->readP, $start);
						break;
					
					case "=>":
					if (isset($m->options->arrayDepth) && ($m->options->arrayDepth>0)){
						 	$q = $m->options->arrayEntity[count($m->options->arrayEntity)-1];
							$q->isassoc = 1;
						}
					break;
					case ",":
						$g .= " ";
						//on array mean special definition 
						if (($m->options->toread=="array") && isset($m->options->arrayDepth) && ($m->options->arrayDepth>0)){
							$q = $m->options->arrayEntity[count($m->options->arrayEntity)-1];
							$h = 1;
							$m->options->DataLFFlag=0;
							igk_treat_append($m->options, $v.$g, 0);
							igk_treat_update_array_item($q, null, -strlen($g), $m);
							 
						}
					break; 
				
					case ":":
						$m->options->DataLFFlag=0;	
						if ($m->options->switchcaseFlag){
							$m->options->switchcaseFlag = 0;
							$m->options->operatorFlag = 0;						
							igk_treat_append($m->options, $v.$g, 0);
							$m->options->DataLFFlag=1; 
							$h = 1;							
						} 
						// else {
						 
						$g = $g." ";
						break;
					case "=": 
						// affectation must be inlined	
						// $m->options->java["allowSpaceAffectation"] = "999";
						if (($cmd = $m->options->command) && 
							isset($cmd->allowSpaceAffectation) && 
							$cmd->allowSpaceAffectation){
							$g = " ".$g." ";						
						}
					 
						if (
							($m->options->bracketVarFlag) || 
							((!$m->options->conditionDepth) && !empty(igk_treat_get($m->options)))
						)
						{
							
							if (!$m->options->conditionDepth && !$m->options->objectPointerFlag){
							// force passing to line
								$m->options->DataLFFlag = 1;
								igk_treat_append($m->options, "", 1);
							}
							$m->options->DataLFFlag = 0;
							$m->options->bracketVarFlag = 0;
						}
						break;
					case "->{": //litteral bracket support
						$m->options->bracketVarFlag = 1;
						if ($m->options->DataLFFlag){
							igk_treat_append($m->options, $v.$g, 1);
							$h= 1;
						}						
						$m->options->DataLFFlag = 0;
						$m->options->bracketDepth++;
						$m->options->objectPointerFlag = 1;
						break;
					case "->":
						$m->options->bracketVarFlag = 0;
						$m->options->objectPointerFlag = 1;
						break;
					case "::": 
							//class marquer  
							$m->options->staticMarkerOperator = 1;
							$m->options->objectPointerFlag = 1;
							$m->options->bracketVarFlag = 0;
						break;
					case "<=":
					case ">=":
					case "++":
					case "--":					
					case "!":
					case "~":						
						break;
					case '.':
						// igk_wln("operator *******************************:".$g);
						if (preg_match("/(\\s+)/", $m->data[0][0]))
							$g.=" ";//.$m->data[0][0]."|";
						if (igk_gettsv($m->options, "command/textMultilineConcatenation")){
							$v_indent = 0;
							$h = 1;
							igk_treat_append($m->options, $v.$g, $v_indent);
							$m->options->DataLFFlag = 1;
						}						
						break;
					case '-':
						if ($start < strlen($t)-1){
							if (!preg_match("/^(\.)?[0-9]/", substr($t, $start+1))){
								$g = " ".$g." ";
							}
						}
					break;
					default:
						$g = " ".$g." ";
						break;
				}
		
				if (!$h){
					 igk_treat_append($m->options, $v.$g, $v_indent);
				}					
				$t = substr($t, $start+$ln);
				$offset = 0;
			}
			return $t;
		}
	));
	
array_unshift($tab, (object)array(
		"name"=>"specialOperator",
		"mode"=>"*",
		"pattern"=>"/(\\s+|^)(?P<operator>(OR|AND|XOR|as))(\\s+|$)/i",
		"callback"=>function(& $t, $start, & $offset, $m){
			// igk_wln("detect:************** ".$m->data[0][0]);
			// special operator detection
			$offset = $start+strlen($m->data[0][0]);
			$ln = strlen($m->data[0][0]);
			$g = preg_replace("/\\s+/", "", $m->data[0][0]);
			$ch = $g;
			$gx = empty(ltrim(substr($t, 0, $start)));
			// igk_wln("what: ".$start);
			
			if (strlen($ch)==1){
				igk_treat_handle_char($t, $start, $offset, $g, $m);
			}
			igk_treat_handle_modargs($t, $start, $offset, $g, $m);
			
			$v = trim(substr($t, 0, $start));
			$RP=array("OR"=>"||", "AND"=>"&&", "XOR"=>"|");
			
			//remove all duplication text
			$m->options->operatorFlag = $g;
			if ($m->options->FormatText){	
				$h = 0;			
				$g = " ".igk_getv($RP, strtoupper($g), $g)." ";
	
				if (!$h)
				igk_treat_append($m->options, $v.$g, 0);
				
				$t = substr($t, $start+$ln);
				$offset = 0;
			}
			return $t;
		})
	);
	
	
	array_unshift($tab, (object)array(
		"name"=>"bracketHandle",
		"mode"=>'*',
		"pattern"=>"/\\s*(\{|\})/",
		"callback"=>function(& $t, $start, & $offset, $m){
			if ($m->options->depthFlag){
				$m->options->depthFlag=0;
			}
			
			$sd = $m->data[0][0];
			$d= trim($sd);			
			$noffset = $start + strlen($sd);
			// igk_wln("bracket :::: ".$d);
			
			//handle char
			igk_treat_handle_char($t, $start, $offset, $d, $m);	
			if ($d=="}"){
				// for {"var"} = "continue inline
				// for {} breacking line
				// for { ... {} .. } must be covered
				$m->options->bracketDepth--;
				$_indent = 1;
				if ($m->options->bracketVarFlag){
					$m->options->bracketVarFlag = 0; //reset
					if (empty($m->options->data))
						$_indent = 0;
					$m->options->DataLFFlag = 0;
				}else {
					$m->options->DataLFFlag = 1;
				}
				if ($m->options->FormatText){
					// igk_wln("format text", $lf, $m->options->bracketVarFlag, $m->options->DataLFFlag);
					// $lf = 0;
					$g  = trim(substr($t, 0, $start));						
					if (!empty($g)){
						igk_treat_append($m->options, $g."}", $_indent);	
						$lf = 0;						
					}else{
						igk_treat_append($m->options, "}", $_indent); 
					}
				}else{
					igk_treat_append($m->options, "}",0);
				}
				// igk_wln_e("bracket depth ", $m->options->bracketDepth, $lf, "g", $g);
				// igk_wln("append in " , $lf, $m->options->FormatText, $g);
				$t = substr($t, $noffset);
				$offset = 0;
				if ($m->options->bracketDepth<=0){
					if (!empty($d = $m->options->data)){ // get data
						$m->options->data = "";
						if (is_object($m->options->toread)){
							$fc = $m->options->toread->endTreat;							
							// igk_wln("endtreatb:".$d);
							$fc($d, $m->options, $m->options->toread);
						}
					}
					
				}
				if ( ($m->options->bracketDepth) || ($m->options->context == "global")){
					$lf = 1; // next data must be in line
				}
				$m->options->DataLFFlag = $lf;
				
				if ($m->options->arrayDepth>0){
					$q = $m->options->arrayEntity[count($m->options->arrayEntity)- 1];
					if (!isset($q->arrayBlockDepthFlag))
						$q->arrayBlockDepthFlag=1;						
					$q->arrayBlockDepthFlag--;
					if ($q->arrayBlockDepthFlag<=0)
						$m->options->arrayBlockDepthFlag = 0;
				}
				
			}else{
				if ($m->options->context == "controlConditionalHandle"){
					igk_treat_restore_context($m->options, 1);
				}
			
					// $m->options->bracketVarFlag = 1;
						// if ($m->options->DataLFFlag){
							// igk_treat_append($m->options, $v.$g, 1);
							// $h= 1;
						// }						
						// $m->options->DataLFFlag = 0;
						// $m->options->bracketDepth++;
						// break;
					// case "->":
				$ln_segment = 1;
				$m->options->bracketVarFlag = 1;
				if ($m->options->objectPointerFlag){
					// igk_wln_e("object pointer .....");
					$ln_segment = 0;
					$m->options->bracketVarFlag = 1;
					$m->options->objectPointerFlag=0;
				}
			
				$f = trim(substr($t, 0, $start).$d); //.$m->options->LF;
				$bck = $m->options->DataLFFlag;
				$m->options->DataLFFlag = 0;
				igk_treat_append($m->options, $f, 0);
				$m->options->DataLFFlag = $bck;
				if ($m->options->FormatText){
					$m->options->DataLFFlag = $ln_segment; // wait for data line file
				}
				$t = ltrim(substr($t, $noffset));
				$offset = 0;
				$m->options->bracketDepth++;
				if ($m->options->arrayDepth>0){
					$q = $m->options->arrayEntity[count($m->options->arrayEntity)- 1];
					if (!isset($q->arrayBlockDepthFlag))
						$q->arrayBlockDepthFlag=0;						
					$q->arrayBlockDepthFlag++;
					$m->options->arrayBlockDepthFlag = 1;
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
			
			if ($m->options->context!='html'){
				$ln = strlen($m->data[0][0]);
				if ($start==0){
					$t = substr($t, $ln);
				}else 
					$t = igk_str_insert(" ", $t, $start, $start+strlen($m->data[0][0]));
				
				$offset= $start;
			}else{
				$offset = $start + strlen($m->data[0][0]);
			}
			return $t;
	}));
	array_unshift($tab, (object)array(
		"name"=>"hookDefinition",
		"mode"=>"*",
		"pattern"=>"/\\s*(\[\\s*|\])/",		
		"callback"=>function(& $t, $start, & $offset, $m){
			$d = trim($m->data[0][0]);
			// igk_wln("---------------------------------".$d);
			if (!isset($m->options->openHook))
				$m->options->openHook = 0;
			
			if (!isset($m->options->arrayDepth)){
				$m->options->arrayDepth = 0;
			}
			if ($d=="["){
				$m->options->conditionDepth++;
				$m->options->openHook++;	
				// $cancel = 0;
				//++
				// igk_treat_bind_array($m, $offset ,  $start, $t, $cancel);
				// igk_wln_e("detect somme ".$m->options->conditionDepth, $m->options->toread, $cancel); 
				igk_treat_start_array($m, $t, $start);
			}else{
				// igk_wln(__LINE__.":".__FILE__, "-\nDecrease condition depth : \n".$m->options->conditionDepth);
				$m->options->conditionDepth--;
				$m->options->openHook--;
				igk_treat_end_array($m, $t, $start, $offset); // end bracket array
 
				// 	$depthf=0; 
				// //	igk_treat_restore_context($m->options, 1);
				// 	$soutput = igk_treat_get($m->options);
				// igk_wln("before : ".$m->option->toread);	

				// igk_treat_restore_context($m->options, 1);
				// $s = trim(substr($t, 0, $start+1));
				// igk_treat_append($m->options, $s, 0);
				// $t = ltrim(substr($t, $offset));
				// $offset = 0; 	 		
				// igk_wln_e(" content : ". igk_treat_get($m->options), $s);
				// ->context,
				// $m->option->toread,  $d, $t, $s);
			}
			igk_treat_handle_char($t, $start, $offset, $d, $m);			
			$offset = $start+strlen($m->data[0][0]);
			return $t;
		})
	);
	
	
	
	array_unshift($tab, (object)array(
		"name"=>"controlCondition",
		"mode"=>"*",
		"pattern"=>"/\\s*(\(\\s*|\))/",
		"callback"=>function(& $t, $start, & $offset, $m){
			// igk_wln("controlCond ".$m->options->conditionDepth);
			// 
			$st = $m->data[0][0];
			$d = trim($st);
			$ln = strlen( $m->data[0][0] );
			if ($m->options->FormatText &&( $ln>1)){
				//contain space
				$t = igk_str_insert($d, $t, $start, $start+strlen($m->data[0][0]));
				$offset = $start+1;
			}else{
				$offset = $start + $ln;
			}
			
			if ($d == ")" ){
				// igk_wln(__LINE__.":".__FILE__, "\n-Decrease condition depth : ".$m->options->conditionDepth);
				$m->options->conditionDepth--;				
				if ($m->options->DataLFFlag){
					if ($m->options->mustPasLineFlag){ 
						$m->options->DataLFFlag = 1;
					}else
						$m->options->DataLFFlag = 0;
				}
				
			}else{
				$m->options->conditionDepth++; 
			}
			// igk_wln("after:: ".$m->options->conditionDepth);
			if($m->options->conditionDepth<0){
				igk_wln_e("something wrong....condition depth equal to :".$m->options->conditionDepth. " line:".$m->options->lineNumber,
				$d,
				$m->options->context);
			}
			// igk_wln($m->options->lineNumber.":{$d}:****". $m->options->conditionDepth);
			// igk_wln("handle char: ".$d. " context:".
			// $m->options->context."-------".$m->options->toread."------------");
			// -------------------------------------------
			igk_treat_handle_char($t, $start, $offset, $d, $m);
			
			// if ($d==")"){
			// }
			switch($m->options->context){				
				case "controlConditionalHandle":
					$depthf = 1;

					// igk_wln_e("bas ; ".$m->options->toread);

					if ($m->options->toread=="array"){
						$q = $m->options->arrayEntity[count($m->options->arrayEntity)-1]; // $m->options->
						 // igk_wln("finish array litteral ? ".$q->litteral, $d, "hookDeepth:".$q->hookDepth);
						$update_depth_litteral = 0;
						if ($q->litteral){ //the entity and element to read match 
							
								if ($d==")"){
									if (!isset($q->hookDepth)){
										igk_wln_e("ttt: hookDepth not define".$t);
									}
									else{
										$q->hookDepth--;
										
										if($q->hookDepth<=0){	
											igk_treat_end_array($m, $t, $start, $offset); 
											$depthf=0; 
										//	igk_treat_restore_context($m->options, 1);
											$soutput = igk_treat_get($m->options);
											igk_treat_restore_context($m->options, 1);
											$s = trim(substr($t, 0, $start+1));
											igk_treat_append($m->options, $s, 0);
											$t = ltrim(substr($t, $offset));
											$offset = 0; 	
											// if ($m->options->context == "modifierReading"){
												// igk_wln(">>>>>>>>", implode(", ",$q->items).$s);
												// $soutput .= $s;
												// $t= $soutput.$t;
												// $offset = strlen($soutput);
											// }
											//igk_wln("finish array reading", $s, $m->options->context, $t);
											return $t;
										}
									}
									// igk_wln("toread:::".$m->options->toread);
									// igk_wln_e("data:".$d." ".count($m->options->arrayEntity));
								} else {
									//detect sub start bracket						
									if (!isset($q->hookDepth))
										$q->hookDepth = 1;
									else 
										$q->hookDepth++;
								}
								$update_depth_litteral = 1;
							$s = trim(substr($t, 0, $start+1));
							igk_treat_append($m->options, $s, 0);
							$offset = 0;
							$t = substr($t, $start+1);
							return $t;
						}
					}
					if ($m->options->conditionDepth==0){
						// 
						igk_treat_restore_context($m->options, 1);
						$s = substr($t, 0, $start+1);		
						//$m->options->DataLFFlag = 0;
						//$m->options->depthFlag = 0;						
						igk_treat_append($m->options, $s, 0);
						$t = ltrim(substr($t, $offset));
						$offset = 0; 
						$m->options->depthFlag=$depthf;
						// igk_wln("finish codi:::::".$m->options->objectPointerFlag);
						if ($m->options->objectPointerFlag){//reset object pointer
							$m->options->objectPointerFlag = 0;
						}
					}
					
					
				break;
				case "globalConstant":
					if ($m->options->conditionDepth==0){
						//finish global constant
						$s = ltrim(substr($t, 0, $start+1));					
						// igk_wln("before:".$t . " start:".$start." ln:".$ln ." offset:".$offset);
						$t = ltrim(substr($t, $offset));
						// igk_wln("after:".$t . " start:".$start." ln:".$ln ." offset:".$offset);
						$offset = 0;
						igk_treat_append($m->options, $s, 0); 		
					}
					break;
				case "parameterReading": // read parameter 
				default:
					$s = ltrim(substr($t, 0, $start+1));
					if ( ($d==")") && ($m->options->conditionDepth<=0)){
						if(igk_getv($m->options, "multiLineFlag")==1){
							$s = $m->options->LF.$s;
							$m->options->multiLineFlag=0;
						}				
					} else if ( ($d=="(") && ($m->options->conditionDepth<=1)){
						
						$s = ltrim(substr($t, 0, $start+1));
						if(igk_getv($m->options, "multiLineFlag")==1){
							$s = $m->options->LF.$s;
							$m->options->multiLineFlag=0;
						}
						 
					}
					//better update operation location					
					igk_treat_append($m->options, $s, 0);
					$t = ltrim(substr($t, $offset));
					$offset = 0;					
					break;
			}   
			// igk_wln("t:".$t, "offset:".$offset, $offset==19 ? $t[$offset]: "-");
			return $t;
		}
	));
	array_unshift($tab, (object)array(
		"name"=>"endInstruction",
		"mode"=>"*",
		"pattern"=>"/\\s*(;)\\s*/",
		"callback"=>function(& $t, $start, & $offset, $m){
			igk_treat_reset_operatorflag($m);
			$d = $m->data[0][0];
			if (isset($m->options->lastOperand)){
				$cc = ltrim(substr($t, 0, $start));
				if (!empty($cc)){
					$cc = " ".$cc.$d;			
				}
				$m->options->DataLFFlag = 0;
				igk_treat_append($m->options, $cc, 0);
				$m->options->DataLFFlag = 1;
				// igk_treat_append($m->options, $cc, 0);
				// igk_wln_e("last operand ",$cc, $m->options->lastOperand, "data : ", $d, $t);
				unset($m->options->lastOperand);
				$t = ltrim(substr($t, $start+ strlen($d)));					
				$offset = 0; 
				$m->options->bracketVarFlag = 0;
				return $t;
			}
			$cbk = $m->options->DataLFFlag;		
			$s = preg_replace("/\\s*/", "", $d);
			
			$ob_pointer = $m->options->objectPointerFlag;
			
			
			if ($m->options->objectPointerFlag){//reset object pointer
				$m->options->objectPointerFlag = 0;
			}
			if ($m->options->DataLFFlag){
				if (trim($t)==";"){
					$m->options->DataLFFlag = 0;
				} 
			}
			
				// igk_wln("endinstruct:",
			// $t,
			// "mode:".$m->options->mode,
			// "context:".$m->options->context,
			// // $m->options->toread,
			// "toreadmode:".$m->options->toread->mode
			
		 // );
		 //disable bracket flag instruction 
		 if ($m->options->bracketVarFlag)
		 	$m->options->bracketVarFlag = 0;
			
		 // igk_wln_e( __FILE__.":".__LINE__, $m->options->bracketVarFlag ,  "finisht >>>>>>>>>>>>>>>>>>>>");
			if (($toread = $m->options->toread) && is_object($toread) && ($toread->mode<4) && 
			($fc = $toread->endTreat)){
				$s = rtrim(igk_treat_get($m->options).substr($t, 0, $start).$s).$m->options->LF;
				
				$fc($s, $m->options, $toread);
				$t = substr($t, $start+ strlen($d));
				$offset = 0;
			
					
				return $t;
			}
			// igk_debug_wln(":::: - >" . $m->options->context);
			switch($v_context = $m->options->context){
				case "controlConditionalHandle":
				 
					$offset = $start+1;
					// $t = substr($t, 0, $start).$s.substr($t, $start+ strlen($d));
					$indent = 0;
					if (igk_getv($m->options, "endDoWhileMarkerFlag")==1){
						//$indent = 0;
						$m->options->endDoWhileMarkerFlag = 0;
					}
					igk_treat_append($m->options, substr($t, 0, $start).$s." ", $indent); 
					$t = substr($t, $start+ strlen($d));
					$offset = 0; 
					return $t;
					break;
				case "globalConstant": 
					
					$m->options->DataLFFlag=0;
					igk_treat_append($m->options, trim(substr($t, 0, $start)).$s, 0);
					// $t = substr($t, 0, $start).$s.substr($t, $start+ strlen($d));
					$totreat = $m->options->toread;
					if ($totreat==null){
						igk_die("totreat is null");
					}
					$deff =  igk_treat_get($m->options).$m->options->LF;
					igk_treat_restore_context($m->options, 1); 
					if (!empty($deff)){
						$objd = (object)array(
						"src"=>$deff,
						"line"=>$totreat->startLine,
						"type"=>$totreat->type
						);
						if (is_object($m->options->toread)){ 
							$m->options->toread->definitions["global"][] =  $objd;
						}else
							$m->options->definitions->{"global"}[] = $objd;
					}
					// igk_wln_e("read: ", $totreat, $totreat);
					$t = substr($t, $start+ strlen($d));
					if ($totreat->comment){
						$t=""; // remove last items
					}
					// igk_wln_e("finish global constant:".$deff, $t, $m->options->output);
					$offset = 0;					
					// igk_wln_e("finish const:", $m->options->toread);
					break;
				// case "globalCommentConstant":
					// igk_wln_e("endEntruction for GlobalCommentContant");
					// break;
				default:
				
				$gg = trim(substr($t, 0, $start)).$s;					
				$h = 0;
				$def = igk_treat_get($m->options);
				// igk_wln("modifier ...............".$def, $m->options->modifier);
					if (!empty($m->options->modifier)){					
						
						// igk_wln("modflag:".$m->options->modFlag);
						// igk_wln_e("context:".$m->options->context);
						if ($m->options->modFlag==2){
							// end read 
							igk_treat_modifier_getvalue($def, $t, $start, $m);
						}else if($m->options->modFlag==1){
							//
							igk_treat_modifier_getname($def, $t, $start, $m);
						}
						
						// igk_wln_e("base:::finish");
						// var_dump($m->options->modifierArgs);
						if (is_object($cop = $m->options->toread) && igk_treat_handle_modifier($m->options))
						{  
							$h =1;
						}
						// igk_wln($m->options->context);
						igk_treat_reset_modifier($m->options);
						
						// igk_wln($cop);
					//	igk_wln_e("output : ".$def.$gg);//, $h, $m->options->totreat, $t);
					 
						
					}
					if (!$h){
						
						if ($m->options->endMarkerFlag && isset($m->options->definitions->lastTreat)){
							$m->options->endMarkerFlag = 0;
							$ls = $m->options->definitions->lastTreat;
							if (!isset($ls->endMarker) && preg_match("/(use|function|define)/", $ls->type)){
								//+ anonymous function type must send ; to output
								if (($ls->type == "function") && (igk_getv($ls, "isanonymous")==1)){
									$cc= trim($gg);
									if ($cc !==";"){
										igk_wln_e("not valid end marker:".$cc. " ".$m->options->lineNumber);
									}				
									
									$m->options->DataLFFlag = 0;	
									igk_treat_append($m->options, $cc, 0);// trim($gg));									
									$m->options->DataLFFlag = $cbk;
									//$ls->endMarker = 1;									
								}else{		 
									$ls->src = rtrim($ls->src).$ls->type.":".trim($gg).$m->options->LF;
								}
								$h=1;								
								$ls->endMarker = 1;			
							}
							// igk_wln_e("last treat:".$gg);							
						}
						if (!$h){
						
							$indent = 0;
							 if (igk_getv($m->options, "endDoWhileMarkerFlag")==1){
								//$indent = 0;
								$m->options->endDoWhileMarkerFlag = 0;
								// $m->options->DataLFFlag=0;
								$m->options->depthFlag = 0;
							}
							// $m->options->DataLFFlag = 0;
							// igk_wln("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<", $m->options->DataLFFlag);
							igk_treat_append($m->options, trim($gg), $indent);
						    // $m->options->DataLFFlag = 1;	
						}
						
						
					}

					$t = ltrim(substr($t, $start+ strlen($d)));					
					$offset = 0;
					// igk_debug_wln("????????????t:".$t." : ".$offset. " ".$m->options->DataLFFlag, $gg);
				break;
			}

			$m->options->DataLFFlag = 1;			
			$m->options->bracketVarFlag = 0; 
			// igk_wln("-------------------------------------");
			return $t;
		}

	));	 

	array_unshift($tab, (object)array(
		"name"=>"uncollapsestring",
		"mode"=>'*', //available mode
		"pattern"=>"/(\"|')/i",
		"callback"=>function(& $t, $start, & $offset, $m){
			//skip reading 
			// reading is only on one line - so is necessairy to read data until the 
			$lis = $start;
			$ch = $t[$start];
			$s = "";
			// igk_wln("start line:", $t);
			// igk_debug_wln("**********************************");
			// $multilinestart = ($ch == "'");
			$line = & $m->options->lineNumber;
			$tline = $m->options->totalLines;
			$ts = "";
			$offset = $start;
			$s = 0;			
			while($line <= $tline){
				$ts .= igk_str_read_brank($t, $offset, $ch, $ch,null, 1, 0);
				// igk_wln("force : ".$ts, $offset);
				if ($t[$offset]==$ch){
					$offset++; 
					$s = 1;			
					break;
				}
				else {
					
					// igk_wln("next line : {$line} {$tline} first: {$ts} ", $m->options->source[$line]);
					igk_treat_append($m->options, $t.$m->options->LF, 0);
					$ts.= "\n";
					$t = $m->options->source[$line];
					$line++;
					$offset = 0;
				}
			}
			if (!$s){
				$ts .= $ch;
				$t.= $ch;
			}
			// $t = $s.$t;
			// igk_wln_e("ts = ".$ts);
			
		// //	if ($ch=="'"){
				// //special case for multi line
				// $ln = & $m->options->lineNumber;
				// $tln = $m->options->totalLines;
				// $before = substr($t,0,  $start);
				// $x = substr($t, $start+1);
				// $start = 0;
				// $escaped = 0;
				// // igk_wln("x:".$x, $ch,   $start,
				// // $ch,
				// // strpos($x, $ch, $start));
				// while( (($pos = strpos($x, $ch, $start)) === false) && ($ln < $tln)
				// || ( $escaped= (($pos>0) && $x[$pos-1]=='\\'))	
				// ){
					// if ($escaped){
						// if($pos>1){
							// if ($x[$pos-2]=="\\"){
								// break;
							// }
						// }
						
						// $start = $pos+1;
						// // igk_wln("escaped ....".$x);
						// $escaped = 0;
						// continue;
					// }
					// $s .= substr($x, $start).$m->options->LF;
					// $x = $m->options->source[$ln];
					// $ln++;
					// $start = 0;
					// $escaped = 0; 
				// }
				// if ($pos!==false){
					// // var_dump($pos);
					// $t = substr($x, $pos+1); 
					// $offset = 0;
					// $s .= substr($x, 0,$pos);
					// $s = $before.$ch.$s.$ch;
					// $offset = strlen($s);
					// $t = $s.$t;
					// igk_wln("skip: ", $s);
				// }else{
					// igk_wln_e("something wrong ... string litteral", $t);
				// } 
				// return $t;
				
				
			// // // } else { 			
				// // $s = igk_str_read_brank($t, $lis, $ch, $ch, null, 1);			
			// // // }
			// $offset = $lis+1;
			// $m->options->reading = $s; 
			// // igk_wln("read : ", $s);
			
			if($m->options->context!='globalConstant'){
				$m->options->stringReading[] = (object)array("data"=>$ts, "line"=>$m->options->lineNumber);
			}		
			return $t;
		}
	)); 


array_unshift($tab, (object)array(
		"name"=>"modifierDeclaration",
		"mode"=>"*",
		"pattern"=>"/(^|\\s+)(?P<modifier>((private|public|protected|final|abstract|const|static|global|var)(\\s+|$))+)/",
		"callback"=>function(& $t, $start, & $offset, $m){
		$skip = 0;
		if (isset($m->options->inlineFunctionReadingFlag)){
				if ($m->options->inlineFunctionReadingFlag <= $m->options->bracketDepth)
				{
					// skip
					$skip = 1;
				}
				else
					unset($m->options->inlineFunctionReadingFlag);
		}
		$modifier = $m->data["modifier"][0];
		if((trim($modifier) == "const"))
		{
			if (igk_treat_handle_use($m, 'const')){
				igk_treat_reset_modifier($m->options);
				$t = ltrim(substr($t, $m->data["modifier"][1]+5));
				$offset = 0;
				// igk_wln_e("handle mode: ".ltrim($t);
				return $t;
			}
			// igk_wln_e("read const ");
			
			if(($m->options->mode == 0)|| (!$m->options->toread))
			{
				$skip = 1;
				igk_wln("skippingmode: ".$m->options->mode);
			}
		}
		if ($skip){
			return igk_treat_skip($t, $start, $offset, $m);
		}
		// igk_wln($modifier."|");
			// igk_wln_e("mode : ".$m->options->mode);
		
		
		$offset = $start + strlen($m->data[0][0]);
		$ln = & $m->options->lineNumber; //next line
		$tn = $m->options->totalLines;
		if ($offset>=strlen($t)){ // reach end of the line. so try to read next modifier
			$toffset = 0;
			while($ln<$tn){
				// get next line
				$st = trim($m->options->source[$ln]);
				$ln++;
				$offset = 0;
				if (empty(trim($st))){
					continue;
				}
				$t = $st;
				if (preg_match($m->matcher->pattern, $st, $ctab, PREG_OFFSET_CAPTURE, $offset)){
					if ($ctab[0][1]!=0){
						break;
					}else{						
						$modifier .= " ".trim(preg_replace("/\\s+/", " ", $ctab["modifier"][0]));
						
						$offset = $ctab[0][1]+strlen($ctab[0][0]);
						$t = ltrim(substr($t, $offset));
						$offset = 0;
						if (empty($t)){							 
							continue;
						}						 
						break;
					}
				}else{
					break;
				}	 
			}	
		}
		if ($offset>0){// detected on the first line. move cursor
			$t = substr($t, $offset);
			$offset = 0;
		}
		$o = null;
		if (is_object($m->options->toread))
			$o = igk_getv($m->options->toread->definitions,"vars");
		
		igk_treat_set_context($m->options, "modifierReading", $m->options->mode, array("data", "DataLFFlag", "bracketVarFlag"));
		$mod = trim($modifier); 
		$m->options->data = $mod." ";
		$m->options->DataLFFlag=0;
		$m->options->modifierArgs = $o ? $o["tab"] : array();
		$m->options->modifier = $mod; 
		$m->options->modFlag = 1;
		$m->options->modOffset = strlen(rtrim(igk_treat_get($m->options)))+$start; // backup the start modifier offset. remove the last empty space
		return $t;
			
}));
		
array_unshift($tab, (object)array(
		"name"=>"definitionDeclaration",
		"mode"=>"*",
		"pattern"=>"/(^|\\s+)(?P<type>(interface|class|trait|function|namespace|use))(\\s+|\\(|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$type = $m->data["type"][0];
			$type_offset = $m->data["type"][1] + strlen($m->data["type"][0]);
			$tab = array();
			$sub = substr($t, $offset, $start-$offset);
			$modifier = igk_treat_modifier($m->options->modifier);
			$totreat = $m->options->toread;
			
			// igk_wln( __LINE__.":".__FILE__, 
				// "start reading . type ... ".$type, $m->options->lineNumber);
			if (isset($m->options->staticMarkerOperator)){ 
				 igk_treat_reset_operatorflag($m);
				if ($type == "class"){
					 $offset = $start + strlen($m->data[0][0]); 
					return $t; 

				}
			} 
			
			/// reset modifier declaration			
			igk_treat_reset_modifier($m->options);
			if(igk_treat_handle_use($m, "function")){
				$t = ltrim(substr($t, $type_offset));
				 	
				$offset = 0; 
				return $t;
			}
			
			
			// igk_treat_reset_modifier($m->options);
			// backup states
			$initContext_callback=function($m){
				igk_treat_set_context($m->options, 
				$m->matcher->name, 
				$m->options->mode,
				["toread", "mode", "data", 
				"bracketDepth", 
				"conditionDepth", 
				"offsetDepth", 
				"DataLFFlag"]);				
				
				$level = $m->options->offsetDepth + $m->options->bracketDepth;
				$m->options->offsetDepth = $level;// $m->options->bracketDepth;
				$m->options->bracketDepth = 0;
				$m->options->mode = 1;
				$m->options->data = "";
				$m->options->DataLFFlag=0;
				$m->options->conditionDepth = 0;
				$m->options->openHook = 0;
			};
			
			//reserver word on oparation handle
			if (igk_treat_handle_operator_flag($m, $type, $t, $start, $offset)){
				//igk_wln_e("exisjjj");
				return $t;
			}
			// if ($opFlag = $m->options->operatorFlag){
				// // igk_wln("operator flag:".$opFlag);//.":: type:".$type."| match:".preg_match("/(=|=\>|,|\?\?)/", $opFlag));
				// if ( ! (($type=="function") && preg_match("/(=|=\>|,|\?\?|:)/", $opFlag))
					// || (preg_match("/(::|-\>)/", $opFlag))
				// )
				 // // probably affectation of anonymous function
				// {
					// igk_wln("\e[0;41mwarning\e[0m ignore pointer: type:".$type.
					// " t:".$t.
					// " Line:".$m->options->lineNumber.
					// " opFlag:[{$opFlag}]".preg_match("/(::|-\>)/", $opFlag));
					// $offset = $start+ strlen($m->data[0][0]);
					// return $t;
				// }
			// }
			// start reading
		
			if ($type=="use"){
				igk_treat_reset_modifier($m->options);
				
				 // igk_wln("detect use ...");
				if (($treat=$m->options->toread) && ($treat->type=="function") && isset($treat->isanonymous) && ($treat->isanonymous)){
					$offset = $m->data["type"][1]+strlen( $type);//$m->data[0][0]);
					$treat->usingDefition=1;
					
					$u = igk_createobj();
					$u->type="useparameter";
					$u->definitions = array();
					$u->src = "";
					$u->mode = 0;// 0
					$u->readPMode = 0;
					$u->startLine = $m->options->lineNumber;
					$u->parent = $treat;
					$u->def = "(";
					$fdef= " ".$type." (";
					igk_treat_append($m->options, substr($t, 0, $start).$fdef, 0);
					$t = substr($t, $offset);
					$offset =0;// $start+strlen($u->def);
					
					
					$u->startTreat =function(){
						igk_wln_e("[use] operation not allowed");		
					};
					$u->endTreat =function($src, $options, $totreat){
						igk_wln_e("[use] operation not allowed");						
					};
					$u->handleChar= function(& $t, $start, & $offset, $ch, $m){
						$u = $m->options->toread;
						// igk_wln("handle char use:******************".$ch);
						if (($ch==")") && ($m->options->conditionDepth<=0)){
							
							igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);
							
							$endef = igk_treat_get($m->options);//.substr($t,0 , $start);
							//igk_wln("use:".$ch." finish parameter reading:".$endef); 
							igk_treat_restore_context($m->options);// exit use parameter readingmode to definition
							igk_treat_restore_context($m->options);// finish use declaration
							
							$n = trim(substr($endef, 1));
							if (!empty($n)){ // passing added data to global context
								//igk_wln("set:::: ".$n);
								igk_treat_append($m->options, $n." ", 0);
							}
							
							$endef = igk_treat_get($m->options);//.$endef;
							$u->mode = 0;	
							
						} else if (($ch=="(" ) && ($m->options->conditionDepth<=1)){							
							igk_treat_set_context($m->options, 
							"parameterReading", 
							$m->options->mode, //maintain mode
							null,
							"useParameterReading: ".$m->options->lineNumber);
							$u->mode=1;
							//start mode
						}
						if ($u->mode>1){							
							$cancel = 0;
							igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);								
							if ($cancel)
								return;
						}else if ($u->mode==1){
							$u->mode=2;
						}
					};
					$initContext_callback($m);
					$m->options->toread=$u;
					$m->options->DataLFFlag=0;	 
					$offset = 0;							
					return $t;
				} 				
			
			}
			
			// igk_wln("detect: ".$type. " offset:".$offset. " t:".$t);
			
			// $names = "private|public|protected|final|abstract|const|static|global|var";
			// $regmodifier = "/(^|\\s+)(?P<modifier>((".$names.")(\\s+|$))+)/";
			// $endmodifier = "/(^|\\s+)(?P<modifier>((".$names.")(\\s+|$))+)$/";
			
			
			
			
		 
			// treat modifier
			$treatmodifier = 1;
			if (strpos("interface,namespace,use", $type)!==false){
				$treatmodifier = 0;
			}
			$def = $type;
			if ($treatmodifier){
				if (!empty($modifier))
				$def = $modifier." ".$def;				
			}
			
						
			$ln = & $m->options->lineNumber;
			$tn = $m->options->totalLines;		
			$pos = false;
			
			
			//because some can introduce comment or specal definitions
			//continue reading in algorithm with DataLF = 0
			

			$startTreat_Callback = function(& $def, $options, $totreat =null){
				//retreive real info of the definition
				// igk_wln("starttreat:".$def);
				// igk_wln(igk_show_trace());
				$name="";
				$extends = "";
				$implements = "";
				$totreat = $totreat ==null ? $options->toread : $totreat;
				$type = $totreat->type;
				$ctab = null;
				//format definition
				$trtn = "";
				$ns = IGK_TREAT_IDENTIFIER;
				
				if ($type=="use"){
					// detected by {"
					$s_ns_rg = "(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?";
					if (preg_match("/".$type."(\\s+function)?\\s+(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?/", $def, $ctab)){
						$name = $ctab["name"];
								// igk_wln("match ns:------------------".$name);
						$pos = strpos($def, $name);
						$totreat->name = preg_replace("/\\s+/", "", $name);
						$def = substr($def, 0, $pos)."".$totreat->name."".substr($def, $pos+strlen($name)); 
						
						
						
						if (isset($ctab["as"])){
							$totreat->definitions["as"] = $ctab["as"];
						}
						
						$s_ns_rg = ",\\s(?<data>(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?)";
						if ($c = preg_match_all("/".$s_ns_rg ."/", $def, $gtg)){
							$indent = str_repeat($options->IndentChar, $totreat->indentLevel);
					
							for($i = 0; $i < $c; $i++){
								$rt = $gtg[0][$i];
								$pos = strpos($def, $rt);
								if ($pos===false){
									igk_wln_e("not found ". $i. " ".
									$rt);
								}
								// igk_wln("bp: ".$def);
								$def = substr($def, 0, $pos).substr($def, $pos+strlen($rt)); //str_replace($rt, "", $def); // remove extra from definition, an create new use item, add it to definition
								// igk_wln("rp: ".$def);
								
								$name = $gtg["name"][$i];
								$data = $gtg["data"][$i];
								$e = igk_createobj();
								$e->type = $type;
								$e->name = preg_replace("/\\s+/", "", $name);
								$rt= $data;
								$pos = strpos($rt, $name); 
								$rt = substr($rt, 0, $pos).$e->name.substr($rt, $pos+strlen($name)); 
						 
								$e->src = $indent.$e->type." ".rtrim($rt).";".$options->LF;
								if (isset($gtg["as"][$i])){
									$e->definitions["as"] = trim($gtg["as"][$i]);
								}
								
								// igk_wln("parent:");
								// igk_wln($options->toread->type);
								// igk_wln($options->toread->parent);
								// igk_wln_e("");
								if ($totreat->parent ){
									$totreat->parent->definitions[$e->type][] = $e;
								}
								else
									$options->definitions->{$e->type}[] = $e;								
							
							}
						}
						return;
					}else{
						igk_wln_e("global use not detected: Line: ".$options->lineNumber." t:".$def);
					}
					return;
				}
				
				
				$is_ns=  ($type=='namespace');
				if ($type=="function"){
					$trtn = "(?P<reffunction>\\s*\&\\s*)?";
				}
				if($is_ns){
					$ns = IGK_TREAT_NS_NAME;
				}
				if (preg_match("/".$type."\\s+".$trtn."(?P<name>(".$ns."))((?P<extra>(\\s+)(.)+$))?/", $def, $ctab)){
					// igk_wln($ctab);
					$name = $ctab["name"];
					if (($totreat->type=="function") && !($totreat->parent)){
						// +: igk_wln_e("global name");
						// +: 
						$idx = strpos($ctab[0], $name);
						
						$name = strtolower($name);
						//check to remove space in front of (
						($epos = strpos($def, '(', $idx)) || ($epos = ($idx+strlen($name))) ;
						
						$def = substr($def, 0, $idx).$name.substr($def, $epos);//+strlen($name));					
					}
					$totreat->name = $name;
				
						
					if ($is_ns){
							$totreat->name = preg_replace("/\\s+/", "", $name);
							$def = str_replace($name, $totreat->name , $def);							 
							return;
					}
					if (isset($ctab["reffunction"])){
						$totreat->options["ref"] = trim($ctab["reffunction"])=="&"; 
					}
					
					
					if ( isset($ctab["extra"]) && !empty($ext=ltrim($ctab["extra"]))){
						$imp = "";
						$c = preg_match_all(
						"/(?P<name>(extends|implements))\\s+(?P<data>((\\\\\\s*)?[_a-z][_0-9a-z]*)((\\s*\\\\\\s*)[_a-z][_0-9a-z]*)*(\\s*,\\s*((\\\\\\s*)?[_a-z][_0-9a-z]*)((\\s*\\\\\\s*)[_a-z][_0-9a-z]*)*)*)/i",
						//((\\\\\\s*)?[_a-z][_0-9a-z]*)((\\s*\\\\\\s*)[_a-z][_0-9a-z]*)*((\\s*,\\s*(\\\\)?[_a-z][_0-9a-z]*)((\\\\)[_a-z][_0-9a-z]*)?)+)?))/i",
						$ext, 
						$btab);
						$gobj = $totreat;
						
						for($i =0; $i<$c;$i++){
							$n = $btab["name"][$i]; 
							$gdt = $btab["data"][$i];
							$data = explode(",", str_replace(" ", "", $gdt));							
							sort($data);  
							
							$gobj->{$n} = $data;
							$k = implode(", ", $data);
							$gobj->{"@".$n} = $k; 
							if (!empty($imp)){
								$imp.=" ";
								 
							}
							$imp .= $n." ".$k." ";
							//$pos = min($pos, strpos($g, $btab[0][$i]));
							// igk_wln("k is ".$imp);
							
							$def = str_replace($gdt, $k.(" "), $def);
						}
					}else{
						$def = rtrim($def);
						// igk_wln_e("base:".$def."|");
					}
				}else{
				if ($type=="function"){
					// probably isanonymous function
					if (empty($name)){ // is anonymous
						$def = trim($def);
						$totreat->isanonymous = 1;					
						return;
					}
				}
				igk_wln(igk_show_trace());
				igk_wln_e("definition not found : ".$type. " ?? ".$name ." for ".$def);
			}			 
			};
			
		
			// declartion object		
			$totreat = igk_createobj();				
			$totreat->type = $type;
			$totreat->startLine = $m->options->lineNumber;
			$totreat->name = "";
			$totreat->options=array();			
			// $totreat->definition = null;
			$totreat->definitions = array();
			$totreat->src = "";
			$totreat->mode = 0;// 0
			$totreat->readPMode = 0; // read parameter mode for function
			$totreat->indentLevel = $m->options->offsetDepth + $m->options->bracketDepth;
			$totreat->parent = $m->options->toread;
			// igk_wln("*******************".$type."= ".$totreat->indentLevel);
			$totreat->documentation = $m->options->documentation ?
				implode("\n", $m->options->documentation->data) : null;
			$totreat->attributes = $m->options->decorator ?
				implode("\n", $m->options->decorator->data) : null;
			
			if (isset($m->options->chainRender)){
				foreach($m->options->chainRender as $chain){
					call_user_func_array($chain->bind, array($m->options, $totreat));
				}
			}			
				
			
				// igk_wln($m->options->decorator);
			$m->options->documentation=null; // reset documentation
			$m->options->decorator=null; // reset decorator
			if (isset($m->options->chainRender)){
				foreach($m->options->chainRender as $chain){
					call_user_func_array($chain->unset, array($m->options, $totreat));
				}
			}
			 
			// igk_wln($totreat->attributes);
			
			$totreat->handleChar = function(& $t, $start, & $offset, $ch, $m){ // return true if update the outpu
				$totreat = $m->options->toread;
				$cmode  = & $totreat->mode;			
				// igk_wln("end char __ ********************".$ch." ".$cmode." ".$totreat->type);
				if ($cmode<4){
					switch($totreat->type){
						case "function":
								if ($cmode==1){
									// read parameter								
									$cancel = 0;
									igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);								
									if ($cancel)
										return;
								}
								//function mecanism reading
								$step = ["("=>[0=>1],")"=> [1=>2], ":"=>[2=>3], "{"=>[2=>4, 3=>4]];
							 
								if (isset($step[$ch])){
									$b = $step[$ch];
									if (isset($b[$cmode])){
										//change mode
										if (($cmode==1) && ($ch == ")") && ($m->options->conditionDepth>1)){ 
											igk_wln_e("failed to return: ) line:".$m->options->lineNumber." conditionDepth: ".$m->options->conditionDepth);
											return;
										}
										if ($cmode==3){
											//read type
											$def = igk_treat_get($m->options).substr($t,0, $start);	
											$totreat->ReturnType = trim(substr($def, strrpos($def, ":", -1)+1 ));
											// igk_wln("ReturnType : ".$totreat->ReturnType );
											// igk_exit();
										}
										
										$cmode = $b[$cmode];
										$totreat->readingMode = $cmode;
										// igk_wln("reading mode :-- $cmode");
										// start and stop parameter reading ( ... )
										if( ($ch == "{") && ($cmode== 4)){

											$op_lx = substr($t,0, $start+1);											
											// igk_wln_e("start reading definitionon",
											$totreat->def = igk_treat_get($m->options).$op_lx;
											$totreat->offsetDefinition = strlen($totreat->def);
										}						
										else if ($ch=="("){
											$totreat->readP = array();
											$op_txt =  igk_treat_get($m->options); 
											$op_lx = substr($t,0, $start+1);
											$def = ltrim(preg_replace("/\\s+/", " ", $op_txt.$op_lx));//."||";
											$sdef=$def;
											// treat definition function:
											$fc = $totreat->startTreat;
											$fc($def, $m->options);

											// if (isset($totreat->isanonymous) && $totreat->isanonymous){
												// $totreat->def = $def;
											// } else 
											$totreat->def = $def; 
											igk_treat_set_context($m->options, 
											"parameterReading", 
											$m->options->mode, null,
											"function:? ".empty($def). " anonymous:".
											igk_getv($totreat,"isanonymous"));
											
											//remove last 
											// igk_treat_set($m->options, $def."(-----");
											// $t = "- ".$start." --.".$offset.".----".substr($t, 7);
											// $t = substr($t, $offset+1);
											// $offset = 0;
											
											
										}else if (($ch==")") && ($m->options->conditionDepth<=0)){		
											// $endef = igk_treat_get($m->options).substr($t,0 ,  $start);	
											igk_treat_restore_context($m->options);	
											$txt = igk_treat_get($m->options);
											// setup the output text to match definition
											if (!igk_getv($totreat,"isanonymous") && !strstr($totreat->def, $bs = ltrim($txt))){
											
												$toffset = strlen($txt) - strlen($bs);
												$gdef = substr($txt, 0, $toffset).$totreat->def.substr($txt, strpos($txt, "(")+1);//strlen($totreat->def));
												// igk_wln("DD:".$txt);
												// igk_wln("DDO:".$totreat->def);
												// igk_wln("GDEF:".$gdef);
												
												$totreat->def = ltrim($gdef);
												igk_treat_set($m->options, $gdef); 
											}
										}
									}
								}
								 
							break;	
						default:							
							if ($ch=="{"){
								$totreat->readP = array();
								$def = igk_treat_get($m->options).substr($t,0, $start);
								$fc = $totreat->startTreat;
								$fc($def, $m->options); 
								$totreat->def = $def;								
								$cmode=4;								
							}
						break;
					}
					if ($cmode>=4){
						// finish inline reading
						$totreat->handleChar=null;						
						//$m->options->DataLF = 1;
					}							
				}
				
			};
			$totreat->startTreat= $startTreat_Callback;			
			
			$totreat->endTreat = function($src, $options, $totreat){ // global end treatment
				
				// igk_wln("GET:".igk_treat_get($options));
				// igk_wln("endTreat:\n".$src);		
				 		
				igk_treat_restore_context($options);
				$bg = $options->toread;
				$options->endMarkerFlag = 0;
				$totreat->handleChar=null;
				$totreat->mode=4; //mode 4 
				$skip = 0;
				// igk_wln("endTreat:\n".$src, "info: ", $totreat->definitions["vars"]["tab"][0]);
				switch($totreat->type){
					case 'function':
					//+ end treat function
					//+ check contains definition
					if (count($_fcdef = $totreat->definitions)> 0){
						$sc = rtrim(igk_treat_outdef($_fcdef, $options, 1));
						if (!empty($sc) && ($_offset = $totreat->offsetDefinition) ){
							//+ insert definition in anonymout function declaration 
							$src = substr($src, 0, $_offset).
								IGK_LF.$sc. substr($src, $_offset);

							// igk_wln("new source : ".$src);
						}
						unset($sc, $_offset);
						//+ reset function definition
						$totreat->definitions = null;
					}
					unset($_fcdef);

					if (isset($totreat->isanonymous) && ($totreat->isanonymous==1)){					
							//+ formatting anonymous function 
							// igk_wln_e("source ," .$src);

							$src = preg_replace("/function\\s+\(/", "function(", $src);

							
							// igk_wln_e(__FILE__.":".__LINE__, $src, $totreat, $totreat->definitions);

							igk_treat_append($options, trim($src), 0);						
							if ($options->conditionDepth<=0){
								$options->definitions->lastTreat = $totreat;
								$options->endMarkerFlag = 1;
							}
							$options->DataLFFlag = 1;
							return;
					}					
					if ($totreat->parent){
						$skip =  ($options->bracketDepth-1)>0; 
					}else{
						$skip = $options->bracketDepth > 0;
					}
					if ($options->arrayDepth > 0){
						// $options->arrayDepth = 0;
						 // igk_ewln("failed: ","depth:".$options->arrayDepth, $options->lineNumber, $src);
						 igk_wln_e("\nfailed: ",
							"arraydepth:".$options->arrayDepth, 
							"currentLineNumber:". $options->lineNumber, 
							$src);
						 
					}
	 
					break;
					case "namespace":
						$options->toread = $totreat; // set the current ns
						
						if (strpos(rtrim($src),";",-1)!==false){ 
							$fc = $totreat->startTreat;
							$fc($src, $options);  
							
						}
						break;
					case "use":
				
						$options->toread = $totreat;					
						if (strpos(rtrim($src),";",-1)!==false){ 
							$fc = $totreat->startTreat; 
							$fc($src, $options); 
						}else{
							//finish use reading;						   										
							$options->endMarkerFlag = 1;
						}
						$options->toread = $bg; 
						break;
					case "class":
						break;
				}
	

				if ($totreat->parent){
						$skip =  ($options->bracketDepth-1)>0; 
					}else{
						$skip = $options->bracketDepth > 0;
					}
		
				
				 
				
				if (($totreat->type!="use") && igk_count($totreat->definitions)>0){
		
						$out = igk_treat_outdef($totreat->definitions, $options, 1);
						
						if ($totreat->type != "function"){
						 
							if (($pos = strpos(rtrim($src), "}", -1))===false){
								igk_wln_e("not end bracket found ".$src);
							}							 
							
							$gs = rtrim(substr($src, 0, $pos )).$options->LF;
							$indent = str_repeat($options->IndentChar, $totreat->indentLevel);
							$rf = "";
							if (($totreat->type=="namespace") && (isset($totreat->def))){
								//+ correct namespace source rendering
								$rf = substr($gs, ($_tpos = strpos($gs, "{"))+1); 
								$out = rtrim($out);
								$gs = $indent.ltrim(substr($gs,0, $_tpos+1).$options->LF);
								// $rf ="********";
								$totreat->globalSrc = $rf;
							} 
							$src = $gs.$out.$rf.$indent."}".$options->LF;
									
						} 
				}
				
				if ($skip){
					// remove
					igk_ewln("\e[0;41mwarning:\e[0m ".$totreat->type." [".$totreat->name."] is embeded in bracket out. Line: ".
					$totreat->startLine. " - ".$options->lineNumber );
									
					// igk_wln_e("skipped ....: ".$src);
					$options->DataLFFlag = 1;
					igk_treat_append($options, ltrim($src), 0);
					$options->DataLFFlag = 1;
					// igk_treat_removedef($m->options, $totreat);
					//unset($options->parent->definitions[$totreat->index]);						
					return;
				}

					
					
				//add to definitions
				if (is_object($options->toread) && ($totreat!==$options->toread)){
					$options->toread->definitions[$totreat->type][] = $totreat;
				}
				else{	
					$options->definitions->{$totreat->type}[] = $totreat;
				}
				$totreat->src = rtrim($src).$options->LF;
				$options->definitions->lastTreat = $totreat;
			};
			
			// must consider offset as 
			$initContext_callback($m);		
			$level = $m->options->offsetDepth + $m->options->bracketDepth;
			igk_treat_append($m->options, $def." ", 1);		
			$totreat->indentLevel = $level; 
			$m->options->toread = $totreat;
			$t = ltrim(substr($t, $type_offset));
			$offset = 0;
			return $t;
		}
	));	
	 
	 array_unshift($tab, (object)array(
		'name'=>'comment',
		'pattern'=>'#//(?P<symbol>(:|\+))?(.)*$#i',
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){ 
			//igk_wln(__FILE__.":".__LINE__, "comment********************", $m->data[0][0],
			//"pos : ".strpos( $m->data[0][0],  "//+") );
			if ((!empty($m->data["symbol"][0])) || (strpos($m->data[0][0],  "//+") ===0)){
				// igk_wln_e($m->data["symbol"]);
				// $m->options->DataLFFlag = 1;			 
				$offset = strlen($t)+strlen($m->options->LF);
				return $t.$m->options->LF;
			}
			if( $m->options->context=="globalConstant"){
				$offset = $start+strlen($m->options->LF)+2;
				$t = $m->options->LF.$t;
			}else{
				$before= trim(substr($t, 0, $start));
				if ($m->options->RemoveComment){					
					if (($pos = strpos(substr($t, $start) , "?>")) !==false)
					{
						$t = $before.substr($t, $start+$pos);
						$offset = strlen($before);
						return $t;
					}
					$t = $before;
					$offset = strlen($t)+1;
				}else{
					if (($pos = strpos(substr($t, $start) , "?>")) !==false)
					{ 
						$g = substr($t,0,  $pos);
						igk_treat_append($m->options, $g, 0);
						$m->options->DataLFFlag = 1;
						$t = substr($t, $pos);
						$offset = 0;
						return $t;
					} 
					$comment = substr($t, $start);
					$m->options->commentInfo[] = $comment;
					$offset = strlen($t)+1;  
					if ($m->options->content != "global"){
						igk_treat_append($m->options, $t, 0);
						$m->options->DataLFFlag = 1;
					}
					$offset = 1;
					$t = "";
					$offset = strlen($t)+1;
				}	
			}			
			return $t;
		}));
	
	 
	// read collapse comment definition
	array_unshift($tab, (object)array(
		'name'=>'globalConstant',
		'pattern'=>"#(^|\\s*)(?P<comment>//\\s*)?(?P<operator>define)\\s*\(#i",
		'mode'=>'*',
		'callback'=>function(& $t, $start, & $offset, $m){ 
			
			if ($m->options->operatorFlag  || (isset($m->options->command)
				&& igk_getv($m->options->command, "noDefineHandle"))){
				//next to operator
				$offset = strlen($m->data["operator"][0])+$m->data["operator"][1];
				return $t;
			}
			// is like conditional
			// igk_wln("match condition");
			$d = $m->data;
			$comment = isset($d["comment"])? !empty($d["comment"][0]) : false;
			
			// igk_wln("for out : ", $m->options->context);
			if ((($m->options->context=='global') && ($m->options->output!='<?php')) || $m->options->depthFlag || ($m->options->bracketDepth>0)){
				// detect that depthFlag detected
				if ($comment){ // leave define as is
					$t="";
					$offset==0;
					return $t;
				}
				$m->options->DataLFFlag= 1;
				$offset = $d["operator"][1]+strlen($d["operator"][0]);
				$g = ltrim(substr($t, 0,  $offset));
				$t = ltrim(substr($t, $offset));
				$offset = 0; 
				igk_treat_append($m->options, $g, 1);
				return $t;
			}
			// igk_wln_e("baseContext: ".$m->options->context. " mode:".$m->options->mode. " ".$m->options->depthFlag);
			 
			
			$objread= igk_createobj();
			$objread->type = "define";
			$objread->startLine = $m->options->lineNumber-1;
			$objread->mode = 0;
			$objread->comment = $comment;
			$objread->handleChar = function(& $t, $start, & $offset, $ch, $m){
				// igk_wln("define handlechar:".$ch);
				$u = $m->options->toread;
				switch($ch){
					case ")":
						if ($m->options->conditionDepth<=0){
								// finish define wait for ';' or read to end line
								$u = $m->options->toread;
								$u->mode = 4;
								$txt = igk_treat_get($m->options).substr($t, 0, $start+1);
								$u->src = $txt;
								// $mt = substr($txt, $u->argumentOffset);
								$offset = $start + 1; //strlen($t);
								 
								$m->options->DataLFFlag = 0; // wait for ";"
								$m->options->endMarkerFlag = 1;								
								$m->options->lastTreat = $u;
								$u->parentTreat = $m->options->lastTreat;								
								// $offset = $start+1;
								// $t = ltrim(substr($t, $start+strlen($m->data[0][0])+1));
								// $offset = 0;
								$u->handleChar = null; 
						}
						break;
					case ";":
						break;
					case ",":
						if (!isset($u->argumentOffset)){ //handle argument offset
							$u->argumentOffset = strlen(igk_treat_get($m->options))+$start+1;
							// igk_wln("argument offset ".$u->argumentOffset);
						}
						break;
					
				}
			};
			$objread->endTreat = function($s, $options, $toread) {				
				igk_wln_e("DEFINE -- END TREAT NOT IMPLEMENT", 
				__LINE__.':'.__FILE__, 
				$s);
			};
			if ($comment){
				$objread->newLineTreat = function(& $t, & $nextline, $options){
					$u = $options->toread;
					$s = ltrim($t);
					$gx = "#^//\s*#";
					if (preg_match($gx, $s)){
						$s = preg_replace($gx, "", $s);
						if ($u->mode==4){
							if (strpos($s, ";")===0){
								$t = $s; // continue as reading
								return;
							}
							$t = ";".$s;
							//igk_wln_e("wait for ;".$s);
							return;
						}
						igk_debug_wln("new line treat:", $t, "mode: ", $u->mode, $s);
						$t = $s;
					}
					else{
						if ($u->mode == 4){
							//$u->src .= ";";
							$t=";";//.$s;
							$nextline--;
							// igk_wln_e("end comment:", $u);
							$u->handleChar = null;
						}else {
							//remove comment
							$u->src = "";
							$u->handleChar = null;
							igk_treat_restore_context($options, 1); 
							//$options->toread = null;
							//igk_wln_e("remove comment.....");
						}
					}
				};
			}
			
			$offset = $start+strlen($m->data [0][0]);
			
			igk_treat_set_context($m->options, $m->matcher->name, 2, array(
			"DataLFFlag", 
			"toread", 
			"data", 
			"conditionDepth", "lastTreat"));
			$m->options->DataLFFlag= 0;
			$m->options->toread = $objread;
			$m->options->data = "";
			igk_treat_append($m->options, ltrim($m->data[0][0]), 1);
			$t = substr($t, $offset);
			$offset = 0;
			$m->options->conditionDepth = 1;  
			return $t;
		}
	));
	


	
	
	array_unshift($tab, (object)array(
		'name'=>'documentation',
		'pattern'=>"#///(?P<data>[^/](.)*)$#i",
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){
			$options = $m->options;
			$ln = strlen($options->output);
			$t = substr($t , 0, $start);
			$offset= strlen($t);
			if (!isset($options->documentation) || ($options->documentation->start!=$ln)){
				$options->documentation = (object)array("start"=>$ln, "data"=>array());
			}
			$options->documentation->data[] = $m->data['data'][0];
			 
			return $t;
		}
	));
	
	
	array_unshift($tab, (object)array(
		'name'=>'decorator',
		'pattern'=>"#//(?P<data>@[^/](.)*)$#i",
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){
			$options = $m->options;
			$ln = strlen($options->output);
			$t = substr($t , 0, $start);
			$offset= strlen($t);
			if (!isset($options->decorator) || ($options->decorator->start!=$ln)){
				$options->decorator = (object)array("start"=>$ln, "data"=>array());
			}
			$before = substr($t, $start);
			if ( ($pos = strpos($t, "?>")) ===false){
				$txt = $m->data['data'][0];
				$options->decorator->data[] = $txt; 
			}else {
				//remove data
				$t = $before.substr($t, $pos);
				$offset = strlen($before);
				
			}	
			// igk_wln($options->decorator);
			// igk_wln_e("handle_deco");
			return $t;
		}
	));
	
	

	array_unshift($tab, (object)array(
		'name'=>'specialDocument',
		'pattern'=>"#///\\s*(?P<info>(TASK|TODO|REMARK|NOTE|DEBUG))\\s*:\\s*(?P<data>(.)+)$#i",
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){
			//leave unchange
			$m->options->DataLFFlag=1;
			igk_treat_append($m->options, trim($m->data[0][0]).$m->options->LF, 1);
			$m->options->DataLFFlag=1;
			$t ="";
			$offset=0;
			return $t;
			 
		}
	));
	
		
	//start multiline string
	array_unshift($tab, (object)array(
			'name'=>'startmultilineString',
			'pattern'=>"#\\s*\<\<\<(')?(?P<name>[a-zA-Z]+)(\\1)?\s*$#",
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, & $m){
				$startup = 0;
				// if ($m->options->context != $m->matcher->name){					
					//start multiline string
				igk_treat_set_context($m->options, $m->matcher->name, 0);					
				// }
				$n = $m->data["name"][0];
				$l = & $m->options->lineNumber;
				$s = substr($t, $start).$m->options->LF;
				$before = substr($t, 0, $start);
				$tL = $m->options->totalLines;
				$sL = $l; // start line
				//$l++;
				$c = 0;
				// end context
				$rgx = "/^".$n."(;)?$/";
				// igk_wln("regex: ".$rgx);
				while($l < $tL){
					// igk_wln("d: ".$l);
					$t = rtrim($m->options->source[$l]);
					// igk_wln("Match: ".$t." ".preg_match($rgx, $t));
					$s.=$t;
					if (preg_match($rgx, $t)){						 
						$c=1;
						$l++;
						break;
					}
					$s.= $m->options->LF;
					$l++;
				} 
				if ($c){ 
					$sf = rtrim($before.$s);
					if ($sf[strlen($sf)-1]!=";"){ 
						$m->options->multiLineFlag = 1;	
						$m->options->mustPasLineFlag= 1;
					} 				 
					$m->options->DataLFFlag = 0;					
					igk_treat_append($m->options, $sf."\n", 0);
					$t = "";
					$offset = 0; 
					
				}else{
					igk_wln_e("no closed multistring found:".$sL);
				}				 
				igk_treat_restore_context($m->options);
				$m->options->DataLFFlag = 1; 
				return $t;
			}));
				 
		//start multiline
		array_unshift($tab, (object)array(
			'name'=>'startmultilineComment',
			'pattern'=>"#/\*#i",
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, $m){
				if ($m->options->context != $m->matcher->name){					
					//start multiline comment
					igk_treat_set_context($m->options, $m->matcher->name);					
				}
				//igk_wln("is start ? ".$m->options->RemoveComment);
				$before = trim(substr($t, 0, $start));
				$move = 0;
				if ($m->options->RemoveComment){
					$t = substr($t, $start+2);
					$l = & $m->options->lineNumber;
					$pos = null;
					// igk_wln("t: ".$t);
					while( ($pos = strpos($t, "*/")) === false){
						// igk_wln("tt: ".$t);
						$move=1;
						if($l < $m->options->totalLines){
							$t = $m->options->source[$l];
							$l++; 
							continue;
						} 
						break;
					}
				  // igk_wln("diiiii?".$pos);
					igk_treat_restore_context($m->options);
					if ($pos!==false){
						//$l++;
						$g = substr($t, $pos+2);
						$t = $before . $g;
						$offset = strlen($before);
						return $t;
					}
					else { // no end comment found
						$t = "";
						$offset = 1;
						return $t;
					} 
					
				}else{
					//leave comment
					$sm = "";
					while( ($pos = strpos($t, "*/")) === false){
						$move=1;
						$l++; 
						if($l < $m->options->totalLines){
							$sm .= rtrim($m->options->source[$l]).$m->options->LF;
							continue;
						} 
						break;
					}
					if ($pos!==false){
						//$l++;
						$g = substr($t, $pos+2);
						$t = $before . $g;
						$offset = strlen($before);
						if ($move && empty(trim($t))){
							$l++;// move to next line
						}
						return $t;
					}
					else { // no end comment found
						$t = "";
						$offset = 1;
						return $t;
					} 
					
					$offset = $start+2;
				}				
				igk_wln_e("comment: failed attention");
			}));
		
	array_unshift($tab, (object)array(
			'name'=>'specialkey',
			'pattern'=>'/(^|\\s+)(?P<operand>break|continue|return|goto)(\\s*)(\\s+|;|$)/',
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, $m){
				unset($m->options->lastOperand);
				$op = $m->data["operand"];
				$tc = $m->data[0][0];
				if ($op[0]=="return"){
					if (strpos($tc, ";")===false){
						//multiline
						$m->options->DataLFFlag=1; // wait for ";"
						igk_treat_append($m->options, "return ", 0);
						$m->options->DataLFFlag=0; 
						
					}else{
						igk_treat_append($m->options, "return;",0);
						$m->options->DataLFFlag=1;
					}
					$t = ltrim(substr($t, $start+strlen($tc)));
					$offset = 0;
					return $t;
				}
				
				$c = preg_replace("/\\s+/", "", $tc);
				
				$m->options->DataLFFlag = 1;
				igk_treat_append($m->options, $c, 0);
				if (strpos($tc, ";")===false){
					$m->options->lastOperand = $c;
					$m->options->DataLFFlag = 0;
				
				}else{
					$m->options->DataLFFlag = 1;
					$m->options->bracketVarFlag = 0;
					// igk_wln_e("bbbjb");
				}
				$t = substr($t, $start+strlen($tc));
				$offset=0;
				return $t;
				 
			}));
	array_unshift($tab, (object)array(
		"name"=>"phpHtmlPreproc",
		"mode"=>-1,
		"pattern"=>"/^(.)+$/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$m->data = array(0=>array("", 0));
			$t = igk_treat_handle_html($t, $start, $offset, $m, $c);
			$def = igk_treat_get($m->options);
			//reset def definition
			$m->options->data="";
			if ($c){
				igk_treat_set_context($m->options, "global", 0);
			}
			// igk_wln("c:".$c);
			// igk_wln("writedef:".$def. " offset:".$offset);
			if (preg_match("/^#!/", $def)){
				//
				$m->options->startCGIOffSet = strlen($def);
				// igk_wln("is cgi script:".$m->options->startCGIOffSet);
			}
			$m->options->output = $def;			
			return $t;
		}
	));	
	//root level 
	array_unshift($tab, (object)array(
		"name"=>"phpPreproc",
		"mode"=>-1,
		"pattern"=>"/\\s*\<\?(php|=)?/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$offset = $start + strlen($m->data[0][0]);
			if (isset($m->options->startFileFlag)){
				$m->options->startFileFlag = 1;				
			}
			igk_treat_set_context($m->options, "global", 0);
			$m->options->DataLFFlag=0;
			$lf = "";
			if (!empty($m->options->output))
				$lf = $m->options->LF;
			igk_treat_append($m->options, $lf."<?php", 0);
			$m->options->DataLFFlag=1;
			$t = substr($t, $offset);
			$offset = 0; 
			return $t;
			
			
		}
	));	
	array_unshift($tab, (object)array(
			'name'=>'phpEndProcessorHandle',
			'pattern'=>'/(\\s*|^)\?\>/',
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, & $m){
		
				igk_treat_set_context($m->options, "html", 0);
				$tc = $m->data[0][0];  				 
				$offset=0;
				$end_rgx = "/\<\?(=|php)/";
				$o = "";
				$before = substr($t, 0, $start);
				$offset = $start+ strlen($tc); //$m->data[0][0];
				$t = substr($t, $offset);
				$offset = 0;
				$ln = & $m->options->lineNumber;
				$tn = $m->options->totalLines;
				$c = 0;
				$s = "";
				$lf = "";
				$lflag = 0;

				$m->options->trace = 1;
				while(($ln-1) < $tn ){					
					// $m->options->trace = 1;
					//detect next <?php open tag 
					if (preg_match($end_rgx, $t, $tab, PREG_OFFSET_CAPTURE, $offset)){						 
						$c=1;
						$s .= substr($t, 0, $tab[0][1]);
						// igk_wln("MATCHING :".substr($t, 0, $tab[0][1]), strlen(substr($t, 0, $tab[0][1])));
						$t = substr($t, $tab[0][1] +strlen($tab[0][0]));
						
					}
					if ($c || ($ln>=$tn))
						break;
					
					$s.= $t;
					$t = $m->options->source[$ln];					
					$s.= $m->options->LF;
					$ln++;
					$offset=0;
				} // end while

				if ($c){
					$v_n = $tab[0][0];
					if ($lflag = ($v_n=="<?php")){
						$lf = "";//$m->options->LF;
						$v_n.= " "; // add space to end bracket style
					}
					
										
					$o = $s.$lf."{$v_n}";
					$offset = 0;
				}else{ 
					$o = trim($s);
					$offset = strlen($t);
				}
				igk_treat_restore_context($m->options);
								
				// igk_wln_e("Match: ".$o ,  $tab[0][0], $lf, "before : ".$before);
				if (!empty($before)){
					$m->options->DataLFFlag = 0;
					igk_treat_append($m->options, $before, 0);
				}
				//+ writing endproc 
				$endproc = "?>";
				if ($m->options->bracketVarFlag || $m->options->DataLFFlag){ 
					$m->options->DataLFFlag = 1;
					$m->options->bracketVarFlag = 0; 
				}
				else { 
					$endproc = " ".$endproc; // spacing before end proc required
				}
				igk_treat_append($m->options,  $endproc.$o, 0);
				if (!$c){
						igk_treat_append($m->options, $t, 0);
						$t="";
						$offset=0;
				}
				$m->options->DataLFFlag = $lflag ? 1 : 0;
				return $t;
				 
			}));
	
	if (file_exists($cf = 'armonic_php_defined_instruct.pinc'))
		include($cf);
	unset($cf);

	//+ files descritor tag

	$files_descriptors = "(".implode("|", ["author","version", "description", 
	"release", "license", "company", "mail", "url", "file"]).")";
	
	array_unshift($tab, (object)array(
			'name'=>'fileDescription',
			'pattern'=>'/\/\/\\s*(?P<name>(@?'.$files_descriptors.')):\\s*(?P<value>(.)+)$/',
			'mode'=>function($option){
				return ($option->mode == 0) && ($option->bracketDepth<=0);
			},
			'callback'=>function($t, $start, & $offset, & $m){
				// igk_wln_e("file descript :::: ");
				 
				 $n = $m->data["name"][0];
				 $v = $m->data["value"][0];
				 $m->options->definitions->{"filedesc"}[]= $m->data[0][0].IGK_LF;
				 $before = rtrim(substr($t, 0, $start));
				 $t="";
				 $offset=0;
				 if (!empty($before)){
					 $t = $before;
					 $offset = strlen($t);
				 }
				 return $t;
				 
			}));	
	
	

			if ((isset($options) && $c = $options->command))
	{
		if(igk_treat_ogetv($c, "allowDocBlocking")){
			// igk_wln("star;;...");
			$options->chainRender[] = (object)array("bind"=>function($options, $totreat){
				$totreat->docBlocking = igk_getv($options, "docBlocking");
			}, "unset"=>function($options){
				 $options->docBlocking = null;
			});
			
			$options->documentationListener[] = function($options, $totreat, $indent=0){
				 
				$v1 = $totreat;
				$s = $v1->docBlocking;
				if (empty($s)){
					
					$s = "/**".$options->LF;
					if($v1->documentation){
						// convert xmldoc to docBlocking
						$s .= igk_treat_converttodockblocking($v1->documentation, $options); //.$options->LF;						
						// $s .= "* @with documentation ".$options->LF;
						
						// igk_wln("s : ".$v1->documentation);
						// igk_wln_e("s : ".$s);
						
					}else{
						$s .= "* ".__("represent")." {$v1->name} {$v1->type}".$options->LF;
						// $s .= "* @return ".$v1->type.$options->LF;
						
						if (!igk_getv($options, "noAutoParameter") && isset($v1->readP)){
							foreach($v1->readP as $kv=>$vv){
								$s .= "* @param 77 ".igk_getv($vv, "type" , "mixed")." ";
								if (isset($vv->ref) && $vv->ref){
									$s.="* ";
								}		
								$s.= "$".$vv->name;
								if (isset($vv->default)){
									// protect default value of */
									
									$s.= " the default value is ".preg_replace("#\*/#", "\*\/", $vv->default);
								}
								$s.=$options->LF; 				 
							}				
						}
						
						if (($cond1=isset($v1->ReturnType)) | ($cond2 = (isset($v1->options) && igk_getv($v1->options, "ref")))){
							$s .= "* @return ";
							if ($cond1)
								$s.= $v1->ReturnType;
							if ($cond2){
								$s .= "*";
							}
							$s.=$options->LF;
						}
						
						
					}					
					$s .= "*/";	
				}				
				return igk_str_format_bind($indent."{0}".$options->LF, explode("\n", $s));//.IGK_LF;
			};
			
			array_unshift($tab, (object)array(
			'name'=>'docBlocking',
			'pattern'=>'/\/\*\*/',
			'mode'=> "*",
			'callback'=>function($t, $start, & $offset, & $m){
				
				$ln = & $m->options->lineNumber;
				$before = substr($t, 0, $start);
				$block_ln = substr($t, $start);
				$boffset = 0;
				$s = "";
				$c = 0;
				while($ln <= $m->options->totalLines){
					// igk_wln("block: ".$ln);
					if ( ($pos= strpos($block_ln, "*/", $boffset))!==false){
						//
						// igk_wln("found");
						$s .= ltrim(substr($block_ln, 0, $pos+2));
						$c = 1;
						$t = $before.substr($block_ln, $pos+2);
						$offset = strlen($before);
					
						break;
					}
					$s .= ltrim($block_ln).IGK_LF;
					$block_ln = $m->options->source[$ln];
					$ln++;
					$t = $block_ln;
				  $boffset = 0;
				}
				$m->options->docBlocking = $s;
				// igk_wln("offset:".$offset); 
				// igk_wln("s:".$s); 
				// igk_wln("blocking finish:".$t);
				// igk_exit();
				 return $t;	
				 
			}));	
	
	
			
		}		
	}	
	return $tab;
}
function igk_treat_reset_operatorflag($m){	
	unset($m->options->staticMarkerOperator);
}
function igk_treat_handle_html(& $t, $start, & $offset, $m, & $found=null){
	igk_treat_set_context($m->options, "html", 0);
	$is_start = empty($m->options->output);
	
	$tc = $m->data[0][0];  				 
	$offset=0;
	$end_rgx = "/\<\?(php)/";
	$o = "";
	$before = substr($t, $start);
	$offset = $start+ strlen($tc); //$m->data[0][0];
	$t = substr($t, $offset);
	$offset = 0;
	$ln = & $m->options->lineNumber;
	$tn = $m->options->totalLines;
	$c = 0;
	$s = "";
	while($ln< $tn ){		
		
		if (preg_match($end_rgx, $t, $tab, PREG_OFFSET_CAPTURE, $offset)){						 
			$c=1;
			$s .= substr($t, 0, $tab[0][1]);
			$t = substr($t, $tab[0][1] +strlen($tab[0][0]));
			 
			break;
		}
		// igk_wln("Match: ".$t );
		
		$s.= trim($t);
		$t = rtrim($m->options->source[$ln]);		
		$s.= $m->options->LF;
		$ln++;
		$offset=0;
	}
	if ($c){
		$o = rtrim($s);
		if (!empty($o)) { // && !$is_start){
			$o.= $m->options->LF;
		}
		$o .= "<?php";
		$offset = 0;
	}else{ 
		$s .= rtrim($t);
		$o = $s;	
		$t= "";		
		$offset = 0;
		
	}
	// igk_wln($offset);
	// igk_wln($c); 
	// igk_wln($ln);
	// igk_wln("s:".$s);
	// igk_wln("o:".$o);
	// igk_wln_e("t:".$t);

	igk_treat_restore_context($m->options);
	// $m->options->DataLFFlag = 0;
	if (!empty($o = trim($o))){
		if ($is_start){
			igk_treat_append($m->options, trim($m->data[0][0]).trim($o), 0);
		}else 
			igk_treat_append($m->options, trim($m->data[0][0]).trim($o), 0);
		$m->options->DataLFFlag = 1;
	}
	$found = $c;
	return $t;
}

//
function igk_treat_set_context($option, $context, $mode=0, $states=null,  $tag=null){
	// igk_wln("set context : ".$context);
	$option->chaincontext[] = array(
		$option->context,
		$option->mode,
		$option->tag);
	$option->context = $context;
	$option->mode = $mode;
	$option->tag = $tag; 
	$states = $states? $states: array();	
	igk_treat_push_state($option, $states);
	
}
function igk_treat_restore_context($option){
	// igk_debug_wln("restore context - context - ".$option->context);
	// igk_wln("set context:".$context);
	$inf = array_pop($option->chaincontext);
	if ($inf){ 
		$option->context = $inf[0];
		$option->mode = $inf[1];
		$option->tag = $inf[2];
	}
	igk_treat_pop_state($option);
}

///<summary>use igk_treat_restore_context</summary>
function igk_treat_push_state($option, $keys){
	// igk_wln("push - state - ");
	$obj = igk_createobj();
	if (!is_array($keys))
	{
		igk_wln(__FILE__.":".__LINE__, __FUNCTION__);
		igk_trace();
		exit;
	}
	foreach($keys as $k){
		if (isset($option->$k))
			$obj->$k = $option->$k;
	}
	$option->states[] = $obj;
	return $obj;
}

///<summary>use igk_treat_restore_context</summary>
function igk_treat_pop_state($option){	 
	$g = array_pop($option->states);
	if ($g){
		foreach($g as $k=>$v){
			$option->$k = $v;
		}
	}
	return $g;
}




function igk_treat_lib($autocheck=1, $verbose=1){
ini_set("max_execution_time", 0);

$dir = $sdir = "d://wwwroot/igkdev/Lib/igk/";
// $dir = "d://wamp/www/igkdev/Lib/igk/";
// $outdir = "d://temp/Lib/igk";
$outdir = "D://wwwroot/demoigk/Lib/igk";
$count = 0;
$treat = 0;
$dln = strlen($sdir);
// $dir = 'D:\dev\2019\php\sources';
$ignore_dir = "/(".implode("|", ["\.git", "\.vscode"]).")$/";

foreach(igk_io_getfiles($dir, "/\.(.)+$/i", true, $ignore_dir) as $k=>$v)
{
	//remove files 
	if (preg_match("/\.(gkds)$/i", $v)){
		continue;
	}
	$n = substr($v,$dln);
	if (preg_match("/\.(php|phtml|pinc|pcss|inc)$/i", $v)){

			if($autocheck){
				exec("php -l ".$v, $out, $c);
				if ($c !=0 ){
					igk_wln("Failed to check : ".igk_io_dir($v));
					continue;
				}
			}
			
			// continue;
			
			if ($verbose)
				igk_wln(igk_io_dir($v));
			$src = igk_io_read_allfile($v);
			$options = igk_str_read_createoptions();
			$options->treatClassSource = 1;
			

			if (strpos($src, "<?php")===0){
				$src = igk_str_read_treat_source($src, $options);
			}
			$outfile = $outdir."/".$n;
			igk_io_w2file($outfile, $src);
			$treat++;
	
	}else{
		
		$f = $outdir.'/'.$n;
		
		// igk_wln("copy to : ".$f);
		if (IGKIO::CreateDir(dirname($f))){
			if (file_exists($f)){
				// igk_wln('file exists');
				unlink($f);
			}
			// igk_wln("copy to ".$v. " ot ".$f);
			copy($v, $f);
		}
		//else{
			// igk_wln("directory not exists");
		//}
	}
	$count++;
	
}
igk_wln("total: ".$count);
igk_wln("treat: ".$treat);

}

///<summary>treat modifier </summary>
function igk_treat_modifier($m){
	//+ armonise modifier detection
	if (empty(trim($m))){
		return "";
	}
	static $ModiL = null;
	
	if ($ModiL == null){
		$ModiL = array(
		"abstract"=>0,
		"final"=>40,
		"protected"=>10,
		"private"=>11,
		"public"=>12,
		"static"=>50,
		"const"=>51,
		"var"=>53
		);
	}
	$tb = explode(" ", $m);
	usort($tb, function($a, $b) use ($ModiL){
		if (!isset($ModiL[$a])){
			//igk_wln_e("mobil not a found : ".$a);
			$a = "public";
		}
		if (!isset($ModiL[$b])){
			//igk_wln_e("mobil not b found : ".$b);
			$b = "public";
		}
		$c1 = $ModiL[$a];		
		$c2 = $ModiL[$b];
		$r = ( $c1<$c2 ) ? -1 : (($c1==$c2)  ? 0 : 1);
		//igk_wln("compare: ", $c1, $c2, " = ".$r);
		return $r;
	});
	$s = implode(" ", $tb);
	 //igk_wln("modifier sort data : ".$s);
	return $s;
}


// igk_treat_lib();
// exit;

function igk_treat_handle_use($m, $type){
	$totreat = $m->options->toread;
	
	if (is_object($totreat)){ //
		switch($totreat->type){
			case "use":  
				 if ( ($type=="function") ||
					  ($type=="const") )
						igk_treat_append($m->options, $type." ",0);				 
					return 1;
				
				break; 
		}
	}
	return false;
}

function igk_treat_generator($def, $callbacks){
	$tab_args = array_slice(func_get_args(), 2);
	$tdef = array(array_merge([$def], $tab_args));
	while($hdef = array_pop($tdef)){
		$def = $hdef[0];
		$tab_args = array_slice($hdef, 1);
		$tab = igk_getv($def,"filedesc");
		if ($tab && isset($callbacks["filedesc"])){			 
			call_user_func_array($callbacks["filedesc"], array_merge(array($tab, & $tdef), $tab_args));
		}
		$tab = igk_getv($def,"FileInstruct");
		if ($tab && isset($callbacks["FileInstruct"])){			 
			call_user_func_array($callbacks["FileInstruct"], array_merge(array($tab, & $tdef), $tab_args));
		}
		
		 
	
		
		///TASK: treat use
		$tab = igk_getv($def,"use");
		if ($tab && isset($callbacks["use"])){
			usort($tab, function($a, $b){
				return $a->name <=>$b->name;
			});
			call_user_func_array($callbacks["use"], array_merge(array($tab, & $tdef), $tab_args));
		}
		//treat: global usage
		$tab = igk_getv($def,"global");
		if ($tab && isset($callbacks["global"])){			 
			call_user_func_array($callbacks["global"], array_merge(array($tab, & $tdef), $tab_args));
		}

		//treat: vars
		$q = igk_getv($def,"vars");
		if ($q && isset($callbacks["vars"])){
			$tab = $q["tab"];
		// $tab = $m->options->modifierArgs;
		usort($tab, function($a, $b){
			$r = strcmp($a->modifier, $b->modifier);
			if ($r==0){
				$r = strcmp($a->name, $b->name);
			}
			return $r;
		});
		call_user_func_array($callbacks["vars"], array_merge(array($tab, & $tdef), $tab_args));
		}
		
			// igk_wln_e("treat::::gggg");
		//treat: function
	$tab = igk_getv($def,"function");
	if ($tab && isset($callbacks["function"])){
		usort($tab, function($a, $b){
			return $a->name <=>$b->name;			
		});
		call_user_func_array($callbacks["function"], array_merge(array($tab, & $tdef), $tab_args));	 

	}
	$tab = igk_getv($def,"interface");
	if ($tab && isset($callbacks["interface"])){
		usort($tab, function($a, $b){
			$da = $a->{'@extends'} ?? "";
			$db = $b->{'@extends'} ?? "";
			if (($r = ($da <=>$db))==0)
				return $a->name <=> $b->name;
			return $r;
		});
		call_user_func_array($callbacks["interface"], array_merge(array($tab, & $tdef), $tab_args));	
		
		 
	}
	$tab = igk_getv($def,"trait");
	if ($tab && isset($callbacks["trait"])){
		usort($tab, function($a, $b){ 
				return $a->name <=> $b->name;
			});		
		call_user_func_array($callbacks["trait"], array_merge(array($tab, & $tdef), $tab_args));	
	}
	
	
	$tab = igk_getv($def,"class");
	if ($tab && isset($callbacks["class"])){
		//because class order in file is important class name must be ordered in reversed extends definitions
			$klist = array();
			$v_sroot = "/";
			foreach($tab as $k=>$v){ 	
				
				$n =$v_sroot;
				if (isset($v->{'@extends'})){
					$p = $v->{'@extends'};
					$key = $v->name;
					$n = $v->{'@extends'};
					while($p && isset($tab[$p])){
						$key = $p."/".$key;
						$p = igk_getv($tab[$p],'@extends');
					}
					$klist[$key] = $v; 
				}else {
					$klist[$v->name] = $v;
				} 
			}  
			$cl = array_keys($klist);
			sort($cl);
			
			$ktab = array();
			foreach($cl as $k){
				$ktab[] = $klist[$k];
			}
			call_user_func_array($callbacks["class"], array_merge(array($ktab, & $tdef), $tab_args));
			 
	}
	
			$tab = igk_getv($def, "namespace");
			if (isset($callbacks["namespace"]) && (igk_count($tab)>0)){		
				usort($tab, function($a, $b){
					return $a->name <=> $b->name;
				}); 
				call_user_func_array($callbacks["namespace"], array_merge(array($tab, & $tdef), $tab_args));
			
				
		}
	
	}	
	
	
}


function igk_treat_bind_data($command=null){

if (!defined("ARMONIC_DATA_FILE"))
		return;

$file = ARMONIC_DATA_FILE;
$outfile = ARMONIC_DATA_OUTPUT_FILE;

if(!file_exists($file)){
	igk_wln_e("file not exists");
	igk_exit();
}
// $file = "d://wamp/www/igkdev/Lib/igk/igk_redirection.php";
// $file = "d://wamp/www/igkdev/Lib/igk/igk_html_func_items.php";
// $file = "d://wamp/www/igkdev/Lib/igk/igk_framework.php";
if($command){
$command->inputFile = $file;
$command->outFile = isset($command->outFile) ? $command->outFile :  $outfile;

igk_treat_filecommand($command);
return;
}

$g = exec("php -l ".realpath($file)." 2> NUL", $c, $o); // redirect error no null
if ($o!=0){
	igk_wln_e(__FILE__.":".__LINE__,  $c);	
}
$source = file_get_contents($file);


//TODO: handle string value: is constant way
//



$mp = igk_treat_source($source);

// igk_wln("source:".$source);
if (strlen($mp)<250000){
	igk_wln("output:\n".$mp);
}
 

igk_io_w2file($outfile, $mp);
$g = exec("php -l ".realpath($outfile)." 2> NUL", $c, $o); // redirect error no null
if ($o!=0){
	igk_wln_e($c);	
} 
}


function igk_treat_show_usage(){
	$date = date("Y");
	igk_wln(<<<EOF
This is igkdev trait php source code CLI
author: C.A.D. BONDJE DOUE
copyright: IGKDEV @ {$date}
version : 2.0

usage:	

EOF
);

$helps = igk_get_env("treat//command_help");
if ($helps){
	$keys = array_keys($helps);
	usort($keys, function($a, $b){
		$p=0;
		$aa="";
		$bb="";
		while(($p<strlen($a)) && ($a[$p]=="-")){ $aa.="-"; $p++;}
		$p=0;
		while(($p<strlen($a)) && ($b[$p]=="-")){ $bb.="-"; $p++;}
	
		if ($aa == $bb){
			return $a <=> $b;
		}else {
			if ($aa=="-"){
				return -1;
			}else{
				return 1;
			}
		}
	});
	$lft = str_pad("", 36, " ");
	foreach($keys as $k){
		$v = $helps[$k];

		// $rt = str_split($v, 60);

		// $v = implode("\n".$lft, $rt);
		igk_wl(str_repeat(" ", 2)."\e[1;32m". str_pad($k, 30, " ")."\e[0m".str_repeat(" ", 2). ": " .$v."\n");
	}
}
}
function igk_treat_command(){
	$c = igk_get_env("treat//command", array());
	$helps = igk_get_env("treat//command_help", array());
	if (!$c){
		$c["-o"] = function($v, $t){
			$t->outDir = igk_io_expand_path(trim($v));
		};
		$c["-v"] = function($v, $t){			
			if (igk_count($t->command)==1){
				igk_wln("version:1.0"); 
				igk_exit();
			}			
		};
		$c["-h"] = function($v, $t){			
			if (igk_count($t->command)==1){
				igk_treat_show_usage();
				igk_exit();
			}			
		};
	}
	return $c;
}
function igk_treat_reg_command($k, $v, $help=null){
	$c = igk_treat_command();	
	$cmd = explode(",", $k);
	foreach($cmd as $kk=>$vv){
		$c[trim($vv)] = $v;
	}
	
	if($help){
		$ch = igk_get_env("treat//command_help", array());
		$ch[$k] = $help;
		igk_set_env("treat//command_help", $ch);
	}
	igk_set_env("treat//command", $c);
}

function igk_treat_execute_command($c){
	if (!($fc = $c->{"exec"})){
		igk_treat_show_usage();
		igk_exit();
	}
	if (igk_treat_ogetv($c, "debug")==1){		
		igk_debug(1);
	}
	$c->reports = array();
	igk_start_time(__FUNCTION__);
	$fc($c);
	$ct= igk_execute_time(__FUNCTION__);
	if (!empty($c->reports)){
		igk_wln("time: ".$ct."s");
		igk_wln("Reports:");
		foreach($c->reports as $k=>$v){
			igk_wln($k."\n\t:".$v);
		} 
	}
	$c_e = 0;
	if (!empty($c->errors)){
		$c_e = igk_count($c->errors);
	}
	if (!isset($c->noOutput))
		igk_ewln("Errors : ".$c_e);
}

function igk_treat_filecommand($command){
	$file = $command->inputFile;
	if (!isset($command->noAutoCheck) || ($command->noAutoCheck==0) ){
		$g = exec("php -l \"".realpath($file)."\" 2> NUL", $c, $o); // redirect error no null
		if ($o!=0){
			igk_ewln("\e[0;31mlint error: ");
			igk_ewln($c);
			igk_ewln("\e[0m");
			if (!isset($command->errors)){
				$command->errors = array();
			}
			$command->errors["{$file}"] = $c;
			return;
		}
	}
	$source = file_get_contents($file);
	$options = igk_treat_create_options();
	$options->command = $command;
	 
	if (igk_treat_ogetv($command, "leaveComment") == 1){
		$options->RemoveComment = 0;
	}  
	if (igk_treat_ogetv($command, "noDefineHandle")){
		$options->noDefineHandle = 1;
	}
	if (igk_treat_ogetv($command, "noFileDesc")){
		$options->noFileDesc = 1;
	}
	
	if (igk_treat_ogetv($command, "genxmldoc")){
		$options->endDefinitionListener[] = function($def, $command){
			$dir =dirname($command->outFile);
			$v_sin = "";
			if(isset($command->xmlOutDir)){
				$dir = $command->xmlOutDir;
				if(isset($command->inDir)){
					$v_sin = substr(dirname($command->inputFile), strlen($command->inDir)+1);
					if (!empty($v_sin)){
						$v_sin .= "/";
					}
				}
			}
			$f = $dir."/".$v_sin.igk_io_basenamewithoutext($command->outFile).".xml";
			
			$xml_doc = igk_createxmlnode("doc");
			$xml_doc["xmlns"] = "https://schema.igkdev.com/balafon/armonic/xmldoc";
			$xml_doc["code"]= "php";
			
			$xml_doc->add("script")->add("name")->Content = basename($command->outFile);
			$m = $xml_doc->add("members");
			
			// $c =  $m->add("classe");
			// $i =  $m->add("interface");
			// $t =  $m->add("trait");
			// $ct = $m->add("const");
			$op  =(object)array(
			"mustclosetag"=>function(){
				return 0;
			});		
			igk_treat_generator($def, [
				"function"=>function($tab, & $tdef,  $m, $op){
						// igk_wln_e("generator ....");
						$fc = $m->add("functions");
						foreach($tab as $v){
							$m = $fc->add("member");
							$m["name"] = $v->name;
							
							
						if ($v->documentation){
							foreach(explode(IGK_LF, $v->documentation) as $out){
							$t = igk_createtextnode($out);
							$m->add($t);//.IGK_LF;
							}						
						}
						else{
							$out = array();
							if ($v->name=="__construct"){
								$out[] = "<summary>.ctr</summary>";							
							} else 
								$out[] = "<summary>".__("represent")." ".$v->name." ".$v->type."</summary>";
							
							if ($v->readP){
								foreach($v->readP as $kv=>$vv){
									$gs ="";
									$g = igk_createxmlnode("param");
									$g["name"]=$vv->name;
									if (isset($vv->default)){
										$g["default"] = $vv->default;
									}
									if (isset($vv->type)){
										$g["type"] = $vv->type;
									}
									if (isset($vv->ref) && $vv->ref){
										$g["ref"] = "true";
									}														
									$out[] = $g->render($op);				 
								}				
							}
							if (($cond1=isset($v->ReturnType)) | ($cond2 = (isset($v->options) && igk_getv($v->options, "ref")))){
								$g = igk_createxmlnode("return");
								if ($cond1)
									$g["type"] = $v->ReturnType;
								if ($cond2){
									$g["refout"] = "true";
								}
								$out[]= $g->render($op);
							}
							foreach($out as $txt){
								$t = igk_createtextnode($txt);					 
								$m->add($t);//Text($out);
							}
						}
						}
				}
				,"class"=>function($tab,& $tdef, $m, $op=null, $gen_type=null){
					$c =  $m->add("classes");
					$gen_type($tab, $tdef, $op, $c, $gen_type);				
				},
				"interface"=>function($tab,& $tdef, $m, $op=null, $gen_type=null){
					$c =  $m->add("interfaces");
					$gen_type($tab, $tdef, $op, $c, $gen_type);						
				
				},
				"use"=>function($tab, & $tdef,$m, $op=null, $gen_type=null){
					$c =  $m->add("uses");
					// $gen_type($tab, $tdef, $op, $c, $gen_type);	
				}
			
			], $m, $op, function($tab, & $tdef, $op, $fc, $gen_type){
					foreach($tab as $k=>$v){
						$m = $fc->add("member");
						$m["name"] = $v->name;
						if ($v->documentation)
							$out =  explode(IGK_LF, $v->documentation);//.IGK_LF;
						else{
							$out= array("<summary>".__("represent")." ".$v->name." ".$v->type."</summary>");
						}
						foreach($out as $txt){
							$t = igk_createtextnode($txt);					 
							$m->add($t); 
						}
						
						if (($v->type =="class") ||($v->type =="interface")){
						
							if(isset($v->{"extends"})){
								foreach($v->{"extends"} as $mt){
									// $m->add("extend")->Content = $mt;
									$t = igk_createtextnode("<extend>".$mt."</extend>");
									// $m->add("implement")->Content = $mt;
									$m->add($t);
								}
							}
							if(isset($v->{"implements"})){
								foreach($v->{"implements"} as $mt){
									$t = igk_createtextnode("<implement>".$mt."</implement>");
									// $m->add("implement")->Content = $mt;
									$m->add($t);//"implement")->Content = $mt;
								}
							}
						}
						
						if ($v->definitions){
							array_push( $tdef, [$v->definitions, $m, $op, $gen_type]); 
						}
					}
			});
			
			igk_io_w2file($f, igk_xml_header()."\n".$xml_doc->render((object)
			array(
			"Indent"=>1,
			"mustclosetag"=>$op->mustclosetag
			)
			));
			
		};
	}

	if ($ctab = igk_treat_ogetv($command, "defListener")) {
		foreach($ctab as $k=>$v){
			$options->endDefinitionListener[] = $v;
		}
	} 
	
	//
	//TODO: handle string value: is constant way
	//
if (isset($options->command->fileOffset)){
	$options->lineNumber = $options->command->fileOffset;
	
	if (isset($options->command->forcePhp) && $options->command->forcePhp){
		igk_treat_set_context($options, "global", 0);
		igk_treat_append($options, "<?php", 0);
	}
}

$mp = igk_treat_source($source, null,null, $options);

$base = function ($out, $option){
	//return $out;
	if (!empty($option->data)){
		
		igk_wln("cDepth: ".$option->conditionDepth);
		igk_wln("bDepth: ".$option->bracketDepth);
		igk_wln("oDepth: ".$option->openHook);
		igk_wln("context:".$option->context);
		igk_wln("context:".$option->context);
		if (is_object($option->toread))
			igk_wln($option->toread->type.":".$option->toread->name."<!>".$option->tag);
		else if ($option->toread){
			igk_wln($option->toread);
		}
		// igk_wln($option->debugStart);
		// constext
		while($option->context && ($option->context !="global")){
			igk_treat_restore_context($option);
			if ($option->context=="html"){
				igk_wln_e("error html");
			}
			igk_wln("+context:".$option->context ." = ".$option->tag);
		}
		igk_wln_e("some: error: data is not empty:". $option->data);
	}else{
		
		if(isset($option->endDefinitionListener)){			
			foreach($option->endDefinitionListener as $fallback){
				$fallback($option->definitions, $option->command);
			}
		}
		
		if (igk_getv($option->command, "noTreat")!=1){
			$def = "";
			
				if (!empty((array)$option->definitions)){
					$def = igk_treat_outdef($option->definitions, $option);
				}else {
					if($option->command){
						$def = igk_treat_getfileheader($option, basename($option->command->inputFile)); 
					}
				}


			$regx = "/^\<\\?(php)?(\\s*|$)/";
			$s = "";
			$lf = (empty($option->LF)? $option->LF : IGK_LF);
			
			if (preg_match($regx, $out)){
				//start with php script : 
				$s = "<?php";
				$out = preg_replace($regx, "", $out);
				$s .= $lf.$def.$out;
			}else{
				 $option->noFileDesc = 1; 
				if (($p = igk_getv($option, 'startCGIOffSet'))>0){
					$s .= substr($out, 0, $p).$lf.$def.substr($out, $p);
				}else 
					$s .= $out.$lf.$def;
			}			
			return $s;
		}else{
			return implode($option->LF, $option->source);
		}
	}	
	return $out;
};//*/,null, $options);
//*/

if (igk_getv($command, "verbose", 0)==1){ 
	if (strlen($mp)<250000){
		igk_wln("output:\n".$mp);
	}
}



$c = $command->outFile;
if (isset($c)){
	igk_io_w2file($c, $mp);
	if (igk_getv($command, "noCheck")!=1){
		// igk_wln("saving....".$c);
		if (file_exists($c) && is_file($c)){
			exec("php -l ".realpath($c)." 2> NUL", $bc, $o);
			if ($o!=0){
				igk_wln($bc);	
				igk_wln_e("checking failed: ".$c);
			}
		}else{
			igk_wln("not file: ".$c);
		}
		// igk_wln_e("base: :::-----");
	}
}



}

igk_treat_reg_command("-local", function($v, $command, $c){	 
	$command->{"exec"} = function($command){ 
		$command->inputFile = __FILE__;
		$command->outFile =igk_io_dir(
		isset($command->outFile)?$command->outFile:
		(isset($command->outDir)? $command->outDir."/".basename(__FILE__) : 
		dirname(__FILE__)."/".basename(__FILE__).".out.php"));
		igk_treat_filecommand($command);
	};	
}, "treat current file. and output formatted");


igk_treat_reg_command("-f,--file", function($v, $command, $c){	 
	if ($command->waitForNextEntryFlag){
		$v = igk_io_expand_path($v);
		if (!file_exists($v)){
			igk_wln_e("\e[0;31mdanger\e[0m file not exists");
		}
		$command->inputFile = $v;
		
		
		// igk_debug_wln("input file : ".$v);
	
		if ($command->{"exec"} == null){
		$command->{"exec"} = function($command){ 
		
			$command->outFile =igk_io_dir(
				isset($command->outFile)?$command->outFile:
				(isset($command->outDir)? $command->outDir."/".basename($command->inputFile ) : 
				dirname(__FILE__)."/".basename($command->inputFile ).".out.php"));
				igk_treat_filecommand($command);
			};	
		}
	}
	$command->waitForNextEntryFlag = 1;
}, "set input file. usage -f|--file [filepath]");


igk_treat_reg_command("-o,--outDir", function($v, $command, $c){	 
	if ($command->waitForNextEntryFlag){
		$command->outDir = igk_io_expand_path($v);
		igk_debug_wln("output dir: ".$v);
	}
	$command->waitForNextEntryFlag = 1;
}, "set output directory file. usage -o|--outDir [dirpath]");


igk_treat_reg_command("-of,--outFile", function($v, $command, $c){	 
	if ($command->waitForNextEntryFlag){
		$command->outFile = igk_io_expand_path($v);
		igk_debug_wln("output file: ".$v);
	}
	$command->waitForNextEntryFlag = 1;
}, "set output file. use with -local");

 
if (defined("ARMONIC_TEST") && defined("ARMONIC_DATA_FILE") && file_exists(ARMONIC_DATA_FILE)){
igk_treat_reg_command("-data", function($v, $command, $c){	 
	$command->{"exec"} = function($command){ 
		igk_treat_bind_data($command);
	};	
}, "Test data.php file library");

}
function igk_treat_get_ignore_regex($command){
	$h = null;
		//build ignore list
		if(isset($command->ignorePattern)){
			$h = $command->ignorePattern;
		}
		else{
		
			if (igk_getv($command, "noGit")==1){
				$h[] = ".git";
			}
			if (igk_getv($command, "noVSCode")==1){
				$h[] = ".vscode";
			}
		}
		
		if ($h){
			if (is_array($h)){
				$h = implode("|", $h);
			}
			$h = str_replace("/","\\/", $h);
			$h = str_replace(".","\.", $h);
			$h = "/(".$h.")$/";
		}
		return $h;
}


if (file_exists('d:\wwwroot\igkdev\Lib\igk\igk_framework.php')){
igk_treat_reg_command("-igklib", function($v, $command, $c){	 
	$command->{"exec"} = function($command){ 
		if (!isset($command->outDir)){
			$command->outDir = "d:/temp/dist";
		};
		ini_set("max_execution_time", 0);
		$dir = igk_io_dir(IGK_LIB_DIR);
		$sdir = igk_io_dir($command->outDir);
		$ln = strlen(dirname(dirname($dir)));
		$ignore_dir = igk_treat_get_ignore_regex($command);	
		
		
		foreach(igk_io_getfiles($dir, IGK_ALL_REGEX, true, $ignore_dir) as $file){			
			$outfile = igk_io_dir($sdir.substr($file,$ln));
			igk_ewln("\e[0;31mfile:\e[0m".$file);
			if (preg_match("/\.(pinc|ph(p|tml))$/", $file)){
				
				$command->inputFile = $file;
				$command->outFile = $outfile;				
				igk_treat_filecommand($command);			
				
			} else if (preg_match("/\.gkds$/", $file)){
				//ignore files
				continue;
			} else {
				// copy files to output
				igk_io_w2file($outfile, file_get_contents($file));
			}
		}
		
	};	
}, "Treat all igk_framework library and generate source files to output folder");

}

igk_treat_reg_command("-d, --inputDir", function($v, $command, $c){
	if ($command->waitForNextEntryFlag){
		$v = igk_io_expand_path($v);		
		$command->inDir = $v;
		if (!is_dir($v)){
			igk_wln_e("error", "Input directory: [".$v."] does not exists");	
		}
		
		$command->{"exec"} = function($command){ 
		 if(!isset($command->outDir)){
			igk_die("outDir not set"); 
		 }
		ini_set("max_execution_time", 0);
		$dir = $command->inDir;
		$sdir = igk_io_dir($command->outDir);
		$ln = strlen($dir);
		$ifolder = null;
		$ignore_dir = igk_treat_get_ignore_regex($command);	
		$v_treatfc = function($file){
			return preg_match("/\.(pinc|ph(p|tml))$/", $file);
		};
		if ($command->ignoreDirs){
			$v_treatfc = function($file)use($command){
				$c = $command->ignoreDirs;
				if (preg_match("/\.(pinc|ph(p|tml))$/", $file)){
					foreach($c as $v){
						// igk_wln_e("check ".$file);
						
						if (strstr($file, $v)){
							// igk_wln_e("cancel treatment");
							return false;
						}
					}
					return true;
				}
				return false;
			};
		}
		
		foreach(igk_io_getfiles($dir, "/(.)+/", true, $ignore_dir)  as $file){
			
			// if(preg_match("/(^\.|\.vscode|\.git)/", basename(dirname($file)))){
				// igk_wln("ignore folder file : ".$file);
				// continue;
			// }
			
			$outfile = igk_io_dir($sdir.substr($file,$ln));
			igk_ewln(
				igk_environment()->cmdColors["Green"]."File: ".
				igk_environment()->cmdColors["No"]. "-> ".
				$file);
			if ($v_treatfc($file)){ //preg_match("/\.(pinc|ph(p|tml))$/", $file)){
				
				if(strpos($file," ")!==false){
					// $file = str_replace(" ", "\ ", $file);
				}
				igk_wln("new file :".$file);
				$command->inputFile = $file;
				$command->outFile = $outfile;				
				igk_treat_filecommand($command);			
				
			} else if (preg_match("/\.gkds$/", $file)){
				//ignore files
				continue;
			} else {
				// copy files to output
				igk_io_w2file($outfile, file_get_contents($file));
			}
		}
		
	};	
		
	}
	$command->waitForNextEntryFlag = 1;
	
	
}, "set input directory");

igk_treat_reg_command("--gen-xmldoc", function($v, $t, $c){
	$t->genxmldoc = 1;
}, "active xml documentation generation");

igk_treat_reg_command("--gen-xmldoc-od", function($v, $command, $c){
	if ($command->waitForNextEntryFlag){
		$v = igk_io_expand_path($v);		
		$command->xmlOutDir = $v;
	}
	$command->waitForNextEntryFlag = 1;
}, "set xml documentation output directory");

function igk_treat_render_data($tab, $title=null, & $r=0 ){
			 
			$o = "";
			
			foreach($tab as $k){
				if ($title==null){
					$title = $k->type."s:";
				}
				$o .=($k->type." ".$k->name." ").IGK_LF;
				$r++;
			}
			if ($r>0){
				igk_wln($title);
				igk_wln($o);
			}
		}	
		

function igk_treat_update_def($m, $type, $r){
	if (isset($m->reports)){
		if (!isset($m->reports[$type])){
			$m->reports[$type] = $r;
		}else {
			$m->reports[$type] += $r;
		}
	}
}
	
	
igk_treat_reg_command("--list", function($v, $command, $c){
		if(!isset($command->storage["list"])){
		
		
		$command->storage["list"]=1;
		
		$command->defListener[] = function($def, $command){
			ob_start();
			igk_treat_generator($def, array(
			"function"=>function($tab, &$tref, $m){
				igk_treat_render_data($tab,"Functions:", $r);
				//$r= 0;
				// $o = "";
				// foreach($tab as $k){
					// $o.= ($k->type." ".$k->name).IGK_LF;
					// $r++;
				// }
				// if ($r>0){
					// igk_wln("functions:");
					// igk_wln($o);
				// }
				igk_treat_update_def($m, 'function', $r);
			},			
			"class"=>function($tab, &$tref, $m){
				igk_treat_render_data($tab,"Classes:", $r);				
				// $r = 0;
				// $o = "";
				// foreach($tab as $k){
					// $o .= $k->type." ".$k->name.IGK_LF;
					// $r++;
				// }
				// if ($r>0){
					// igk_wln("classes:");
					// igk_wln($o);
				// }
				igk_treat_update_def($m, 'class', $r);
			},
			"interface"=>function($tab, &$tref, $m){ 
				igk_treat_render_data($tab,"Interfaces:", $r); 
			},
			"trait"=>function($tab, &$tref, $m){
				igk_treat_render_data($tab,"Traits:", $r);	
			}, "global"=>function($tab, & $tref, $m){
				//+ treat global definition
				$r = 0;
				$o = "";
				$rtab = array();
				foreach($tab as $k=>$v){
					// igk_wl("[".$v->type.":".$v->line."]\n".
					$rtab[$v->type][] = $v->src;
					$o .= $v->src;
					$r++;
				}
				if ($r>0){
					if (count($rtab["define"])){
						igk_wln("Constants:");
						igk_wln(implode("", $rtab["define"]));
					}
				}
				// igk_wln_e("treat global");
			}
		), $command);
			$s = ob_get_contents();
			ob_end_clean();
			if (!empty($s)){
				igk_wln("[".$command->inputFile."]");
				igk_wln($s); 
			}
	};
	}
}, "list all detected items types");

igk_treat_reg_command("--verbose", function($v, $t, $c){
	$t->verbose = 1;
}, "activate verbosity");
igk_treat_reg_command("--debug", function($v, $t, $c){
	$t->debug = 1;
}, "activate or not debug ");

igk_treat_reg_command("--no-output", function($v, $t, $c){
	$t->noRenderOutput = 1;
}, "disable rendering output");


igk_treat_reg_command("--no-definehandle", function($v, $t, $c){
	$t->noDefineHandle = 1;
}, "disable define - global handle ");



igk_treat_reg_command("--no-treat", function($v, $t, $c){
	$t->noTreat = 1;
}, "disable source code treatment");


igk_treat_reg_command("--no-check", function($v, $t, $c){
	$t->noCheck = 1;
}, "disable source code syntax checking");


igk_treat_reg_command("--no-git", function($v, $t, $c){
	$t->noGit = 1;
}, "ignore git folder for treatment");

igk_treat_reg_command("--no-vscode", function($v, $t, $c){
	$t->noVSCode = 1;
}, "ignore vscode folder for treatment");

igk_treat_reg_command("--single-file-per-class", function($v, $t, $c){
	$t->singleFilePerClass = 1;
}, "for armonic to generate single file per class or interface");


igk_treat_reg_command("--single-file-output", function($v, $t, $c){
	if ($t->waitForNextEntryFlag){
		$t->singleFileOutput = $v;
	}
	$t->waitForNextEntryFlag = 1;
}, "directory for single file output");


igk_treat_reg_command("--ignore-pattern", function($v, $command, $c){
	// igk_wln_e("define....".$v);
	if ($command->waitForNextEntryFlag){
		$command->ignorePattern = $v;
	}
	$command->waitForNextEntryFlag = 1;
	
	
}, "ignore vscode folder for treatment");


igk_treat_reg_command("--no-hiddenfolder", function($v, $t, $c){
	$t->noHiddenFolder = 1;
}, "ignore vscode folder for treatment");


igk_treat_reg_command("--ignore-dir", function($v, $command, $c){
	// igk_wln_e("define....".$v);
	if ($command->waitForNextEntryFlag){
		$v = explode(";", igk_html_uri(igk_io_expand_path($v)));
		
		$command->ignoreDirs = $v;
		
	}
	$command->waitForNextEntryFlag = 1;
}, "list of semi-column separated directory path that must be ignored for treatment");



igk_treat_reg_command("--max-arraylength", function($v, $command, $c){
	// igk_wln_e("define....".$v);
	if ($command->waitForNextEntryFlag){
		$v = intval($v);
		// igk_wln_e("file : ".$v);
		if ($v > 0)
			$command->maxArrayLength = $v; 
		
	}
	$command->waitForNextEntryFlag = 1;
}, "Maximum array definition for an array. [number]");


igk_treat_reg_command("--def", function($v, $command, $c){
	// igk_wln_e("define....".$v);
	if ($command->waitForNextEntryFlag){
		$v = igk_io_expand_path($v);
		// igk_wln_e("file : ".$v);
		if (file_exists($v))
			$command->descriptionHeaderFile = $v;
		
	}
	$command->waitForNextEntryFlag = 1;
}, "set definition header file");

igk_treat_reg_command("--offset", function($v, $command, $c){
	if ($command->waitForNextEntryFlag){
		$v = intval($v);
		$command->fileOffset = $v;		
	}
	$command->waitForNextEntryFlag = 1;
}, "set current file line offset");
igk_treat_reg_command("--offsetMode", function($v, $command, $c){
	if ($command->waitForNextEntryFlag){
		if ($v=="php"){
			$command->forcePhp = 1;
		}		
	}
	$command->waitForNextEntryFlag = 1;
}, "set php mode offset. allowed value is (php). if not specified mode is detected");

igk_treat_reg_command("--forceFileHeader", function($v, $command, $c){
	$command->forceFileHeader = 1;
}, "always use file for file description. note the file: tag will be replaced with current file");


igk_treat_reg_command("--allowDocBlocking", function($v, $command, $c){
	$command->allowDocBlocking = 1; 
}, "allow document php Blocking");

igk_treat_reg_command("--spaceAffectation", function($v, $command, $c){
	$command->allowSpaceAffectation = 1; 
}, "allow space for '=' operator");

igk_treat_reg_command("--no-vargroup", function($v, $command, $c){
	$command->noVarGroup = 1; 
}, "disable variable grouping");

igk_treat_reg_command("--no-filedesc", function($v, $command, $c){
	$command->noFileDesc = 1; 
}, "disable file description");

igk_treat_reg_command("--multi-linevar", function($v, $command, $c){
	$command->multilineVars = 1; 
}, "on variable grouping - force to write one variable per line. variable grouping is the defaultmode.");


igk_treat_reg_command("--text-lineConcatenation", function($v, $command, $c){
	$command->textMultilineConcatenation = 1; 
}, "concatenate line segment in multiline.");

igk_treat_reg_command("--no-xmlDoc", function($v, $command, $c){
	$command->noXmlDoc = 1; 
}, "disable auto xml documentation.");

igk_treat_reg_command("--leave-comment", function($v, $command, $c){
	$command->leaveComment = 1; 
}, "do not remove comment");


igk_treat_reg_command("-json-definition", function($v, $command, $c){
$command->{"exec"} = function($command){
	// igk_wln("render json definition");
	// $data = [];
	$command->noOutput = 1;
	$data = igk_treat_source_expression(null);
	foreach($data as $k=>$v){
		$v->callback = null;
		$v->priority = 0;
	}
	
	// igk_wln($command);
	echo json_encode($data );
} ;
}, "json definition");

// igk_treat_reg_command("-utest", function($v, $command, $c){
// 	igk_treat_check_command_handle($command);
// 	$command->unitTest = 1; 
// 	$command->{"exec"} = function($command){ 
// 		igk_wln("start unit testing");
// 	};
// 	$command->commandHandle=1;
	
// }, "start unit testing on local data");


function igk_treat_check_command_handle($command, $throw =1){
	if (igk_gettsv($command,"commandHandle")==1){
		if ($throw){
			igk_ewln("\e[0;31m.misconfiguration command\e[0m");
			exit -1;
		}
		return false;
	}
	return true;	
}

// igk_treat_reg_command("--wsdl", function($v, $command, $c){
	// $command->{"exec"} = function(){
			
	// };
// }, "Extract web service definition");
//demo

// $_SERVER["argv"] = explode(" ", " -f D:/wamp/www/igkdev/Lib/igk/igk_framework.php -of ./test/ouput.php --allowDocBlocking");
// $_SERVER["argv"] = explode(" ", " -f ./test/data.php -of ./test/ouput.php --verbose");


if (defined("IGK_TREAT_TESTING"))
	return 0;


$tab = array_slice($_SERVER['argv'], 1);
 

if (count($tab)==0){
	igk_treat_show_usage();
}
else{
	$command = igk_createobj();
	$command->command = $tab;
	$command->{"exec"}= null;
	$command->storage = array(); // function storage
	$command->waitForNextEntryFlag = false;
	
	$gcommand = igk_treat_command();
	$action = null;
	foreach($tab as $k=>$v){
		
		if ($command->waitForNextEntryFlag){
			$action($v, $command, []);
			$command->waitForNextEntryFlag = false;
		}
		$c = explode(":", $v);
		
		if (isset($gcommand[$c[0]]))
		{
			$action = $gcommand[$c[0]];
			$action($v, $command, implode(":", array_slice($c,1)));
		}
	}
	// if reach here execute command 
	igk_treat_execute_command($command);
}
