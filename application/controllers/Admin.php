<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

   public function __construct() {

      parent::__construct();
      $this->load->model('user');
      if (!$this->session->userdata('logged_in') || $this->user->getUserdataById($this->session->userdata('user_id'))->role!=1) redirect('/');
      $this->load->library('form_validation');
      $this->load->helper("security");
      $this->load->library('pagination');      
      $this->load->config('appconfig');      
   }

	public function index()
	{
      $this->users();
	}

   
   // public function dashboard()
	// {
   //    $data['pageN'] = 1;
	// 	return $this->load->view('admin/dashboard', $data);
	// }


   public function users(){
      $data['pageN'] = 2;
      $this->load->model('user');

      $config = $this->config->item('adminPaginationConfig');
      $config['base_url'] = base_url('admin/users');
      $config['total_rows'] =  $this->user->getAllUsersCount();
      $this->pagination->initialize($config);
      $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
       
      $data['links'] = $this->pagination->create_links();
      $data['page'] = $page;
      $data['users'] = $this->user->getAllUsers($config["per_page"], $page);
		return $this->load->view('admin/users', $data);
	}


   public function jobs(){	      
      $data['pageN'] = 3;
      $this->load->model(['jobtype', 'category', 'job']);
      $data['jobTypes'] = $this->jobtype->getJobTypes();
      $data['categories'] = $this->category->getCategories();
      
      // $this->form_validation->set_data($_GET);     
      // $this->form_validation->set_rules('keyword', 'Keyword', 'xss_clean|max_length[100]');      
      // $this->form_validation->set_rules('jobtype', 'Job type', 'integer');   
      // $this->form_validation->set_rules('category', 'Category', 'integer');   
      // $this->form_validation->set_rules('status', 'Status', 'integer');
      
      // if ($this->form_validation->run()) {
         $keyword    = $this->input->get('keyword', true);
         $jobtype    = filter_var($this->input->get('jobtype'), FILTER_VALIDATE_INT)?$this->input->get('jobtype'):0;
         $category   = filter_var($this->input->get('category'), FILTER_VALIDATE_INT)?$this->input->get('category'):0;
         $status     = filter_var($this->input->get('status'), FILTER_VALIDATE_INT)?$this->input->get('status'):0;
         $total_rows = $this->job->getAllJobsAdminCount($keyword, $jobtype, $category, $status);                 
         $config = $this->config->item('searchPaginationConfig');
         $config['suffix'] =  $this->input->get('keyword')? '?'.http_build_query($_GET, '', "&") : '?keyword=&jobtype='.$jobtype.'&category='.$category.'&status='.$status;
         $config["base_url"] = base_url('admin/jobs');
         $config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);         
         $config["total_rows"] = $total_rows;
         $this->pagination->initialize($config);
         $data['links'] = $this->pagination->create_links();
         $page = $this->uri->segment(3) ? $this->uri->segment(3) : $page = 0;         
         $data['jobs'] = $this->job->getAllJobsAdmin($keyword, $jobtype, $category, $status, $config["per_page"], $page);
         $data['keyword'] = $keyword;
         $data['jobtype'] = $jobtype;
         $data['category'] = $category;
         $data['status'] = $status;
         $data['page'] = $page;
      // }else{
      //    $config = $this->config->item('searchPaginationConfig');
      //    $data['page'] = 0;
      //    $data['jobs'] = $this->job->getAllJobsAdmin(null, 0, 0, 2, $config["per_page"], 0);
      // }
      //
		return $this->load->view('admin/jobs', $data);
	}

   public function editjob($id){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model(['jobtype', 'location', 'category', 'subcategory', 'job']);
         $data['jobTypes'] = $this->jobtype->getJobTypes();
         $data['locations'] = $this->location->getLocations();
         $data['categories'] = $this->category->getCategories();

         if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $this->form_validation->set_rules('fullname', 'Full name', 'trim|required|xss_clean|min_length[2]|max_length[200]');
            $this->form_validation->set_rules('jobtype', 'Job type', 'required|integer|less_than['.(count($data['jobTypes'])+1).']');
            $this->form_validation->set_rules('category', 'Category', 'required|integer');
            $this->form_validation->set_rules('subcategory', 'Subcategory', 'required|integer');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean|min_length[5]|max_length[100]');
            $this->form_validation->set_rules('website', 'Website', 'trim|valid_url|xss_clean|min_length[5]|max_length[200]');
            $this->form_validation->set_rules('location', 'Location', 'required|integer');
            $this->form_validation->set_rules('zip', 'Zip code', 'alpha_dash|required|xss_clean');
            $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean|min_length[4]|max_length[200]');
            $this->form_validation->set_rules('shorttexten', 'Short text English', 'trim|required|xss_clean|min_length[4]|max_length[250]');
            $this->form_validation->set_rules('shorttextru', 'Short text Russian', 'trim|xss_clean|max_length[250]');
            $this->form_validation->set_rules('largetexten', 'Long text English', 'trim|required|xss_clean');
            $this->form_validation->set_rules('largetextru', 'Long text Russian', 'trim|xss_clean');
            $this->form_validation->set_rules('company', 'Company', 'xss_clean|min_length[2]|max_length[100]');
                       
            //if validation passed save data to db
            if ($this->form_validation->run()) {
               
               $jobid = $this->job->editJobAdmin(
                  $id,
                  $this->input->post('jobtype'),
                  $this->input->post('fullname', true),
                  $this->input->post('phone', true),
                  $this->input->post('email', true),
                  $this->input->post('website', true),
                  $this->input->post('company', true),
                  $this->input->post('location'),
                  $this->input->post('address', true),
                  $this->input->post('zip', true),
                  $this->input->post('category'),
                  $this->input->post('subcategory'),
                  $this->input->post('shorttexten', true),
                  $this->input->post('shorttextru', true), 
                  $this->input->post('largetexten', true),
                  $this->input->post('largetextru', true), 
                  strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->input->post('shorttexten', true)))
               );              
                
               //if data saved set message
               if($jobid){           
                  $this->session->set_flashdata('editJobResult', array('status' => true, 'message' => "Job updated successfully"));
                  return redirect('admin/jobs');
               }else{
                  $this->session->set_flashdata('editJobResult', array('status' => false, 'message' => "Could not update job"));
                  return redirect('admin/jobs');
               }
            }
         }
         $data['currentJob'] = $this->job->getJobById($id);
         $data['subcategories'] = $this->subcategory->getSubcategoriesByCategoryId($data['currentJob']->category_id);
         $data['bgPath'] = base_url($this->config->item('fileUploadConfig')['upload_path']);
         $this->load->view('admin/editjob', $data);
      }else{
         return redirect('admin/jobs');
      }      
   }


   public function deletejob($id){
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         // Delete files
         $this->load->model('job');
         foreach($this->job->getImages($id) as $image){
            if($image){
               unlink($this->config->item('uploadFolder') . $image);
            }
         }
         // Then record from DB
         if($this->job->deleteJob($id)){
            $this->session->set_flashdata('deleteJobResult', array('status' => true, 'message' => lang('delAppSucc')));
         }else{
            $this->session->set_flashdata('deleteJobResult', array('status' => false, 'message' => lang('delAppFail')));
         }       
      }
      return redirect('admin/jobs');
   }


   public function deleteimage($jobid, $imageid){
      if($jobid && filter_var($jobid, FILTER_VALIDATE_INT) && $imageid && filter_var($imageid, FILTER_VALIDATE_INT) && $imageid<6){
         $this->load->model('job');
         $currentJob = $this->job->getJobById($jobid);
         if($this->job->clearImage($jobid, $imageid)){
            $uploadFolder = $this->config->item('uploadFolder');
            $filename = $currentJob->{'imgfilename'.$imageid};
            //echo $uploadFolder . $filename;exit;
            unlink(base_url($uploadFolder . $filename));
            $this->session->set_flashdata('fileDelete', array('status' => true, 'message' => "File deleted successfully"));
            return redirect('admin/editjob/'.$jobid);
         }else{
            $this->session->set_flashdata('fileDelete', array('status' => false, 'message' => "Could not delete file"));
         }

      }else{
         return redirect('admin/jobs');
      }
   }


   public function categories(){
      $data['pageN'] = 4;
		$this->load->model('category');
		$data['categories'] = $this->category->getCategorySubcategory();
      $this->load->view('admin/categories', $data);      
   }

   public function addcategory(){
      $data['pageN'] = 5;      
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){     
         $this->form_validation->set_rules('category_en', 'Category English', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[categories.category_en]');
         $this->form_validation->set_rules('category_ru', 'Category Russian', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[categories.category_ru]');
         if (empty($_FILES['icon']['name'])) $this->form_validation->set_rules('icon', 'Category image', 'required');
         //if validation passed save data to db
         if ($this->form_validation->run()) {
            $this->load->model('category');
            $catId = $this->category->addCategory(
               $this->input->post('category_en', true),
               $this->input->post('category_ru', true)
            );
            
            //if data saved try to upload image
            if($catId){
               $filename = 'icon'.'_'.$catId;
               $config = $this->config->item('categoryIconUploadConfig');
               $config['file_name'] = $filename;
               $this->load->library('upload');
               $this->upload->initialize($config);

               $originalFileName = $_FILES['icon']['name'];
               $ext = pathinfo($originalFileName, PATHINFO_EXTENSION);
               
               if ($this->upload->do_upload('icon')) {
                  $this->category->updateImage($catId, $filename.'.'.$ext);
                  $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
               }else {
                  $this->category->deleteCategory($catId);
                  $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "job added successfully"));
               }
            }else{
               $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
            }
         }
      }
      $this->load->view('admin/addcategory', $data);      
   }


   public function editcategory($id=0){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('category');
         $category = $this->category->getCategoryById($id);         
         $iconPath = base_url($this->config->item('categoryIconUploadConfig')['upload_path']); 
         
         if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $this->form_validation->set_rules('category_en', 'Category English', 'trim|required|xss_clean|min_length[2]|max_length[200]');
            $this->form_validation->set_rules('category_ru', 'Category Russian', 'trim|required|xss_clean|min_length[2]|max_length[200]');
            //if validation passed save data to db
            if ($this->form_validation->run()) {
               $filename = 'icon'.'_'.$id;
               $config = $this->config->item('categoryIconUploadConfig');
               $config['file_name'] = $filename;
               $this->load->library('upload');
               $this->upload->initialize($config);

               if($_FILES['icon']['name']){
                  if ($this->upload->do_upload('icon')){
                     if($this->category->editCategory($id, $this->input->post('category_en', true), $this->input->post('category_ru', true))){           
                        $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
                        return redirect('admin/categories');
                     }else{
                        $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
                     }
                  }else{
                     // couldnot upoad image, ie different type or larger size
                     $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error uploading image"));
                  }
               }else{
                  if($this->category->editCategory($id, $this->input->post('category_en', true), $this->input->post('category_ru', true))){
                     $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
                     return redirect('admin/categories');
                  }else{
                     $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
                  }
               }
            }
         }
         $data['category'] = $category;         
         $data['iconPath'] = $iconPath;         
         if ($data['category'])
            $this->load->view('admin/editcategory', $data);
         else
            return redirect('admin/categories');
      }else{
         return redirect('admin/categories');
      }      
   }


   public function deletecategory($id=0){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model(['category', 'subcategory']);
         $category = $this->category->getCategoryById($id);
         if ($category){
            if($this->category->deleteCategory($id) && $this->subcategory->deleteSubcategoryByCategoryId($id)){
               unlink($this->config->item('categoryIconUploadConfig')['upload_path'] . $category->filename);
               $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
            }else{
               $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
            }
         }         
      }
      return redirect('admin/categories');            
   }

   
   public function addsubcategory($id=0){
      $data['pageN'] = 6;
      $data['id'] = filter_var($id, FILTER_VALIDATE_INT) ? $id : 0;
      $this->load->model('category');      
      $data['categories'] = $this->category->getCategories();      
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){  
         $this->subCategoryValidation();         
         //if validation passed save data to db
         if ($this->form_validation->run()) {
            $this->load->model('subcategory');
            $subCatId = $this->subcategory->addSubcategory(
               $this->input->post('category'),
               $this->input->post('subcategory_en', true),
               $this->input->post('subcategory_ru', true)
            );
            
            if($subCatId){
               $this->session->set_flashdata('addSubcatResult', array('status' => true, 'message' => "job added successfully"));
            }else {
               $this->session->set_flashdata('addSubcatResult', array('status' => false, 'message' => "job added successfully"));
            }
         }
      }
      $this->load->view('admin/addsubcategory', $data);
   }


   public function editsubcategory($id=0){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('category');
         $data['categories'] = $this->category->getCategories();
         $this->load->model('subcategory');
         $data['subcategory'] = $this->subcategory->getSubcategoryById($id);
         
         if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $this->form_validation->set_rules('category', 'Category', 'required|integer');
            $this->form_validation->set_rules('subcategory_en', 'Subcategory English', 'trim|required|xss_clean|min_length[2]|max_length[200]');
            $this->form_validation->set_rules('subcategory_ru', 'Subcategory Russian', 'trim|required|xss_clean|min_length[2]|max_length[200]');
            //if validation passed save data to db
            if ($this->form_validation->run()) {
               if($this->subcategory->editSubcategory($id, $this->input->post('category'), $this->input->post('subcategory_en', true), $this->input->post('subcategory_ru', true))){           
                  $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
                  return redirect('admin/categories');
               }else{
                  $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
               }                 
            }
         }       
         if ($data['subcategory'])
            $this->load->view('admin/editsubcategory', $data);
         else
            return redirect('admin/categories');
      }else{
         return redirect('admin/categories');
      }      
   }

   public function deletesubcategory($id=0){	
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('subcategory');
         $subcategory = $this->subcategory->getSubcategoryById($id);
         if ($subcategory){
            if($this->subcategory->deleteSubcategory($id)){
               $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
            }else{
               $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
            }
         }         
      }
      return redirect('admin/categories');            
   }


   public function locations(){
      $data['pageN'] = 7;      
      $this->load->model('location');
		$data['locations'] = $this->location->getLocations();
      $this->load->view('admin/locations', $data);      
   }

   public function addlocation($id=0){
      $data['pageN'] = 8;
      $data['id'] = filter_var($id, FILTER_VALIDATE_INT) ? $id : 0; 
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){  
         $this->form_validation->set_rules('location', 'Location', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[locations.location]');        
         //if validation passed save data to db
         if ($this->form_validation->run()) {
            $this->load->model('location');            
            if($this->location->addLocation($this->input->post('location'))){
               $this->session->set_flashdata('addLocResult', array('status' => true, 'message' => "job added successfully"));
            }else {
               $this->session->set_flashdata('addLocResult', array('status' => false, 'message' => "job added successfully"));
            }
         }
      }
      $this->load->view('admin/addlocation', $data);
   }

   public function editlocation($id=0){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('location');         
         
         if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $this->form_validation->set_rules('location', 'Location', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[locations.location]');
            //if validation passed save data to db
            if ($this->form_validation->run()) {
               //if data saved set flash message
               if($this->location->updateLocation($id, $this->input->post('location', true))){           
                  $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
                  return redirect('admin/locations');
               }else{
                  $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
               }
            }
         }

         $data['location'] = $this->location->getLocationById($id);
         if ($data['location'])
            $this->load->view('admin/editlocation', $data);
         else
            return redirect('admin/locations');
      }else{
         return redirect('admin/locations');
      }      
   }

   public function deletelocation($id=0){
      $data['pageN'] = 99;
      if($id && filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('location');         
         if($this->location->deleteLocation($id)){           
            $this->session->set_flashdata('flashMsg', array('status' => true, 'message' => "job added successfully"));
            return redirect('admin/locations');
         }else{
            $this->session->set_flashdata('flashMsg', array('status' => false, 'message' => "error adding job"));
         }
      }
      return redirect('admin/locations');            
   }


   public function editimages(){
      $data['pageN'] = 9;      
      $this->load->model('image'); 
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         //set file upload config for images         
         $filename = 'favicon';
         $config = $this->config->item('bgImagesUploadConfig');
         $config['file_name'] = $filename;
         $this->load->library('upload');
         $this->upload->initialize($config);
         $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
         if($_FILES['favicon']['name'])
            if ($this->upload->do_upload('favicon'))
               $this->image->editImage(3, $filename.'.'.$ext);

         $filename = 'logo_en';
         $config = $this->config->item('bgImagesUploadConfig');
         $config['file_name'] = $filename;
         $this->load->library('upload');
         $this->upload->initialize($config);
         $ext = pathinfo($_FILES['logo_en']['name'], PATHINFO_EXTENSION);
         if($_FILES['logo_en']['name'])
            if ($this->upload->do_upload('logo_en'))
               $this->image->editImage(4, $filename.'.'.$ext);

         $filename = 'logo_ru';
         $config = $this->config->item('bgImagesUploadConfig');
         $config['file_name'] = $filename;
         $this->load->library('upload');
         $this->upload->initialize($config);
         $ext = pathinfo($_FILES['logo_ru']['name'], PATHINFO_EXTENSION);
         if($_FILES['logo_ru']['name'])
            if ($this->upload->do_upload('logo_ru'))
               $this->image->editImage(5, $filename.'.'.$ext);
               

         $filename = 'maincover';
         $config = $this->config->item('bgImagesUploadConfig');
         $config['file_name'] = $filename;
         $this->load->library('upload');
         $this->upload->initialize($config);
         $ext = pathinfo($_FILES['main']['name'], PATHINFO_EXTENSION);
         if($_FILES['main']['name'])
            if ($this->upload->do_upload('main'))
               $this->image->editImage(1, $filename.'.'.$ext);

         $filename = 'jobscover';
         $config = $this->config->item('bgImagesUploadConfig');
         $config['file_name'] = $filename;
         $this->load->library('upload');
         $this->upload->initialize($config);
         $ext = pathinfo($_FILES['job']['name'], PATHINFO_EXTENSION);
         if($_FILES['job']['name'])
            if ($this->upload->do_upload('job'))
               $this->image->editImage(2, $filename.'.'.$ext);

         $this->image->editImage(6, $this->input->post('jobListBanner'));
         $this->image->editImage(7, $this->input->post('detailListBanner'));
         return redirect('admin/editimages');
      }
      $data['bgPath'] = base_url($this->config->item('bgImagesUploadConfig')['upload_path']);
      $data['images'] = $this->image->getImageNames();
      $this->load->view('admin/editimages', $data);
   }


   public function socials(){
      $data['pageN'] = 10;
      $this->load->model('social');
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         $this->form_validation->set_rules('facebook', 'Facebook lin', 'trim|max_length[200]|valid_url');
         $this->form_validation->set_rules('instagram', 'Instagram link', 'trim|max_length[200]|valid_url');
         $this->form_validation->set_rules('linkedin', 'Location', 'trim|max_length[200]|valid_url');
         $this->form_validation->set_rules('google', 'Location', 'trim|max_length[200]|valid_url');
         $this->form_validation->set_rules('twitter', 'Location', 'trim|max_length[200]|valid_url');
         //if validation passed save data to db
         if ($this->form_validation->run()) {
            //if data saved set flash message
            if($this->social->updateSocials(
               strlen($this->input->post('facebook', true))?(substr($this->input->post('facebook',true),0,4)==="http"? $this->input->post('facebook', true):'https://'.$this->input->post('facebook', true)):null,
               strlen($this->input->post('instagram', true))?(substr($this->input->post('instagram',true),0,4)==="http"? $this->input->post('instagram', true):'https://'.$this->input->post('instagram', true)):null,
               strlen($this->input->post('linkedin', true))?(substr($this->input->post('linkedin',true),0,4)==="http"? $this->input->post('linkedin', true):'https://'.$this->input->post('linkedin', true)):null,
               strlen($this->input->post('google', true))?(substr($this->input->post('google', true),0,4)==="http"? $this->input->post('google', true):'https://'.$this->input->post('google', true)):null,
               strlen($this->input->post('twitter', true))?(substr($this->input->post('twitter', true),0,4)==="http"? $this->input->post('twitter', true):'https://'.$this->input->post('twitter', true)):null)){
                  $this->session->set_flashdata('socialResult', array('status' => true, 'message' => "Social links updated successfully"));
                  return redirect('admin/socials');
            }else{
               $this->session->set_flashdata('socialResult', array('status' => false, 'message' => "Error updating social links"));
            }
         }
      }
      $data['socials'] = $this->social->getSocials();
      $this->load->view('admin/socials', $data);    
   }


   public function jobtypes(){
      $data['pageN'] = 11;
      $this->load->model('jobtype');
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         $this->form_validation->set_rules('stInitPeriod', 'Standart Application Initial Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('stInitPrice', 'Standart Application Initial Price', 'integer|greater_than_equal_to[0]');
         $this->form_validation->set_rules('stRenPeriod', 'Standart Application Renewal Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('stRenPrice', 'Standart Application Renewal Price', 'integer|greater_than_equal_to[0]');

         $this->form_validation->set_rules('silInitPeriod', 'Silver Application Initial Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('silInitPrice', 'Silver Application Initial Price', 'integer|greater_than_equal_to[0]');
         $this->form_validation->set_rules('silRenPeriod', 'Silver Application Renewal Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('silRenPrice', 'Silver Application Renewal Price', 'integer|greater_than_equal_to[0]');

         $this->form_validation->set_rules('golInitPeriod', 'Gold Application Initial Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('golInitPrice', 'Gold Application Initial Price', 'integer|greater_than_equal_to[0]');
         $this->form_validation->set_rules('golRenPeriod', 'Gold Application Renewal Period', 'integer|greater_than[0]');
         $this->form_validation->set_rules('golRenPrice', 'Gold Application Renewal Price', 'integer|greater_than_equal_to[0]');

         //if validation passed save data to db
         if ($this->form_validation->run()) {
            //if data saved set flash message
            if(
               $this->jobtype->updateJobTypes(
                  1, $this->input->post('stInitPeriod')*86400, $this->input->post('stInitPrice'), $this->input->post('stRenPeriod')*86400, $this->input->post('stRenPrice')) &&
               $this->jobtype->updateJobTypes(
                  2, $this->input->post('silInitPeriod')*86400, $this->input->post('silInitPrice'), $this->input->post('silRenPeriod')*86400, $this->input->post('silRenPrice')) &&
               $this->jobtype->updateJobTypes(
                  3, $this->input->post('golInitPeriod')*86400, $this->input->post('golInitPrice'), $this->input->post('golRenPeriod')*86400, $this->input->post('golRenPrice'))
            ){
               $this->session->set_flashdata('jobTypesResult', array('status' => true, 'message' => "Job type data updated successfully"));
               return redirect('admin/jobtypes');
            }else{
               $this->session->set_flashdata('jobTypesResult', array('status' => false, 'message' => "Error updating job type data"));
            }
         }
      }
      $data['jobTypes'] = $this->jobtype->getJobTypes();
      $this->load->view('admin/jobtypes', $data);
   }


   public function aboutus(){
      $data['pageN'] = 12;
      $this->load->model('aboutus');
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         $this->form_validation->set_rules('title_en', 'Title English', 'required');
         $this->form_validation->set_rules('title_ru', 'Title Russian', 'required');
         $this->form_validation->set_rules('subtitle_en', 'Subtitle English', 'required');
         $this->form_validation->set_rules('subtitle_ru', 'Subtitle Russian', 'required');
         $this->form_validation->set_rules('aboutus_en', 'About us English', 'required');
         $this->form_validation->set_rules('aboutus_ru', 'About us Russian', 'required');
         if ($this->form_validation->run()) {
            //if data saved set flash message
            if($this->aboutus->editAboutus(
                  $this->input->post('title_en', true), 
                  $this->input->post('title_ru', true),
                  $this->input->post('subtitle_en', true),
                  $this->input->post('subtitle_ru', true),
                  $this->input->post('aboutus_en'),
                  $this->input->post('aboutus_ru')
               )){
               $this->session->set_flashdata('aboutusResult', array('status' => true, 'message' => "About us updated successfully"));
               return redirect('admin/aboutus');
            }else{
               $this->session->set_flashdata('aboutusResult', array('status' => false, 'message' => "Error updating About us"));
            }
         }
      }
      $data['aboutUs'] = $this->aboutus->getAboutus();
      $this->load->view('admin/aboutus', $data);
   }


   public function contact(){
      $data['pageN'] = 13;
      $this->load->model('contactus');
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         $this->form_validation->set_rules('address_en', 'Address English', 'xss_clean');
         $this->form_validation->set_rules('address_ru', 'Address Russian', 'xss_clean');
         $this->form_validation->set_rules('email', 'Email', 'valid_email|xss_clean');
         $this->form_validation->set_rules('phone', 'Phone', 'xss_clean');
         $this->form_validation->set_rules('location', 'Location URL', 'valid_url|xss_clean');
         if ($this->form_validation->run()) {
            //if data saved set flash message
            if($this->contactus->editContact(
                  $this->input->post('address_en', true), 
                  $this->input->post('address_ru', true),
                  $this->input->post('email'),
                  $this->input->post('phone', true),
                  $this->input->post('location')
               )){
               $this->session->set_flashdata('contactusResult', array('status' => true, 'message' => "Contact details updated successfully"));
               return redirect('admin/contact');
            }else{
               $this->session->set_flashdata('contactusResult', array('status' => false, 'message' => "Error updating Contact details"));
            }
         }
      }
      $data['contacts'] = $this->contactus->getContacts();
      $this->load->view('admin/contact', $data);
   }


   public function terms(){
      $data['pageN'] = 14;
      $this->load->model('term');
      if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
         $this->form_validation->set_rules('terms_en', 'Terms in English', 'xss_clean|required');
         $this->form_validation->set_rules('terms_ru', 'Terms in Russian', 'xss_clean|required');
         if ($this->form_validation->run()) {
            //if data saved set flash message
            if($this->term->editTerms($this->input->post('terms_en', true), $this->input->post('terms_ru', true))){
               $this->session->set_flashdata('termsResult', array('status' => true, 'message' => "Terms updated successfully"));
               return redirect('admin/terms');
            }else{
               $this->session->set_flashdata('termsResult', array('status' => false, 'message' => "Error updating Terms"));
            }
         }
      }
      $data['terms'] = $this->term->getTerms();
      $this->load->view('admin/terms', $data);
   }


   public function deactivateuser($id){
      if (filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('user');
         $this->user->setStatus($id, false);
         return redirect('admin/users');
      }
   }

   public function activateuser($id){
      if (filter_var($id, FILTER_VALIDATE_INT)){
         $this->load->model('user');
         $this->user->setStatus($id, true);
         return redirect('admin/users');
      }
   }


   public function setUserRole($userId, $role){
      if (filter_var($userId, FILTER_VALIDATE_INT) && ($role==1 || $role==2)){
         $this->load->model('user');
         $this->user->setUserRole($userId, $role);
         return redirect('admin/users');
      }
   }





   public function changepassword()
	{
      $data['pageN'] = 20;
		$this->load->view('admin/changepassword', $data);
	}


   public function changepassword_process()
	{
		$this->form_validation->set_rules('oldPassword', 'Old Password', 'required');
		$this->form_validation->set_rules('newPassword', 'New Password', 'required|min_length[6]|max_length[32]');
		$this->form_validation->set_rules('confPassword', 'Confirm Password', 'required|min_length[6]|max_length[32]|matches[newPassword]');

		if ($this->form_validation->run()) {
			$this->load->model('user');
			$userdata = $this->user->getUserdataById($this->session->userdata('user_id'));
         
			if ($userdata && password_verify($this->input->post('oldPassword'), $userdata->password)){
				if($this->user->updatePassword($this->session->userdata('user_id'), password_hash($this->input->post('newPassword'), PASSWORD_BCRYPT))){
					$this->session->set_flashdata('pwdChng', array('status' => true, 'message' => "Password updated successfully"));
               return redirect('admin/changepassword');
				}else{
				   $this->session->set_flashdata('pwdChng', array('status' => false, 'message' => "Could not update password"));
            }
			}else{
            $this->session->set_flashdata('pwdChng', array('status' => false, 'message' => "Invalid old password."));
			}
		}
      return redirect('admin/changepassword');
	}


   public function getSubcategories(){      
      $this->load->model('subcategory');
      //$data['subcategories'] = $this->subcategory->getSubcategoriesByCategoryId($postData['categoryid']);//
      $data['subcategories'] = $this->subcategory->getSubcategoriesByCategoryId($_POST['categoryid']);//$postData['categoryid']
      $data['token']= $this->security->get_csrf_hash();
      echo json_encode($data);
	}

      
   private function subCategoryValidation(){
      $this->form_validation->set_rules('category', 'Category', 'required|integer');
      $this->form_validation->set_rules('subcategory_en', 'Subcategory English', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[subcategories.subcategory_en]');
      $this->form_validation->set_rules('subcategory_ru', 'Subcategory Russian', 'trim|required|xss_clean|min_length[2]|max_length[200]|is_unique[subcategories.subcategory_ru]');
   }

  
   // File manager integration into Codeigniter start
   public function filemanager() {
      $seg = $this->uri->segment(3);
      if (file_exists("application/third_party/filemanager/" . $seg . ".php")) {
         include "application/third_party/filemanager/" . $seg . ".php";
      }
   }
   // File manager integration into Codeigniter end








   
}
