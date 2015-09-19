<?php
include (APPPATH.'libraries/facebook/facebook.php');
class Fbconnect {
    public function send_back($value){
        return $value;
    }
    
    public function test(){
        $ci =& get_instance();
        
        $ci->load->library('form_validation');
        $ci->load->helper('url');
        echo base_url();
    }
}