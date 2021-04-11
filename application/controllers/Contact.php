<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends CI_Controller {

	public function index()
	{
		$this->load->config('appconfig');
		$data['iconPath'] = base_url($this->config->item('categoryIconUploadConfig')['upload_path']);
		$data['bgPath'] = base_url($this->config->item('bgImagesUploadConfig')['upload_path']);
		$this->load->model('image');
		$data['images'] = $this->image->getImageNames();
		$data['pageN'] = 3;
		$this->load->view('contact', $data);
	}

	

}