<?php
defined("IGK_FRAMEWORK") || die("REQUIRE FRAMEWORK - No direct access allowed");

function igk_treat_source($source,  $callback, $tab=null,& $options=null){
	if (is_string($source)){
		$source = explode("\n", $source);
	}
	$options = $options ?? igk_treat_create_options();
	$tab = $tab ?? igk_treat_source_expression($options);
	$out = & $options->output;
	$offset = & $options->offset;
	$sline = & $options->lineNumber;
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
	
	if (!function_exists("igk_treat_append")){
		function igk_treat_append($options, $t, $indent=0){
			if (isset($options->writeListener ))
			{
				$fc = $options->writeListener;
				call_user_func_array($fc, func_get_args());
				return;
			}	 		
			if ($options->mode != 0)
				return; 
			$options->output .= $t;
		}
	}
	
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
		$auto_reset_list=  isset($options->autoResetList) ? $options->autoResetList : 
			array("operatorFlag", "mustPasLineFlag");
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
					if (isset($options->$re)){
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

