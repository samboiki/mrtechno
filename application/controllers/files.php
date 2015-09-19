<?php
class Files extends Controller{
    function Files(){
        parent::Controller();
        $this->load->helper('file');
    }
    
    function write_test(){
        $data = "Hello World";
        $file = "application". DIRECTORY_SEPARATOR . "test_files"
               . DIRECTORY_SEPARATOR . "hello.txt";            
    
       write_file($file, $data);
       echo "finished";
    }
}