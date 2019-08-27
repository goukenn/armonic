<?php





class DataTest extends AbstractArmonicTesting{
    
    public static function CreateTest($file){

    }
    public function testallData()
    {

        foreach( igk_io_getfiles(dirname(__FILE__)."/Data", "/\.xml$/") as $k=>$v){
            $data = igk_conf_load_file($v, "armonic");
            if (isset($data->test->disable) && $data->test->disable){
                continue;
            }

            $this->setName(basename($v));
            $this->createWarning("Warning info");
            $out = igk_treat_source($data->test->input);         
        //    igk_wln_e("assert : ", $out);//, $data->test->output);
             $this->assertEquals( $data->test->output, $out);
            //$this->assertEquals("","");
        } 
    }
}

