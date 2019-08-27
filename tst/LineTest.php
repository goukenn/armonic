<?php
//
use PHPUnit\Framework\TestCase;

class MMMLineTest  //+ extends AbstractArmonicTesting7
{
    public function testInline(){
        // igk_wln_e("tesing ...");
        $data = igk_conf_load_file(dirname(__FILE__).'/Data/linetest.xml', "armonic");
        $out = igk_treat_source($data->test->input);
        $this->assertEquals($out, $data->test->output);

        // $this->runTest();
        

        // $this->runTest("");
        // $g = new ReflectionMethod(get_class($this), "runTest");
        
        // usort($tc = $g->getNumberOfParameters(), function($a,$b)use(& $tab){
        //     $tab[$a->getName()] = 1;
        //     return 0; 
        // });

        // igk_wln("blamkd", $tc);


        // exit;
    }
}