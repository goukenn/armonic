<?php
// desc: balafon-module : php formatter armonic  
// author: C.A.D BONDJE DOUE
// email: bondje.doue@igkdev.com
// version:1.0
// release: 22/03/2019
// copyright: igkdev @ 2019

if (!defined('IGK_FRAMEWORK')){
	$libfile = dirname(__FILE__)."/../igk/igk_framework.php";
	if (file_exists($libfile)){
		$libfile = realpath($libfile);
	}else
		$libfile = "d://wamp/www/igkdev/Lib/igk/igk_framework.php";
	require_once($libfile);
	igk_display_error(1);
}

define("ARMONIC_INDENT_CHAR", "    ");

/// TASK: append decorator 
/// TASK: append php doc blocker setting 
/// TASK: array dependencie

define("IGK_APP_DIR", dirname(__FILE__));
define("IGK_BASE_DIR", dirname(__FILE__));

define("IGK_TREAT_IDENTIFIER","[_a-zA-Z][_a-zA-Z0-9]*");
define("IGK_TREAT_NS_NAME" ,"((\\\\\\s*)?".IGK_TREAT_IDENTIFIER.")((\\s*\\\\\\s*)".IGK_TREAT_IDENTIFIER.")*");




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
			"offset"=>strlen($base_def)+$start // offset where name . start can be 'for ',' or '='
		);
		// igk_wln("name: ".$name);
		// igk_wln("init offset:".(strlen($base_def)+$start));
	}else{
		igk_wln("target:".$t);
		igk_wln("gt:".$gt);
		igk_wln("base_def:".$base_def);
		// igk_wln("addition:".substr($t, 0, $start));
		// igk_wln("next:".$rgx);
		// igk_wln("conditionDepth:".$m->options->conditionDepth);
		// igk_wln("lineDepth:".$m->options->conditionDepth);	
		// igk_wln(igk_show_trace());
		igk_wln_e("not name : ".$gt . " Line:".$m->options->lineNumber);
	}
}
///<summary>internal use. read modifier value on mode 2</summary>
function igk_treat_modifier_getvalue($base_def, $t, $start, $m){		
	$md = igk_last($m->options->modifierArgs);
	if($md){
		$def = substr($t, 0,$start);
		$def= $base_def.$def;							
		$tb = ltrim(substr(ltrim(substr($def, $md->offset)),1)); // +1 cause of = present 
		$md->value = $tb;
	}
	$m->options->modFlag = 1;
}
		
function igk_treat_handle_modargs(& $t, $start, & $offset, $g, $m){
	// for variable args
	//igk_wln("context: ".$m->options->context . " toread:".$m->options->toread);
	
    // igk_wln("handlemodeargs:".$g."|".$m->options->modFlag . " cdepth:".$m->options->conditionDepth. " modifier:".
	// $m->options->modifier);
	if( ($m->options->toread != "array") && $m->options->modFlag && ($m->options->conditionDepth<=0) && !empty($m->options->modifier)){
		switch($g){
			// case ",": // mod separator
				// handle param
				// igk_wln("mod separator:".$t);
				// break;
			case ",":
			case "=":
				// valid separator
				// igk_wln("mod: ". $g. " ".$m->options->modFlag);
				$base_def = igk_treat_get($m->options);
				
				if ($m->options->modFlag==2){ // for multi variable declaration
					// end read value
					// igk_wln("**************:end get value:".$t);
					igk_treat_modifier_getvalue($base_def, $t, $start, $m);
					return;
				}
				igk_treat_modifier_getname($base_def, $t, $start, $m);
				 
				if ($g == "="){							
					$m->options->modFlag = 2; // 
					// igk_wln("###############wait for reading value: ".$t);
				} 
				$base_def =  igk_treat_get($m->options).substr($t, 0, $start);
				// igk_wln("basedef:1:".$base_def);
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
			"global"=>(object)array(
				"render"=>function($v){
					 return $v->src;
				}
			),
			"use"=>(object)array("sort"=>$name_sort, "render"=>function(){
				return $v->src;
			} )
			,"vars"=>(object)array(
			"doc"=>0,
			"noXmlDoc"=>1,
			"sort"=>function(& $tab){
				$q = $tab["tab"];
				// $tab = $m->options->modifierArgs;
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
							if ($inline_var){
								if (($modifier==-1) || ($modifier!= $v->modifier)){
									if ($modifier!==-1){
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
								if ($v->value!==null)
									$out.= $sp."=".$sp.$v->value;
							 
							}
							else{
								$out .= $indent.$v->modifier." ".$v->name;
								if ($v->value!==null)
									$out.=" =".$v->value;
								$out.=";".IGK_LF;

						}
				
				}
				if ($inline_var){
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
			"function"=>(object)array("sort"=>$name_sort, 
			"autodoc"=>function($v, $indent, $options){
				$t_out = $indent."///<summary>represent ".$v->name." ".$v->type."</summary>".IGK_LF;
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
				
				$tab = igk_getv($def,"filedesc");
				$rpheader = $options->command && igk_getv($options->command, 'forceFileHeader', 0);
				if (!$rpheader && $tab && (count($tab)>0)){			 
					foreach($tab as $k=>$v){ 			
						$out.=  $v;
					}
					$out .= IGK_LF; 
				}else{
					$mark ="";
					if (true){
						$mark = "@";
					}
					// igk_wln("base:::".$mark);
					if($options->command){
					// igk_wln("ok----");
						
						static $defaultHeader = null;
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
								$defaultHeader.="// ".$mark."license: Microsoft MIT License. For more informartion read license.txt".IGK_LF;
								$defaultHeader.="// ".$mark."company: IGKDEV".IGK_LF;
								$defaultHeader.="// ".$mark."mail: bondje.doue@igkdev.com".IGK_LF;
								$defaultHeader.="// ".$mark."url: https://www.igkdev.com".IGK_LF;
							}
						}
						$out.="// ".$mark."file: ".basename($options->command->inputFile).IGK_LF;
						$out.= $defaultHeader.IGK_LF;
						// igk_wln_e("bind default header ".$defaultHeader);
					}
				}
			}
			// igk_wln("render def .....". $def_file);
			$def_file = 1;
			//treat: global usage
			// $tab = igk_getv($def,"global");
			// if ($tab){			 
				// foreach($tab as $k=>$v){ 			
					// $out.=  $v->src;
				// }
			// }
			
			///TASK: treat use
			// $tab = igk_getv($def,"use");
			// if ($tab){
				// usort($tab, function($a, $b){
					// return $a->name <=>$b->name;
				// });
				// foreach($tab as $k=>$v){
					// // $indent_c($v);				
					// $out.=  $v->src;
				// }
				// $out.= $options->LF;
			// }
			//treat: vars
			$q = igk_getv($def,"vars");
			if ($q){
				// $tab = $q["tab"];
				// // $tab = $m->options->modifierArgs;
				// usort($tab, function($a, $b){
				// $r = strcmp($a->modifier, $b->modifier);
				// if ($r==0){
					// $r = strcmp($a->name, $b->name);
				// }
				// return $r;
				// });
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
		}
		//treat: function
			// $tab = igk_getv($def,"function");
			// if ($tab){
				// usort($tab, function($a, $b){
					// return $a->name <=>$b->name;			
				// });
				// foreach($tab as $v){
					// $indent_c($v);
					// if ($v->documentation)
							// $out.= igk_str_format_bind($indent."///{0}".IGK_LF, explode(IGK_LF, $v->documentation));//.IGK_LF;
						// else{
							// $out.=$indent."///<summary>represent ".$v->name." ".$v->type."</summary>".IGK_LF;
							// if (!isset($v->readP)){
								// igk_wln($v->src);
								// igk_wln("startline: ".$v->startLine);
								// igk_wln("function parameter not exists : ".$v->name);
							// }
							// if (!igk_getv($options, "noAutoParameter") && isset($v->readP)){
								// foreach($v->readP as $kv=>$vv){
									// $gs ="";
									// $g = igk_createxmlnode("param");
									// $g["name"]=$vv->name;
									// if (isset($vv->default)){
										// $g["default"] = $vv->default;
									// }
									// if (isset($vv->type)){
										// $g["type"] = $vv->type;
									// }
									// if (isset($vv->ref) && $vv->ref){
										// $g["ref"] = "true";
									// }														
									// $out.= $indent ."///".$g->render().IGK_LF;					 
								// }				
							// }
							// if (($cond1=isset($v->ReturnType)) | ($cond2 = (isset($v->options) && igk_getv($v->options, "ref")))){
								// $g = igk_createxmlnode("return");
								// if ($cond1)
									// $g["type"] = $v->ReturnType;
								// if ($cond2){
									// $g["refout"] = "true";
								// }
								// $out.= $indent ."///".$g->render().IGK_LF;
							// }
						// }
					// if (isset($v->attributes)){
							// // $v->attributes
							// $out .= igk_str_format_bind($indent."//{0}".IGK_LF, explode(IGK_LF, $v->attributes));//.IGK_LF;
					// }
					// $out.= igk_treat_render_documentation($options, $v, $indent);			
					// $out.= $v->src;//.$options->LF;
				// }
			// }
			// $tab = igk_getv($def,"interface");
			// if ($tab){
				// usort($tab, function($a, $b){
					// $da = $a->{'@extends'} ?? "";
					// $db = $b->{'@extends'} ?? "";
					// if (($r = ($da <=>$db))==0)
						// return $a->name <=> $b->name;
					// return $r;
				// });
				
				
				// foreach($tab as $k=>$v){
					// $indent_c($v);
						// if ($v->documentation)
							// $out.= igk_str_format_bind($indent."///{0}".IGK_LF, explode(IGK_LF, $v->documentation));//.IGK_LF;
						// else{
							// $out.=$indent."///<summary>represent ".$v->name." interface</summary>".IGK_LF;
						// }
						// $out .= $v->src;
				// }
				
			// }
			// $tab = igk_getv($def,"trait");
			// if ($tab){
				// usort($tab, function($a, $b){ 
						// return $a->name <=> $b->name;
					// });		
					// foreach($tab as $k=>$v){
						// $indent_c($v);
							// if ($v->documentation)
								// $out.= igk_str_format_bind($indent."///{0}".IGK_LF, explode(IGK_LF, $v->documentation));//.IGK_LF;
							// else{
								// $out.=$indent."///<summary>represent ".$v->name." ".$v->type."</summary>".IGK_LF;
							// }
							// $out.= $v->src; //render_source($s, $indent, $v);
							 
					// }
			// }
			// $tab = igk_getv($def,"class");
			// if ($tab){
				// //because class order in file is important class name must be ordered in reversed extends definitions
					// $klist = array();
					// $nlist = array();
					// usort($tab, function($a,$b) use(& $nlist){
						// if (!isset($nlist[$a->name])){
							// $nlist[$a->name] = $a;
						// }
						// if (!isset($nlist[$b->name])){
							// $nlist[$b->name] = $b;
						// }
						// return strcmp($a->name, $b->name);
						
					// });
					// $v_sroot = "/";
					// foreach($tab as $k=>$v){ 
						// $n =$v_sroot;
						// if (isset($v->{'@extends'})){
							// $p = $v->{'@extends'};
							// $n = $v_sroot.trim($v->{'@extends'});
							// $key =$v->name;				
							
							// while($p && isset($nlist[$p])){
								// $key = $p."/".$key;
								// $p = igk_getv($nlist[$p],'@extends');
							// }
							// //igk_wln("key ::: ".$key);
							// $klist[$key] = $v; 
						// }else {
							// $klist[$v->name] = $v;
						// } 
					// } 
					// // igk_wln_e("");
					// $cl = array_keys($klist);
					// sort($cl);
					
					
					// // foreach($cl as $k){
						// // $v = $klist[$k];
						// // $indent_c($v);
						// // if (isset($v->documentation))
							// // $out.= igk_str_format_bind($indent."///{0}".IGK_LF, explode(IGK_LF, $v->documentation));//.IGK_LF;
						// // else{
							// // $out.=$indent."///<summary>represent ".$v->name. " class</summary>".IGK_LF;
						// // }
						// // // igk_wln("ddd----------------");
						// // // igk_wln($v->attributes);
						// // if (isset($v->attributes)){
							// // // $v->attributes
							// // $out .= igk_str_format_bind($indent."//{0}".IGK_LF, explode(IGK_LF, $v->attributes));//.IGK_LF;
						// // }
						// // $out .= $v->src;						
					// // }	
					 
			// }
			
			// $tab = igk_getv($def, "namespace");
			// if (igk_count($tab)>0){		
				// usort($tab, function($a, $b){
					// // $r = ($a->modifier <=> $b->modifier);
					// // if ($r==0){
						// return $a->name <=> $b->name;
					// // }
					// // return $r;
				// }); 
				// foreach($tab as $k=>$v){
					// if ($v->documentation)
						// $out.= igk_str_format_bind($indent."///{0|trim}".IGK_LF, explode(IGK_LF, $v->documentation));//.IGK_LF;
					// else{
						// $out.= $indent."///<summary>represent ".$v->type. ": ".$v->name."</summary>".IGK_LF;
					// }	
					
					 	
					// $out.= $v->src.IGK_LF;
					 
					// if ($v->definitions && (strpos($v->src, "{")===false)){ 
						// array_push($tdef , $v->definitions);
					// } 			
				// } 
				
		// }
	 
			foreach($tlist as $k=>$v){ 
				$tab = igk_getv($def, $k);
				if (!$tab){
					continue;
				}
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
						return $indent."///<summary>represent ".$cv->type. ": ".$cv->name."</summary>".IGK_LF;
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

///<summaryr>set or replace current output</summmary>
///<param name="options">definition options</param>
///<param name="text">new text</param>
function igk_treat_set($options, $t){
	igk_debug_wln("settext :".$t);
	if(empty($options->data) && ($options->mode==0)){
		$g = & $options->output;
	}
	else{
		$g = & $options->data; 
	} 
	$g = $t;
}
function igk_treat_append($options, $t, $indent=1){
	
	// if (igk_env_count(__FUNCTION__)>1){
		// igk_wln_e(igk_show_trace());
		
	// }
	 // if (($t=="\$library=")){
		// igk_wln_e(igk_show_trace());
	 // }
	
		igk_debug_wln("append:".$options->context." :".
		$options->DataLFFlag." indent:".$indent." cDepth:".$options->conditionDepth
		." t:".$t);
		if (!$indent && (igk_getv($options, "depthFlag")==1)){
			$indent = 1; 
			$options->DataLFFlag = 1;
		}
		
	$tab ="";
	$g = 0;
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
	if ($options->DataLFFlag){
		// igk_wln_e(igk_show_trace());
		$g.= $options->LF;
		$options->DataLFFlag=0;
		$indent=1;
	}
						
	if($indent){
		$idx = $options->bracketDepth + $options->offsetDepth;
		if ( $options->depthFlag){ // for the next adding 
			$idx++;
			$options->depthFlag=0;
		}
		//update for array depth level .activate by fonction start block \{
		if ($options->arrayBlockDepthFlag && ($options->arrayDepth>0)){
			$idx+= $options->arrayDepth+1;
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
		
		"offset"=>0,
		"DataLF"=>1,
		"DataLFFlag"=>0, // flag use for append // flow
		"depthFlag"=>0, // flag use for append // flow
		"LF"=>IGK_LF,
		//array management . in php array can be declared as [] or array(); and association array is like definition. armonic must be capable to replace
		// single line definition content with 
		"arrayEntity"=>array(), // list of array definition. (startOffset, endOffset)
		//"arrayFlag"=>0,			//use to idicate that we are on array definition
		"arrayDepth"=>0,		//encaspulate array feel
		"arrayMaxLength"=>60,	// max caracter length of an array
		"arrayBlockDepthFlag"=>0, 
		
		
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

function igk_treat_skip(& $t, $start, & $offset, $m){
	$offset = $start + strlen($m->data[0][0]);
	// igk_wln_e("skipping....:");
	return $t;
}
function igk_treat_source($source,  $callback, $tab=null,& $options=null){
	if (is_string($source)){
		$source = explode("\n", $source);
	}
	$options = $options ?? igk_treat_create_options();
	$tab = $tab ?? igk_treat_source_expression($options);
	$out = & $options->output;
	$offset = & $options->offset;
	$sline = & $options->lineNumber;
	
	// treat source algorithm
	// when tab search index position is lower that all available use it for search
	// 
	
	
	
	$tline = igk_count($source);
	
	$options->totalLines = $tline;
	$options->source = $source;
	$options->{"@automatcher_flag"} = array();
	 
	$flag = 0;
	$autoreset_flag = & $options->{"@automatcher_flag"};
	while($sline < $tline){
		$t = $source[$sline];
		$sline++;
		
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
		//igk_wln("Line: ".$sline);
		$flag = 1;
		$matchFlag = 0;
		$tq = array(rtrim($t)); // remove last trailing space . because we don't know the model no need to remove space before
		$offset = 0;
		$auto_reset_list=["operatorFlag", "mustPasLineFlag"];
		while($t = array_pop($tq))
		{		
			//treat every line
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
						// igk_debug_wln("match ::: ".$v->name. " start:".$start . " t: ".substr($t, 0, 10)."....");
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
				
				
				
				igk_debug_wln("matcher: ".$mlist->matcher->name);
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
///<summary>represent php treat expression</Summary>
function igk_treat_source_expression($options=null){
	$tab = array(); 
	
	static $defExpression = null;
	if ($defExpression==null){
		$defExpression = 1;
		
		function update_array_item(& $q, $t, $start, $m){
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
		
		function start_array($m, $t, $start, $bracket=1){
			// igk_wln_e("start ::: ");
			
			$m->options->arrayDepth++; 
			
			$tbefore = substr($t,0, $start);
			
			// igk_wln("before : ".$tbefore);
			 
			
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
		function end_array($m, & $t, & $start,& $offset){
			// igk_wln("********************************");
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
			update_array_item($q, $t , $start, $m);		
			$lvg = strlen($q_txt)-strlen($q->before);		
			
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
						$outtxt = "[";
						if ($v_cc >1){	
							$outtxt .= "\n".$indentd.implode(",\n".$indentd, $tq)."\n".$indents;
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
		};
			
		
		
		
	}
	
	


	array_unshift($tab, (object)array(
		"name"=>"switchCaseOperatorHandle",
		"mode"=>"*",
		"pattern"=>"/(^|\\s+)(?P<operator>(case|default))(\\s+|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$idx = $m->data["operator"];
			$op_n = $idx[0];
			$space = " ";
			if ($op_n=="default"){
				$space="";
			}
			switch($op_n){
				case "case": //wait
				case "default": //waiting for ":"
					$m->options->switchcaseFlag = 1;
					break;
			}
			
			igk_treat_append($m->options, $op_n.$space);
			$t = substr($t, $start+strlen($m->data[0][0]));
			$offset = 0;
			return $t;		
	}));		
	array_unshift($tab, (object)array(
		"name"=>"controlConditionalHandle",
		"mode"=>"*",
		"pattern"=>"/(^|[^a-zA-Z0-9_ ]|\\s+|)(?P<operator>(for|if|elseif|while|do|switch|foreach|try|finaly|catch|array))\\s*(\(|\{|$)/",
		"callback"=>function(& $t, $start, & $offset, $m){
			$idx = $m->data["operator"];
			// if ($idx[0]=="array"){
				// igk_wln($t);
				// igk_wln($offset);
				// igk_wln($start);
				
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
			switch($idx[0])
			{
				case "do":
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
					// igk_wln_e("array_detection");
					
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
						//$offset = $start + strlen($m->data[0][0])+1;
						
						$offset = 0;
						return $t;
					} 
					start_array($m, $t, $start, 0);// wait for "("
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
			$m->options->toread = $idx[0];
			// igk_wln("end condition depth:1");
			// $m->options->conditionDepth = 0;

			
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
			$offset = $start+strlen($m->data[0][0]);
			$ln = strlen($m->data[0][0]);
			$g = preg_replace("/\\s+/", "", $m->data[0][0]);
			$ch = $g;
			$gx = empty(ltrim(substr($t, 0, $start)));
			// igk_wln("what: ".$start);
			// igk_debug_wln("detect: ".$g);
			
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
						igk_debug_wln("&:".$t . " start:".$start);
						if (($m->options->context == "parameterReading")
						&& 	($m->options->toread) && 
						isset($m->options->toread->readP)){
						 
							if ($start >0){
								$g =" ".$g." ";
								break;
							}
						}
						$g = "& ";
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
							update_array_item($q, null, -strlen($g), $m);
							 
						}
					break; 
				
					case ":":
						if ($m->options->switchcaseFlag){
							$m->options->switchcaseFlag = 0;							
							igk_treat_append($m->options, $v.$g, 0);
							$m->options->DataLFFlag=1;
							$h = 1;							
						}
						$g = $g." ";
						break;
						case "=": // affectation must be inlined	
						
						// $m->options->java["allowSpaceAffectation"] = "999";
						if (igk_gettsv($m->options, "command/allowSpaceAffectation", null)){
							$g = " ".$g." ";						
						}
						break;
					case "->{": //litterla bracket support
						$m->options->bracketVarFlag = 1;
						if ($m->options->DataLFFlag){
							igk_treat_append($m->options, $v.$g, 1);
							$h= 1;
						}						
						$m->options->DataLFFlag = 0;
						$m->options->bracketDepth++;
						break;
					case "->":
						$m->options->objectPointerFlag = 1;
						break;
					case "<=":
					case ">=":
					case "++":
					case "--":
					case "::": 
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
					default:
						$g = " ".$g." ";
						break;
				}
		
				if (!$h)
				igk_treat_append($m->options, $v.$g, $v_indent);
				
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
				$m->options->bracketDepth--;
				$lf = 1;
				if ($m->options->bracketVarFlag){
					$m->options->bracketVarFlag = 0; //reset
					$lf = 0;
				}
				if ($m->options->FormatText){
						$g  = trim(substr($t, 0, $start));
						
						if (!empty($g)){
							igk_treat_append($m->options, $g."}", $lf);							
						}else{
							igk_treat_append($m->options, "}", $lf); 
						}
				}else{
					igk_treat_append($m->options, "}");
				}
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
				$m->options->DataLFFlag = $lf;
				
				if ($m->options->arrayDepth>0){
					$q = $m->options->arrayEntity[count($m->options->arrayEntity)- 1];
					if (!isset($q->arrayBlockDepthFlag))
						$q->arrayBlockDepthFlag=1;						
					$q->arrayBlockDepthFlag--;
					if ($q->arrayBlockDepthFlag==0)
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
			if (!isset($m->options->openHook))
				$m->options->openHook = 0;
			
			if (!isset($m->options->arrayDepth)){
				$m->options->arrayDepth = 0;
			}
			if ($d=="["){
				$m->options->conditionDepth++;
				$m->options->openHook++;				
				start_array($m, $t, $start);
			}else{
				$m->options->conditionDepth--;
				$m->options->openHook--;
				end_array($m, $t, $start, $offset); 
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
			
			if ($d==")"){
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
			if($m->options->conditionDepth<0){
				igk_wln_e("something wrong....".$m->options->conditionDepth. " line:".$m->options->lineNumber);
			}
			// igk_wln($m->options->lineNumber.":{$d}:****". $m->options->conditionDepth);
			// igk_wln("handle char: ".$d. " context:".
			// $m->options->context."-------".$m->options->toread."------------");
			// -------------------------------------------
			igk_treat_handle_char($t, $start, $offset, $d, $m);
			
			// if ($d==")"){
				// igk_wln("content : ".$m->options->context. " cd:".$m->options->conditionDepth);
			// }
			switch($m->options->context){				
				case "controlConditionalHandle":
					$depthf = 1;
					if ($m->options->toread=="array"){
						$q = $m->options->arrayEntity[count($m->options->arrayEntity)-1]; // $m->options->
						 
						if ($q->litteral){ //the entity and element to read match 
							
								if ($d==")"){
									if (!isset($q->hookDepth)){
										igk_wln_e("ttt: hookDepth not define".$t);
									}
									else{
										$q->hookDepth--;
										
										if($q->hookDepth<=0){	
											end_array($m, $t, $start, $offset); 
											$depthf=0; 
										//	igk_treat_restore_context($m->options, 1);
											
											igk_treat_restore_context($m->options, 1);
											$s = substr($t, 0, $start+1);	 					
											igk_treat_append($m->options, $s, 0);
											$t = ltrim(substr($t, $offset));
											$offset = 0; 
						
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
 
			// substr($t, $offset));
			return $t;
		}
	));
	array_unshift($tab, (object)array(
		"name"=>"endInstruction",
		"mode"=>"*",
		"pattern"=>"/\\s*(;)\\s*/",
		"callback"=>function(& $t, $start, & $offset, $m){
			
			
			$d = $m->data[0][0];
			$s = preg_replace("/\\s*/", "", $d);
			
			$ob_pointer = $m->options->objectPointerFlag;
			
			
			if ($m->options->objectPointerFlag){//reset object pointer
				$m->options->objectPointerFlag = 0;
			}
		 	// igk_wln("endinstruct:".$t);
			if (($toread = $m->options->toread) && is_object($toread) && ($toread->mode<4) && 
				($fc = $toread->endTreat)){
					$s = rtrim(igk_treat_get($m->options).substr($t, 0, $start).$s).$m->options->LF;
					// igk_wln_e("finish instruct::::: ".$s);
					$fc($s, $m->options, $toread);
					$t = substr($t, $start+ strlen($d));
					$offset = 0;
					return $t;
				}
			switch($m->options->context){
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
					// igk_wln_e("finish global constant:".$deff);
					$t = substr($t, $start+ strlen($d));
					$offset = 0;
					break;
				default:
					$gg = trim(substr($t, 0, $start)).$s;
					
					$h = 0;
					$def = igk_treat_get($m->options);
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
						if (is_object($m->options->toread) && igk_treat_handle_modifier($m->options))
						{ 
							// $o = $def.$gg;
							// igk_wln("out:".$o);
							// igk_wln("out2: ".$def); 
							// var_dump($m->options->modifierArgs);
							$h =1;
						}
						// igk_wln($m->options->context);
						igk_treat_reset_modifier($m->options);
						// igk_exit();
						
					}
					if (!$h){						
						if ($m->options->endMarkerFlag && isset($m->options->definitions->lastTreat)){
							$m->options->endMarkerFlag = 0;
							$ls = $m->options->definitions->lastTreat;
							if (!isset($ls->endMarker) && preg_match("/(use|function|define)/", $ls->type)){
								//anonymous type must send ; to output
								if (($ls->type == "function") && (igk_getv($ls, "isanonymous")==1)){
									$cc= trim($gg);
									if ($cc !==";"){
										igk_wln_e("not valid end marker:".$cc. " ".$m->options->lineNumber);
									}				
									$cbk = $m->options->DataLFFlag;
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
							$cbk = $m->options->DataLFFlag;	
							$indent = 0;
							 if (igk_getv($m->options, "endDoWhileMarkerFlag")==1){
								// $indent = 0;
								$m->options->endDoWhileMarkerFlag = 0;
								// $m->options->DataLFFlag=0;
								$m->options->depthFlag = 0;
							}
							igk_treat_append($m->options, trim($gg), $indent);
							$m->options->DataLFFlag = 1;
						}
					}
					$t = substr($t, $start+ strlen($d));					
					$offset = 0;
					// igk_wln("????????????t:".$t." : ".$offset. " ".$m->options->DataLFFlag);
				break;
			}			
			return $t;
		}
	));
	
	
	array_unshift($tab, (object)array(
		"name"=>"uncollapsestring",
		"mode"=>'*', //available mode
		"pattern"=>"/(\"|')/i",
		"callback"=>function(& $t, $start, & $offset, $m){
			$lis = $start;
			$ch = $t[$start];
			$s = "";
			
			if ($ch=="'"){
				//special case for multi line
				$ln = $m->options->lineNumber;
				$tln = $m->options->totalLines;
				while( ($ln < $tln) && (($pos = strpos($t, "'", $start+1)) !== false)){
					$s .= substr($t, $start).$m->options->LF;
					$ln++;
				}
				
				
			} else { 			
				$s = igk_str_read_brank($t, $lis, $ch, $ch, null, 1);			
			}
			$offset = $lis+1;
			$m->options->reading = $s; //get last reading char
			
		igk_wln($t);	
			igk_wln_e($s);
			
			if($m->options->context!='globalConstant'){
				$m->options->stringReading[] = (object)array("data"=>$s, "line"=>$m->options->lineNumber);
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
			// igk_wln_e("base");
			
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
		
		igk_treat_set_context($m->options, "modifierReading", $m->options->mode, array("data", "DataLFFlag"));
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
			/// reset modifier declaration			
			igk_treat_reset_modifier($m->options);
			if(igk_treat_handle_use($m, "function")){
				$t = ltrim(substr($t, $type_offset));//$start + strlen($type);
				$offset = 0; 
				return $t;
			}
			
			
			
			// igk_wln($totreat);
			// if (strpos("class", $type)!== false){
				// // in
				// $skip = 0;
				// // igk_wln("finish:********SSS*** ".( $m->options->bracketDepth));
				// // igk_wln("-------------t:{$type}--------:".$m->options->bracketDepth. " ?? ".($totreat!=null));//finith:********SSS***");
				// // var_dump($m->options->toread); //"-------------t:{$type}--------:".$m->options->bracketDepth);//finith:********SSS***");
				// if ($totreat){
					// $skip =  ($m->options->bracketDepth-1)>0;
					// // igk_wln("indentLevel:MMMMM: ".
					// // $m->options->bracketDepth." VS ".
					// // $totreat->indentLevel. " ::: ".$skip);
				// }else{
					// $skip = $m->options->bracketDepth > 0;
				// }
				// if ($skip){
					// igk_wln("\e[0;41mwarning\e[0m ".$type." is embeded in bracket out. Line: ".$m->options->lineNumber);
					// // igk_wln("modifier: ".$modifier);
					// // igk_wln_e($totreat);
					// // igk_wln_e("gg: ".$t);
					// // $offset = $start + $type_offset;
					// $m->options->inlineFunctionReadingFlag = $m->options->bracketDepth;
				
					// $m->options->DataLFFlag = 1;
					// if ($modifier){
						// igk_treat_append($m->options, $modifier. " ", 1);
						// $m->options->DataLFFlag = 0;
						// $t =  igk_treat_skip($t, $start, $offset, $m);
						// // igk_wln("finith:********SSS************".$t);
						// return $t;
					// }else{
						// igk_treat_append($m->options, ltrim(substr($t, 0, $type_offset)). " ", 1);
						// $t = substr($t, $type_offset);
						// // igk_wln("finith:********************".$t);
						
						// $offset = 0;
						// return $t;
						
					// }
				
					// return $t;
				// }
			// }
			
			// igk_treat_reset_modifier($m->options);
			
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
			
			if ($opFlag = $m->options->operatorFlag){
				// igk_wln("operator flag:".$opFlag);//.":: type:".$type."| match:".preg_match("/(=|=\>|,|\?\?)/", $opFlag));
				if ( ! (($type=="function") && preg_match("/(=|=\>|,|\?\?)/", $opFlag))
					|| (preg_match("/(::|-\>)/", $opFlag))
				)
				 // probably affectation of anonymous function
				{
					igk_wln("\e[0;41mwarning\e[0m ignore pointer: type:".$type.
					" t:".$t.
					" Line:".$m->options->lineNumber.
					" opFlag:".$opFlag);			 
					$offset = $start+ strlen($m->data[0][0]);
					return $t;
				}
			}
			// start reading
		
			if ($type=="use"){
				igk_treat_reset_modifier($m->options);
				// igk_wln($m->options->toread);
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
					
					
					$u->startTreat=function(){
						igk_wln_e("operatation not allowed");		
					};
					$u->endTreat=function($src, $options, $totreat){
						igk_wln_e("operatation not allowed");						
					};
					$u->handleChar= function(& $t, $start, & $offset, $ch, $m){
						$u = $m->options->toread;
						// igk_wln("handle char use:******************".$ch);
						if (($ch==")") && ($m->options->conditionDepth<=0)){
							
							igk_treat_handle_funcparam($ch, $t, $start, $m, $cancel);
							
							$endef = igk_treat_get($m->options);//.substr($t,0 , $start);
							//igk_wln("use:".$ch." finish parameter reading:".$endef); 
							igk_treat_restore_context($m->options);// exit use parameter readingmode to definitio
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
					if (preg_match("/".$type."\\s+(?P<name>(".IGK_TREAT_NS_NAME.")(\\\\)?)(\\s+as\\s+(?P<as>".IGK_TREAT_IDENTIFIER."))?/", $def, $ctab)){
						
						$name = $ctab["name"];
						$pos = strpos($def, $name);
						$totreat->name = preg_replace("/\\s+/", "", $name);
						$def = substr($def, 0, $pos).$totreat->name.substr($def, $pos+strlen($name)); 
						
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
									$e->definitions["as"] = $gtg["as"][$i];
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
			$totreat->definition = null;
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
				// igk_wln("end char********************".$ch." ".$cmode." ".$totreat->type);
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
										if ($ch=="("){
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
											 // igk_wln("sdef:".$sdef);
											 // igk_wln("def:".$def);
											 // igk_wln("def: ".$op_lx);
											//controlConditionalHandle
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
				// igk_wln_e("endTreat:\n".$src);		
				 		
				igk_treat_restore_context($options);
				$bg = $options->toread;
				$options->endMarkerFlag = 0;
				$totreat->handleChar=null;
				$totreat->mode=4; //mode 4 
				$skip = 0;
				switch($totreat->type){
					case 'function':
					// igk_wln("is ana:::?".(isset($totreat->isanonymous) && ($totreat->isanonymous==1)));
					if (isset($totreat->isanonymous) && ($totreat->isanonymous==1)){					
							//formating isanonymous
							$src = preg_replace("/function\\s+\(/", "function(", $src);
							igk_treat_append($options, trim($src), 0);
// igk_wln_e("appendin::::::".$src);							
							if ($options->conditionDepth<=0){
								$options->definitions->lastTreat = $totreat;
								$options->endMarkerFlag = 1;
							}
							$options->DataLFFlag = 1;
							return;
					}
					// if (!$totreat->parent){
						// igk_wln("form at:");
						///TASK: Name formatted to lower case
						// $p = strpos($src, "(");
						// $src = $totreat->def ." ";//.substr($src, $p+1);
					
					// }
	//				$skip = 0;
					
					if ($totreat->parent){
						$skip =  ($options->bracketDepth-1)>0; 
					}else{
						$skip = $options->bracketDepth > 0;
					}
					
					// igk_wln_e("need to skip: ".$skip);
					// if ($skip){
						// // remove 
						// $options->DataLFFlag = 1;
						// igk_treat_append($options, ltrim($src), 0);
						// $options->DataLFFlag = 1;
						// // igk_treat_removedef($m->options, $totreat);
						// //unset($options->parent->definitions[$totreat->index]);						
						// return;
					// }
					
					//igk_wln_e("finish function:\n".$src);
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
		
				
				// // if (($totreat->type=="function") && isset($totreat->isanonymous) && ($totreat->isanonymous==1)){
					
					// formating isanonymous
					// // $src = preg_replace("/function \(/", "function(", $src);
					// // igk_treat_append($options, trim($src), 0);	
					// // if ($options->conditionDepth<=0){
						// // $options->definitions->lastTreat = $totreat;
						// // $options->endMarkerFlag = 1;
					// // }
					// // $options->DataLFFlag = 1;
					// // return;
				// // }
				// if (($totreat->type=="function") && !$totreat->parent){
					// ///TASK: Name formatted to lower case
					// $p = strpos($src, "(");
					// $src = $totreat->def.substr($src, $p+1);
				
				// }
						
				// if ($totreat->type =="namespace"){
									
				// }
				// if ($totreat->type =="use"){ 	
					
					// $options->toread = $totreat;					
					// if (strpos(rtrim($src),";",-1)!==false){ 
						// $fc = $totreat->startTreat;
						// $fc($src, $options); 
					// }else{
						// //finish use reading;						   										
						// $options->endMarkerFlag = 1;
					// }
					// $options->toread = $bg;
					// //igk_wln_e("finish use : ".$src);
				// }	
				
				if (($totreat->type!="use") && igk_count($totreat->definitions)>0){
						//igk_wln("read definition ");
						// igk_wln_e("ggk".  $options->toread);
						$out = igk_treat_outdef($totreat->definitions, $options, 1);
						
						if ($totreat->type == "function"){
							// igk_wln($totreat->def);
							if (($pos = strpos(rtrim($src), "{", strlen($totreat->def)))===false){
								igk_wln_e("not start bracket found ".$src);
							}	
							$gs = substr($src,0, $pos+1);
							$out = rtrim($out);
							if (!empty($out))
								$src = $gs.$options->LF.$out. substr($src, $pos+1);
							 
						}
						else{
							if (($pos = strpos(rtrim($src), "}", -1))===false){
								igk_wln_e("not end bracket found ".$src);
							}							 
							
							$gs = rtrim(substr($src, 0, $pos )).$options->LF;
							$indent = str_repeat($options->IndentChar, $totreat->indentLevel);
							$src = $gs.$out.$indent."}".$options->LF;
									
						}
						// igk_wln("3:");
				}
				
				if ($skip){
					// remove
					igk_ewln("\e[0;41mwarning:\e[0m ".$totreat->type." is embeded in bracket out. Line: ".
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
	 // array_unshift($tab, (object)array(
		// 'name'=>'readingFunctionReturnType',
		// 'pattern'=>'#\\s*(:)\\s*#',
		// 'mode'=>1,
		// 'callback'=>function($t, $start, & $offset, $m){ // for function reading mode
			// $s = $m->data[0][0];
			// $d = trim($s);		 
			// igk_treat_handle_char($t, $start, $offset, $d, $m);			
			// if ($m->options->FormatText){
				// $d .= " ";
			// }
			// $t = substr($t, 0, $start).$d.substr($t, $start+strlen($s));
			// $offset = $start+strlen($d);
			// return $t;
		// })
	// );
	 array_unshift($tab, (object)array(
		'name'=>'comment',
		'pattern'=>'#//(.)*$#i',
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){
			if( $m->options->context=="globalConstant"){
				$offset = $start+strlen($m->options->LF)+2;
				$t = $m->options->LF.$t;
			}else{
				if ($m->options->RemoveComment){
					$before= trim(substr($t, 0, $start));
					
					if (($pos = strpos(substr($t, $start) , "?>")) !==false)
					{
						$t = $before.substr($t, $start+$pos);
						$offset = strlen($before);
						return $t;
					}
					$t = $before;
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
			// igk_wln("matck cacondigtion");
			$d = $m->data;
			$comment = isset($d["comment"])? !empty($d["comment"][0]) : false;
			if ($m->options->depthFlag || ($m->options->bracketDepth>0)){
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
			$objread->handleChar = function(& $t, $start, & $offset, $ch, $m){
				// igk_wln("define handlechar:".$ch);
				$u = $m->options->toread;
				switch($ch){
					case ")":
						if ($m->options->conditionDepth<=0){
								// finish define wait for ;
								$u = $m->options->toread;
								$u->mode = 4;
								$txt = igk_treat_get($m->options).substr($t, 0, $start);
								$u->src = $txt;
								$mt = substr($txt, $u->argumentOffset);
								// check if contain variable
								// if (preg_match("/\\$/", $mt)){
									// igk_treat_restore_context($m->options);
									// igk_treat_append($m->options, $txt.")", 0);
									// $t = substr($t, $start+1);
									// $offset = 1;
									// // igk_wln_e("contain some variable dec:".$txt);
									// return $t;
								// }
								
								// igk_wln($u);
								// igk_wln("argument:". substr($txt, $u->argumentOffset));
								// igk_wln_e("finish define:".$txt);
								// igk_treat_restore_context($m->options);
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
					case ",":
						if (!isset($u->argumentOffset)){ //handle argument offset
							$u->argumentOffset = strlen(igk_treat_get($m->options))+$start+1;
							// igk_wln("argument offset ".$u->argumentOffset);
						}
						break;
					
				}
			};
			$objread->endTreat = function(){
				igk_wln_e("not implement");
			};
			
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
		'pattern'=>"#///(?P<info>(TASK|TODO|REMARK|NOTE|DEBUG))\\s*:\\s*(?P<data>(.)+)$#i",
		'mode'=>'*',
		'callback'=>function($t, $start, & $offset, $m){
			//leave unchange
			$m->options->DataLFFlag=1;
			igk_treat_append($m->options, trim($m->data[0][0]), 0);
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
					// igk_wln_e("must past line: ".$sf);
					$m->options->DataLFFlag = 0;					
					igk_treat_append($m->options, $sf, 0);
					$t = "";
					$offset = 0; 
					
				}else{
					igk_wln_e("no closed multistring found:".$sL);
				}
				 
				igk_treat_restore_context($m->options);
				$m->options->DataLFFlag = 1;
				// igk_wln("datacontent :".$m->options->DataLFFlag);
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
				igk_wln_e("comment");
			}));
		
	array_unshift($tab, (object)array(
			'name'=>'specialkey',
			'pattern'=>'/(^|\\s+)(?P<operand>break|continue|return|goto)(\\s*)(\\s+|;|$)/',
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, $m){
				$op = $m->data["operand"];
				$tc = $m->data[0][0];
				if ($op[0]=="return"){
					if (strpos($tc, ";")===false){
						//multiline
						igk_treat_append($m->options, "return ", 0);
						$m->options->DataLFFlag=0; // wait for ";"
					}else{
						igk_treat_append($m->options, "return;",0);
						$m->options->DataLFFlag=1;
					}
					$t = ltrim(substr($t, $start+strlen($tc)));
					$offset = 0;
					
					// igk_wln_e("matchreturn:");
					return $t;
				}
				
				$c = preg_replace("/\\s+/", "", $tc);
				$m->options->DataLFFlag = 1;
				igk_treat_append($m->options, $c, 0);
				if (strpos($tc, ";")===false){
					$m->options->DataLFFlag = 0;
				}else
					$m->options->DataLFFlag = 1;
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
			// igk_wln_e("start:::".$t);
			return $t;
		}
	));	
	array_unshift($tab, (object)array(
			'name'=>'htmlContextHandle',
			'pattern'=>'/(\\s*|^)\?\>/',
			'mode'=>'*',
			'callback'=>function($t, $start, & $offset, & $m){
		
				igk_treat_set_context($m->options, "html", 0);
				$tc = $m->data[0][0];  				 
				$offset=0;
				$end_rgx = "/\<\?(php)/";
				$o = "";
				$before = substr($t, 0, $start);
				$offset = $start+ strlen($tc); //$m->data[0][0];
				$t = substr($t, $offset);
				$offset = 0;
				$ln = & $m->options->lineNumber;
				$tn = $m->options->totalLines;
				$c = 0;
				$s = "";
				while(($ln-1) < $tn ){
					
					//detect next <?php open tag 
					if (preg_match($end_rgx, $t, $tab, PREG_OFFSET_CAPTURE, $offset)){						 
						$c=1;
						$s .= substr($t, 0, $tab[0][1]);
						$t = substr($t, $tab[0][1] +strlen($tab[0][0]));
					}
					if ($c || ($ln>=$tn))
						break;
					// igk_wln("Match: ".$t );
					$s.= $t;
					$t = rtrim($m->options->source[$ln]);					
					$s.= $m->options->LF;
					$ln++;
					$offset=0;
				}
				if ($c){
					$o = rtrim($s).$m->options->LF."<?php ";
					$offset = 0;
				}else{ 
					$o = trim($s);
					$offset = strlen($t);
				}
				$empty_o = !empty($o);
				igk_treat_restore_context($m->options);
				$m->options->DataLFFlag = 0;
			 
				if (!empty($before)){
					// if (!$empty_o){
						// $before .= " ? >";
					// }
					igk_treat_append($m->options, $before, 0);
				}
				// }else{
					// if ($empty_o){
						$m->options->DataLFFlag = 0;
						igk_treat_append($m->options, " ?>".$o, 0);
					// }else{
						// igk_treat_append($m->options, " ? >", 0);
					// }
				// }
				if (!$c){
						igk_treat_append($m->options, $t, 0);
						$t="";
						$offset=0;
				}
				return $t;
				 
			}));	
	
	array_unshift($tab, (object)array(
			'name'=>'fileDescription',
			'pattern'=>'/\/\/\\s*(?P<name>([a-zA-Z ]+)):\\s*(?P<value>(.)+)$/',
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
		if(igk_getv($c, "allowDocBlocking")){
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
						$s .= "* @with documentation ".$options->LF;
						
					}else{
						$s .= "* represent {$v1->name} {$v1->type}".$options->LF;
						// $s .= "* @return ".$v1->type.$options->LF;
						
						if (!igk_getv($options, "noAutoParameter") && isset($v1->readP)){
							foreach($v1->readP as $kv=>$vv){
								$s .= "* @param ";
								if (isset($vv->type)){
									$s.= $vv->type;
								}
								if (isset($vv->ref) && $vv->ref){
									$s.="* ";
								}		
								$s.= "$".$vv->name;
								if (isset($vv->default)){
									$s.= IGK_LF."* ".IGK_LF."* default: ".$vv->default;
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
	igk_debug_wln("restore context - context - ".$option->context);
	// igk_wln(igk_show_trace());
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
		igk_wln_e(igk_show_trace());
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

$sdir = "d://wamp/www/igkdev/Lib/igk/";
$dir = "d://wamp/www/igkdev/Lib/igk/";
$outdir = "d://temp/Lib/igk";
$outdir = "D://wamp/www/demoigk/Lib/igk";
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
					igk_wln("failed to check : ".igk_io_dir($v));
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
			igk_io_w2file($outdir."/".$n,$src);
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
		}else{
			// igk_wln("directory not exists");
		}
	}
	$count++;
	
}
igk_wln("total: ".$count);
igk_wln("treat: ".$treat);

}


function igk_treat_modifier($m){
	//armonise modifier detection
	if (empty(trim($m))){
		return "";
	}
	static $ModiL = null;
	
	if ($ModiL == null){
		$ModiL = array(
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
	$tb = explode(" ", $m);
	usort($tb, function($a, $b) use ($ModiL){
		$c1 = $ModiL[$a];
		$c2 = $ModiL[$b];
		return  $c1<$c2 ? 1 : ($c1>$c2 ? 1 : 0);
	});
	$s = implode(" ", $tb);
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
		 
		//treat: global usage
		$tab = igk_getv($def,"global");
		if ($tab && isset($callbacks["global"])){			 
			call_user_func_array($callbacks["global"], array_merge(array($tab, & $tdef), $tab_args));
		}
		
		///TASK: treat use
		$tab = igk_getv($def,"use");
		if ($tab && isset($callbacks["use"])){
			usort($tab, function($a, $b){
				return $a->name <=>$b->name;
			});
			call_user_func_array($callbacks["use"], array_merge(array($tab, & $tdef), $tab_args));
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


function bind_data($command=null){


$file = 'D:\dev\2019\php\sources\data.php';
$outfile =  "d://temp/deftest/out.php";

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
	igk_wln_e($c);	
}
$source = file_get_contents($file);


//TODO: handle string value: is constant way
//


$mp = igk_treat_source($source, function($out, $option){
	if (!empty($option->data)){
		
		igk_wln("cDepth: ".$option->conditionDepth);
		igk_wln("bDepth: ".$option->bracketDepth);
		igk_wln("oDepth: ".$option->openHook);
		igk_wln("context:".$option->context);
		igk_wln("context:".$option->context);
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
		igk_wln_e("some: error: data is not empty:". $option->data);
	}else{
		// var_dump($option->definitions);
		// igk_wln_e($option->context);
		$regx = "/^\<\\?(php)?(\\s*|$)/";
		$s = "";
		$lf = (empty($option->LF)? $option->LF:IGK_LF);
		
		if (preg_match($regx, $out)){			
			$def = igk_treat_outdef($option->definitions, $option);
			$s = "<?php";
			$out = preg_replace($regx, "", $out);
			$s .= $lf.$def.$out;
		}else{
			$option->noFileDesc = 1;
			$def = igk_treat_outdef($option->definitions, $option);
			igk_wln_e("OUTPUT:".$out);
			$s .= $out.$lf.$def;
		}
			igk_wln_e("OUTPUT:".$s);
		return $s;
	}
	return $out;
});

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
	igk_wln(<<<EOF
This is igkdev trait php source code CLI
author: C.A.D. BONDJE DOUE
copyright: IGKDEV @ 2019
version : 1.0

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
	foreach($keys as $k){
		$v = $helps[$k];
		igk_wl("    \e[1;32m". str_pad($k, 20, " ")."\e[0m".str_repeat(" ", 2). ": " .$v."\n");
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
	if (igk_getv($c, "debug")==1){		
		igk_debug(1);
	}
	$c->reports = array();
	igk_start_time(__FUNCTION__);
	$fc($c);
	$ct= igk_execute_time(__FUNCTION__);
	igk_wln("time: ".$ct."s");
	if (!empty($c->reports)){
		igk_wln("Reports:");
		foreach($c->reports as $k=>$v){
			igk_wln($k."\n\t:".$v);
		} 
	}
	$c_e = 0;
	if (!empty($c->errors)){
		$c_e = igk_count($c->errors);
	}
	igk_ewln("Errors : ".$c_e);
}

function igk_treat_filecommand($command){
	$file = $command->inputFile;
	if (!isset($command->noAutoCheck) || ($command->noAutoCheck==0) ){
		$g = exec("php -l ".realpath($file)." 2> NUL", $c, $o); // redirect error no null
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
	
	if (igk_getv($command, "noDefineHandle")){
		$options->noDefineHandle = 1;
	}
	
	if (igk_getv($command, "genxmldoc")){
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
			$xml_doc["xmlns"] = "https://schema.igkdev.com/php/doc";
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
							$out[] = "<summary>represent ".$v->name." ".$v->type."</summary>";
							
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
					$gen_type($tab, $tdef, $op, $c, $gen_type);	
				}
			
			], $m, $op, function($tab, & $tdef, $op, $fc, $gen_type){
					foreach($tab as $k=>$v){
						$m = $fc->add("member");
						$m["name"] = $v->name;
						if ($v->documentation)
							$out =  explode(IGK_LF, $v->documentation);//.IGK_LF;
						else{
							$out= array("<summary>represent ".$v->name." ".$v->type."</summary>");
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

	if ($ctab = igk_getv($command, "defListener")) {
		foreach($ctab as $k=>$v){
			$options->endDefinitionListener[] = $v;
		}
	} 
	
	//
	//TODO: handle string value: is constant way
	//

$mp = igk_treat_source($source, function($out, $option){
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
			 
			$regx = "/^\<\\?(php)?(\\s*|$)/";
			$s = "";
			$lf = (empty($option->LF)? $option->LF:IGK_LF);
			
			if (preg_match($regx, $out)){			
				$def = igk_treat_outdef($option->definitions, $option);
				$s = "<?php";
				$out = preg_replace($regx, "", $out);
				$s .= $lf.$def.$out;
			}else{
				$option->noFileDesc = 1;
				$def = igk_treat_outdef($option->definitions, $option);
				// igk_wln("OUT2:".$out);
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
},null, $options);

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
			igk_wln("not file: ".$f);
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
		
		
		igk_debug_wln("input file : ".$v);
	
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




igk_treat_reg_command("-data", function($v, $command, $c){	 
	$command->{"exec"} = function($command){ 
		bind_data($command);
	};	
}, "Test data.php file library");

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
			$h = str_replace("/","\\/", $h);
			$h = str_replace(".","\.", $h);
			$h = "/(".implode("|", $h).")$/";
		}
		return $h;
}
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
			igk_wln("\e[0;31mfile:\e[0m".$file);
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

igk_treat_reg_command("-d, --inputDir", function($v, $command, $c){
	if ($command->waitForNextEntryFlag){
		$v = igk_io_expand_path($v);		
		$command->inDir = $v;
		if (!is_dir($v)){
			igk_wln_e("error", "Input directory ".$v." does not exists");		
		}
		
		$command->{"exec"} = function($command){ 
		 if(!isset($command->outDir)){
			 $command->outDir = "d:/temp/treatin/public";
		 }
		ini_set("max_execution_time", 0);
		$dir = $command->inDir;
		$sdir = igk_io_dir($command->outDir);
		$ln = strlen($dir);
		$ifolder = null;
		$ignore_dir = igk_treat_get_ignore_regex($command);	
		
		foreach(igk_io_getfiles($dir, "/(.)+/", true, $ignore_dir)  as $file){
			
			// if(preg_match("/(^\.|\.vscode|\.git)/", basename(dirname($file)))){
				// igk_wln("ignore folder file : ".$file);
				// continue;
			// }
			
			$outfile = igk_io_dir($sdir.substr($file,$ln));
			igk_ewln("\e[0;31mfile:\e[0m->".$file);
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
				//igk_wln("\e[0;32mclass : \e[0m" );
				igk_treat_render_data($tab,"Interfaces:", $r);	
				// render_data($k);
				
			},
			"trait"=>function($tab, &$tref, $m){
				igk_treat_render_data($tab,"Traits:", $r);	
				// igk_wln("\e[0;32mtrait: \e[0m" );
				// foreach($tab as $k){
					// igk_wln($k->type." ".$k->name." ");
				// }
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
		$v = explode(";", igk_io_expand_path($v));
		
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

igk_treat_reg_command("--multi-linevar", function($v, $command, $c){
	$command->multilineVars = 1; 
}, "on variable grouping - declare one variable per line.");


igk_treat_reg_command("--text-lineConcatenation", function($v, $command, $c){
	$command->textMultilineConcatenation = 1; 
}, "concatenate line segment in multiline.");

igk_treat_reg_command("--no-xmlDoc", function($v, $command, $c){
	$command->noXmlDoc = 1; 
}, "disable auto xml documentation.");

igk_treat_reg_command("-utest", function($v, $command, $c){
	igk_treat_check_command_handle($command);
	$command->unitTest = 1; 
	$command->{"exec"} = function($command){ 
		igk_wln("start unit testing");
	};
	$command->commandHandle=1;
	
}, "start unit testing on local data");


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
