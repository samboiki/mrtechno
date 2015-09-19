<?php if (!defined('BASEPATH')) die();
class Mrtechno extends Main_Controller {
    public function __construct(){
		parent::__construct();

        // To use site_url and redirect on this controller.
        $this->load->helper('url');
	}
    public function index(){
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->view('index');
   }
  
   public function signin_validation(){
           $this->load->library('form_validation');
           $this->load->library('session');
           $this->form_validation->set_rules('username','Username','required|trim|xxs_clean|callback_validate_credentials');           
           $this->form_validation->set_rules('password','Password','required|md5|trim');
            
        if ($this->form_validation->run()){
              $data = array(
                    'username' => $this->input->post('username'),
                    'logged_in' => 1
                );
                $this->session->set_userdata($data);
                
                $username = $this->input->post('username');
                
                $id_of_user = $this->crud->get_new_user_id($username);
                $userid = $id_of_user['id'];
                //get role
                    $rowarray = $this->crud->get_role($userid);
                    $role = $rowarray['role'];
                    
                    if($role == 'employer'){
                         redirect('rprofile');
                         
                    } elseif ($role == 'youth') {
                         redirect('yprofile');
                    }
                    else {
                         redirect('aprofile');
                    }
                
            } else {
                $this->load->helper('form');
                $this->load->view('signin'); 
                }
        } 
        
        
   public function signup_validation() {
       $this->load->library('form_validation');
       $this->load->helper('date');
        //runnning backend form validation to sanitize the data
        $this->form_validation->set_rules('email','Email','trim|xxs_clean|is_unique[person.email]');
        $this->form_validation->set_rules('password','Password','trim');
        $this->form_validation->set_rules('firstname','Firstname','trim|xxs_clean');
        $this->form_validation->set_rules('lastname','Lastname','trim|xxs_clean');
        $this->form_validation->set_rules('number','Number','trim|xxs_clean');
        $this->form_validation->set_rules('role','Role','trim|xxs_clean');
            //if validation runs successfully insert the data
            if ($this->form_validation->run()){
                    $this->load->model('crud');
                    $signupdata = array(
                                    'last_modified' => now(),
                                    'firstname' => $this->input->post('firstname'),
                                    'lastname' => $this->input->post('lastname'),
                                    'password' => $this->input->post('password'),
                                    'phone' => $this->input->post('number'),
                                    'email' => $this->input->post('email'),
                                    'role' => $this->input->post('role')
                    );
                    
                    
                    $this->crud->add_users($signupdata);
                    
                    //getting current id of recently inserted user
                    $username = $this->input->post('number');
                    
                    //using the posted username to compare the latest of the one in the DB as 
                    //its a unique one so that i can get that users id which will be added in the contact table crud is 
                    //model name, get_new_user_id is funtion name in that model and im passing the username value and i store the returned row of that function 
                    //in $id_of_added_user and will get the id value only and and store to $userid which will be used as foreign key in contact table
                    $id_of_added_user = $this->crud->get_new_user_id($username);
                    $userid = $id_of_added_user['id'];
                   
                    
                    // invoking add_userscontact() method to add the user
                   
                    // now im setting up session data
                    $data = array(
                        'number' => $this->input->post('number'),
                        'logged_in' => 1
                    );
                    $this->session->set_userdata($data);
                    
                    //get role
                    $rowarray = $this->crud->get_role($userid);
                    $role = $rowarray['role'];
                    
                    if($role == 'employer'){
                         redirect('rprofile');
                         
                    } elseif ($role == 'youth') {
                         redirect('yprofile');
                    }
                    else {
                         redirect('aprofile');
                    }
                    
            }
            else {
                redirect();
            }
   }
   
   public function validate_credentials(){
            $this->load->library('form_validation');
            $this->load->model('crud');
            if($this->crud->can_log_in()){
               return true;
            } else {
                
                $this->form_validation->set_message('validate_credentials','incorrect username/password');
                return false;
            }
    }  

   public function yprofile(){
        //loading required libraries and model
        $this->load->library('facebook');
        $this->load->library('session');
        $this->load->model('crud');
        
        //check to see if user is logged in else redirect to restricted page
            if($this->session->userdata('logged_in')){
            
                                  
              // var_dump($this->session->all_userdata());
               $username = $this->session->userdata('username');
            
               $data['user'] = $this->crud->get_user($username);
               $userid = $data['user']->id;
               $data['contact'] = $this->crud->get_usercontacts($userid);
              // echo $data['contact']->id;
               $this->load->helper('form');
               $this->load->helper('url');
               $this->load->view('yprofile',$data);
            } else {
                redirect('restricted');
            }
    }
    
   public function rprofile(){
    //loading required libraries and model
        $this->load->library('facebook');
        $this->load->library('session');
        $this->load->model('crud');
        
              // echo $data['contact']->id;
               $this->load->helper('form');
               $this->load->helper('url');
               $this->load->view('rprofile');
          
    }

   public function header(){
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->view('include/header',$data);
    }
   
    public function signin(){
      $this->load->library('facebook');
      $user = $this->facebook->getUser();
      if ($user) {
            try {
                $data['user_profile'] = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                $user = null;
            }
        }else {
            $this->facebook->destroySession();
        }
      if ($user) {
            $data['logout_url'] = site_url('logout'); // Logs off application
            $data['logout_url'] = $this->facebook->getLogoutUrl();// Logs off FB!
        } else {
            $data['login_url'] = $this->facebook->getLoginUrl(array(
                'redirect_uri' => site_url('yprofile'), 
                'scope' => array("email") // permissions here
            ));
        }
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->view('signin',$data);
   }
   
    public function signup(){
      $this->load->library('facebook');
      $user = $this->facebook->getUser();
      if ($user) {
            try {
                $data['user_profile'] = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                $user = null;
            }
        }else {
            $this->facebook->destroySession();
        }
      if ($user) {
        } else {
            $data['login_url'] = $this->facebook->getLoginUrl(array(
                'redirect_uri' => site_url('yprofile'), 
                'scope' => array("email") // permissions here
            ));
        }
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->view('signup',$data);
   }


    
    public function stat_charts(){
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->model('crud');
      $tbdata['samm'] = $this->crud->get_tb();
      $tbs = $tbdata['samm'];
      $tbdatad['damm'] = $this->crud->get_tbreg();
      $tbreg = $tbdatad['damm'];
      //echo json_encode($tbs);
     // $mann = $tbs->region;
      //var_dump($tbs);
      $result = array();
      $res = array();
      
      foreach($tbreg as $vals){
         $district = $vals->region;
         $col = $vals->color;
         $q5 = $vals->Quest5;
         $q6 = $vals->Quest6;
         $q7 = $vals->Quest7;
         $q8 = $vals->Quest8;
         $q10 = $vals->Quest10;
         $q13 = $vals->Quest13;
         $q14 = $vals->Quest14;
         $q15 = $vals->Quest15;
         $q21 = $vals->Quest21;
         $tot = $vals->total;
         
         $valu = array(
             ['x'=> 'q5', 'y' => $q5],
             ['x'=> 'q6', 'y' => $q6],
             ['x'=> 'q10','y' => $q10],
             ['x'=> 'q13','y' => $q13],
             ['x'=> 'q14','y' => $q14],
             ['x'=> 'q15','y' => $q15],
             ['x'=> 'q21','y' => $q21],
            // ['x'=> 'tot','y' => $tot]
         );
         
         $values = array(
          'key' => $district,
          'color' => $col,
          'values' => $valu
         );
         
         $wow[]=$values;
         $valu = [];
         }
     //echo json_encode($valu);

     
      //echo json_encode($results);
      
      
      $this->load->view('stat_charts');
      // echo json_encode($wow, JSON_NUMERIC_CHECK);
    }
    


    public function restricted(){
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->view('restricted');
    }
    

    public function logout(){
        $this->session->sess_destroy();
        $this->load->library('facebook');
        // Logs off session from website
        $this->facebook->destroySession();
        // Make sure you destory website session as well.
        redirect('index.php');
    }
    
    
       
}
/* End of file frontpage.php */
/* Location: ./application/controllers/frontpage.php */
